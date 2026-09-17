<?php
/**
 * Accessibility scanning, scoring, and statement helper.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backend accessibility checks with user-approved fixes.
 */
class Sitepulse_Guard_By_Plugin_Pros_Accessibility {

	/**
	 * Scan published content and media.
	 *
	 * @param int $limit Max posts to inspect.
	 * @return array<string, mixed>
	 */
	public function scan( $limit = 40 ) {
		$types = Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'ai_visibility.post_types', array( 'post', 'page' ) );
		$query = new WP_Query(
			array(
				'post_type'              => $types,
				'post_status'            => 'publish',
				'posts_per_page'         => max( 5, min( 80, (int) $limit ) ),
				'orderby'                => 'modified',
				'order'                  => 'DESC',
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		$pages  = array();
		$totals = array(
			'missing_alt'   => 0,
			'weak_alt'      => 0,
			'heading'       => 0,
			'label'         => 0,
			'link'          => 0,
			'iframe'        => 0,
			'pages_scanned' => 0,
		);

		$suggestions = array();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$report = $this->scan_post( $post );
				$pages[] = $report;
				++$totals['pages_scanned'];

				foreach ( $report['counts'] as $key => $count ) {
					if ( isset( $totals[ $key ] ) ) {
						$totals[ $key ] += (int) $count;
					}
				}

				foreach ( $report['alt_suggestions'] as $item ) {
					$suggestions[] = $item;
				}

				update_post_meta( $post->ID, '_spg_by_ppros_a11y_score', $report['score'] );
				update_post_meta( $post->ID, '_spg_by_ppros_a11y_issues', $report['issues'] );
			}
		}

		wp_reset_postdata();

		$media = $this->scan_media_library( 40 );
		$totals['missing_alt'] += $media['missing'];
		$totals['weak_alt']    += $media['weak'];
		$suggestions            = array_merge( $suggestions, $media['suggestions'] );

		$score = $this->score_from_totals( $totals );
		$label = $this->label( $score );

		$report = array(
			'score'        => $score,
			'label'        => $label,
			'summary'      => $this->summary( $score, $totals ),
			'totals'       => $totals,
			'pages'        => $pages,
			'media'        => $media,
			'calculated'   => time(),
			'recommendations' => $this->recommendations( $totals ),
		);

		update_option( 'spg_by_ppros_a11y_report', $report, false );
		update_option( 'spg_by_ppros_alt_suggestions', $this->unique_suggestions( $suggestions ), false );

		$scores        = get_option( 'spg_by_ppros_scores', array() );
		$scores        = is_array( $scores ) ? $scores : array();
		$scores['a11y'] = array(
			'score'      => $score,
			'label'      => $label,
			'summary'    => $report['summary'],
			'calculated' => time(),
		);
		update_option( 'spg_by_ppros_scores', $scores, false );

		return $report;
	}

	/**
	 * Scan a single post’s HTML.
	 *
	 * @param WP_Post $post Post.
	 * @return array<string, mixed>
	 */
	public function scan_post( $post ) {
		$html   = (string) $post->post_content;
		$issues = array();
		$counts = array(
			'missing_alt' => 0,
			'weak_alt'    => 0,
			'heading'     => 0,
			'label'       => 0,
			'link'        => 0,
			'iframe'      => 0,
		);
		$alts   = array();

		if ( '' === trim( wp_strip_all_tags( $html ) ) && 'page' === $post->post_type ) {
			// Builder content often lives outside post_content; do not fail the page hard.
			$issues[] = array(
				'type'    => 'content',
				'message' => __( 'This page has little stored HTML. If you use a builder, scan the live page after publishing.', 'spg-by-ppros' ),
			);
		}

		if ( $html && class_exists( 'DOMDocument' ) ) {
			$dom = $this->load_dom( $html );
			if ( $dom ) {
				$image_result = $this->scan_images( $dom, $post );
				$counts['missing_alt'] += $image_result['missing'];
				$counts['weak_alt']    += $image_result['weak'];
				$issues                 = array_merge( $issues, $image_result['issues'] );
				$alts                   = $image_result['suggestions'];

				$heading = $this->scan_headings( $dom );
				$counts['heading'] += $heading['count'];
				$issues             = array_merge( $issues, $heading['issues'] );

				$forms = $this->scan_forms( $dom );
				$counts['label'] += $forms['count'];
				$issues           = array_merge( $issues, $forms['issues'] );

				$links = $this->scan_links( $dom );
				$counts['link'] += $links['count'];
				$issues          = array_merge( $issues, $links['issues'] );

				$frames = $this->scan_iframes( $dom );
				$counts['iframe'] += $frames['count'];
				$issues            = array_merge( $issues, $frames['issues'] );
			}
		}

		$issue_total = array_sum( $counts );
		$score       = max( 20, 100 - ( $issue_total * 8 ) );
		if ( $issue_total === 0 && $html ) {
			$score = 96;
		}

		return array(
			'id'               => (int) $post->ID,
			'title'            => get_the_title( $post ),
			'url'              => get_permalink( $post ),
			'edit'             => get_edit_post_link( $post->ID, 'raw' ),
			'score'            => (int) $score,
			'issues'           => $issues,
			'counts'           => $counts,
			'alt_suggestions'  => $alts,
		);
	}

	/**
	 * Scan attachments missing or weak alt text.
	 *
	 * @param int $limit Max attachments.
	 * @return array<string, mixed>
	 */
	public function scan_media_library( $limit = 40 ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'post_mime_type'         => 'image',
				'posts_per_page'         => max( 5, min( 80, (int) $limit ) ),
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
			)
		);

		$missing     = 0;
		$weak        = 0;
		$suggestions = array();
		$items       = array();

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $attachment ) {
				$alt = (string) get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
				$kind = $this->classify_alt( $alt, $attachment->post_title );
				if ( 'ok' === $kind ) {
					continue;
				}

				if ( 'missing' === $kind ) {
					++$missing;
				} else {
					++$weak;
				}

				$suggested = $this->suggest_alt_rule_based( $attachment );
				$item      = array(
					'attachment_id' => (int) $attachment->ID,
					'title'         => get_the_title( $attachment ),
					'current'       => $alt,
					'suggested'     => $suggested,
					'thumb'         => wp_get_attachment_image_url( $attachment->ID, 'thumbnail' ),
					'edit'          => get_edit_post_link( $attachment->ID, 'raw' ),
					'kind'          => $kind,
				);
				$items[]       = $item;
				$suggestions[] = $item;
			}
		}

		wp_reset_postdata();

		return array(
			'missing'     => $missing,
			'weak'        => $weak,
			'items'       => $items,
			'suggestions' => $suggestions,
		);
	}

	/**
	 * Apply an approved alt-text change.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $alt           New alt text.
	 * @return true|WP_Error
	 */
	public function apply_alt( $attachment_id, $alt ) {
		$attachment_id = absint( $attachment_id );
		$alt           = sanitize_text_field( $alt );

		if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
			return new WP_Error( 'spg_by_ppros_alt', __( 'Invalid image.', 'spg-by-ppros' ) );
		}

		if ( '' === $alt ) {
			return new WP_Error( 'spg_by_ppros_alt', __( 'Alt text cannot be empty.', 'spg-by-ppros' ) );
		}

		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
		$this->drop_suggestion( $attachment_id );

		return true;
	}

	/**
	 * Suggest alt text, using AI when the user opted in.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|WP_Error
	 */
	public function generate_alt( $attachment_id ) {
		$attachment = get_post( absint( $attachment_id ) );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return new WP_Error( 'spg_by_ppros_alt', __( 'Invalid image.', 'spg-by-ppros' ) );
		}

		$fallback = $this->suggest_alt_rule_based( $attachment );

		if ( ! Sitepulse_Guard_By_Plugin_Pros_Ai::is_available() ) {
			return $fallback;
		}

		$url = wp_get_attachment_image_url( $attachment->ID, 'medium' );
		if ( ! $url ) {
			return $fallback;
		}

		$context = $this->attachment_context( $attachment );
		$prompt  = __( 'Write concise, factual HTML alt text (max 15 words). Do not start with “image of”. Describe what a screen reader user needs. Return only the alt text.', 'spg-by-ppros' );

		$result = Sitepulse_Guard_By_Plugin_Pros_Ai::complete( $prompt, $context, $url );
		if ( is_wp_error( $result ) ) {
			if ( in_array( $result->get_error_code(), array( 'spg_by_ppros_limit', 'spg_by_ppros_unavailable' ), true ) ) {
				return $result;
			}
			return $fallback;
		}

		$clean = sanitize_text_field( $result );
		return $clean ? $clean : $fallback;
	}

	/**
	 * Create or update an Accessibility Statement page.
	 *
	 * @return int|WP_Error Page ID.
	 */
	public function generate_statement_page() {
		$existing = (int) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'accessibility.statement_page', 0 );
		$content  = $this->statement_content();
		$title    = __( 'Accessibility Statement', 'spg-by-ppros' );

		if ( $existing && get_post( $existing ) ) {
			$update = wp_update_post(
				array(
					'ID'           => $existing,
					'post_content' => $content,
					'post_status'  => 'publish',
				),
				true
			);

			return is_wp_error( $update ) ? $update : $existing;
		}

		$page_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => 'accessibility-statement',
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		Sitepulse_Guard_By_Plugin_Pros_Settings::update(
			array(
				'accessibility' => array(
					'statement_page' => (int) $page_id,
				),
			)
		);

		return (int) $page_id;
	}

	/**
	 * Last stored report.
	 *
	 * @return array<string, mixed>
	 */
	public function last_report() {
		$report = get_option( 'spg_by_ppros_a11y_report', array() );
		return is_array( $report ) ? $report : array();
	}

	/**
	 * Pending alt suggestions.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function suggestions() {
		$items = get_option( 'spg_by_ppros_alt_suggestions', array() );
		return is_array( $items ) ? $items : array();
	}

	/**
	 * Scan images in a DOM.
	 *
	 * @param DOMDocument $dom  Document.
	 * @param WP_Post     $post Parent post.
	 * @return array<string, mixed>
	 */
	private function scan_images( $dom, $post ) {
		$missing = 0;
		$weak    = 0;
		$issues  = array();
		$alts    = array();
		$images  = $dom->getElementsByTagName( 'img' );

		foreach ( $images as $img ) {
			$alt = $img->getAttribute( 'alt' );
			$src = $img->getAttribute( 'src' );
			$id  = $this->attachment_id_from_src( $src );
			$kind = $this->classify_alt( $alt, basename( (string) wp_parse_url( $src, PHP_URL_PATH ) ) );

			if ( 'ok' === $kind ) {
				continue;
			}

			if ( 'missing' === $kind ) {
				++$missing;
				$issues[] = array(
					'type'    => 'missing_alt',
					'message' => __( 'An image is missing alt text.', 'spg-by-ppros' ),
				);
			} else {
				++$weak;
				$issues[] = array(
					'type'    => 'weak_alt',
					'message' => __( 'An image uses weak or filename-like alt text.', 'spg-by-ppros' ),
				);
			}

			if ( $id ) {
				$attachment = get_post( $id );
				if ( $attachment ) {
					$alts[] = array(
						'attachment_id' => $id,
						'title'         => get_the_title( $attachment ),
						'current'       => $alt,
						'suggested'     => $this->suggest_alt_rule_based( $attachment, $post ),
						'thumb'         => wp_get_attachment_image_url( $id, 'thumbnail' ),
						'edit'          => get_edit_post_link( $id, 'raw' ),
						'kind'          => $kind,
					);
				}
			}
		}

		return array(
			'missing'     => $missing,
			'weak'        => $weak,
			'issues'      => $issues,
			'suggestions' => $alts,
		);
	}

	/**
	 * Heading outline checks.
	 *
	 * @param DOMDocument $dom Document.
	 * @return array{count:int,issues:array<int,array<string,string>>}
	 */
	private function scan_headings( $dom ) {
		$xpath = new DOMXPath( $dom );
		$nodes = $xpath->query( '//h1|//h2|//h3|//h4|//h5|//h6' );
		$levels = array();
		$issues = array();

		if ( $nodes ) {
			foreach ( $nodes as $node ) {
				$levels[] = (int) substr( $node->nodeName, 1 );
			}
		}

		if ( ! $levels ) {
			return array(
				'count'  => 0,
				'issues' => array(),
			);
		}

		$h1 = 0;
		foreach ( $levels as $level ) {
			if ( 1 === $level ) {
				++$h1;
			}
		}

		if ( $h1 > 1 ) {
			$issues[] = array(
				'type'    => 'heading',
				'message' => __( 'More than one H1 found. Keep a single page title.', 'spg-by-ppros' ),
			);
		}

		$prev = $levels[0];
		foreach ( $levels as $level ) {
			if ( $level > $prev + 1 ) {
				$issues[] = array(
					'type'    => 'heading',
					'message' => __( 'Heading levels skip a step (for example H2 to H4). Use a logical outline.', 'spg-by-ppros' ),
				);
				break;
			}
			$prev = $level;
		}

		return array(
			'count'  => count( $issues ),
			'issues' => $issues,
		);
	}

	/**
	 * Form control label checks.
	 *
	 * @param DOMDocument $dom Document.
	 * @return array{count:int,issues:array<int,array<string,string>>}
	 */
	private function scan_forms( $dom ) {
		$issues = 0;
		$list   = array();
		$xpath  = new DOMXPath( $dom );
		$fields = $xpath->query( '//input|//select|//textarea' );

		if ( $fields ) {
			foreach ( $fields as $field ) {
				$type = strtolower( $field->getAttribute( 'type' ) );
				if ( in_array( $type, array( 'hidden', 'submit', 'button', 'reset', 'image' ), true ) ) {
					continue;
				}

				$id    = $field->getAttribute( 'id' );
				$label = $field->getAttribute( 'aria-label' );
				$labelled = $field->getAttribute( 'aria-labelledby' );
				$has_label = false;

				if ( $label || $labelled ) {
					$has_label = true;
				} elseif ( $id ) {
					$match = $xpath->query( '//label[@for="' . $this->css_escape( $id ) . '"]' );
					$has_label = $match && $match->length > 0;
				}

				if ( ! $has_label ) {
					++$issues;
					$list[] = array(
						'type'    => 'label',
						'message' => __( 'A form field is missing an associated label.', 'spg-by-ppros' ),
					);
				}
			}
		}

		return array(
			'count'  => $issues,
			'issues' => $list,
		);
	}

	/**
	 * Vague link text.
	 *
	 * @param DOMDocument $dom Document.
	 * @return array{count:int,issues:array<int,array<string,string>>}
	 */
	private function scan_links( $dom ) {
		$bad    = array( 'click here', 'here', 'read more', 'learn more', 'more' );
		$issues = array();
		$count  = 0;
		$links  = $dom->getElementsByTagName( 'a' );

		foreach ( $links as $link ) {
			$text = strtolower( trim( preg_replace( '/\s+/', ' ', $link->textContent ) ) );
			if ( in_array( $text, $bad, true ) ) {
				++$count;
				$issues[] = array(
					'type'    => 'link',
					'message' => __( 'A link uses vague text such as “click here”. Use descriptive link text.', 'spg-by-ppros' ),
				);
			}
		}

		return array(
			'count'  => $count,
			'issues' => $issues,
		);
	}

	/**
	 * Iframes without a title.
	 *
	 * @param DOMDocument $dom Document.
	 * @return array{count:int,issues:array<int,array<string,string>>}
	 */
	private function scan_iframes( $dom ) {
		$count  = 0;
		$issues = array();
		$frames = $dom->getElementsByTagName( 'iframe' );

		foreach ( $frames as $frame ) {
			if ( ! trim( $frame->getAttribute( 'title' ) ) ) {
				++$count;
				$issues[] = array(
					'type'    => 'iframe',
					'message' => __( 'An embedded frame is missing a title.', 'spg-by-ppros' ),
				);
			}
		}

		return array(
			'count'  => $count,
			'issues' => $issues,
		);
	}

	/**
	 * Load HTML fragment into DOMDocument.
	 *
	 * @param string $html HTML.
	 * @return DOMDocument|null
	 */
	private function load_dom( $html ) {
		$dom    = new DOMDocument();
		$wrapped = '<!DOCTYPE html><html><body>' . $html . '</body></html>';
		$prev    = libxml_use_internal_errors( true );
		$ok      = $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );

		return $ok ? $dom : null;
	}

	/**
	 * Classify alt quality.
	 *
	 * @param string $alt   Alt text.
	 * @param string $hint  Filename or title hint.
	 * @return string missing|weak|ok
	 */
	private function classify_alt( $alt, $hint = '' ) {
		$alt = trim( (string) $alt );
		if ( '' === $alt ) {
			return 'missing';
		}

		$lower = strtolower( $alt );
		if ( strlen( $alt ) < 3 ) {
			return 'weak';
		}

		if ( preg_match( '/\.(jpe?g|png|gif|webp|avif|svg)$/i', $alt ) ) {
			return 'weak';
		}

		if ( preg_match( '/^(img|image|dsc|dscn|screenshot|untitled)[-_\s]?\d*$/i', $alt ) ) {
			return 'weak';
		}

		$hint = strtolower( (string) $hint );
		if ( $hint && $lower === $hint ) {
			return 'weak';
		}

		return 'ok';
	}

	/**
	 * Rule-based alt suggestion from titles and nearby context.
	 *
	 * @param WP_Post      $attachment Attachment.
	 * @param WP_Post|null $parent     Parent post.
	 * @return string
	 */
	private function suggest_alt_rule_based( $attachment, $parent = null ) {
		$title = trim( wp_strip_all_tags( get_the_title( $attachment ) ) );
		$title = preg_replace( '/[-_]+/', ' ', $title );
		$title = preg_replace( '/\.(jpe?g|png|gif|webp|avif|svg)$/i', '', $title );
		$title = preg_replace( '/\s+/', ' ', (string) $title );

		if ( $title && ! preg_match( '/^(img|image|dsc|screenshot|untitled)\s*\d*$/i', $title ) ) {
			return ucfirst( $title );
		}

		if ( ! $parent && $attachment->post_parent ) {
			$parent = get_post( $attachment->post_parent );
		}

		if ( $parent ) {
			$parent_title = trim( wp_strip_all_tags( get_the_title( $parent ) ) );
			if ( $parent_title ) {
				return sprintf(
					/* translators: %s: parent page title */
					__( 'Illustration for %s', 'spg-by-ppros' ),
					$parent_title
				);
			}
		}

		$caption = trim( wp_strip_all_tags( $attachment->post_excerpt ) );
		if ( $caption ) {
			return wp_trim_words( $caption, 12, '' );
		}

		return __( 'Descriptive image', 'spg-by-ppros' );
	}

	/**
	 * Context string for vision prompts.
	 *
	 * @param WP_Post $attachment Attachment.
	 * @return string
	 */
	private function attachment_context( $attachment ) {
		$parts = array(
			'Title: ' . get_the_title( $attachment ),
		);

		if ( $attachment->post_excerpt ) {
			$parts[] = 'Caption: ' . wp_strip_all_tags( $attachment->post_excerpt );
		}

		if ( $attachment->post_parent ) {
			$parent = get_post( $attachment->post_parent );
			if ( $parent ) {
				$parts[] = 'Page: ' . get_the_title( $parent );
			}
		}

		return implode( "\n", $parts );
	}

	/**
	 * Resolve attachment ID from an image URL.
	 *
	 * @param string $src URL.
	 * @return int
	 */
	private function attachment_id_from_src( $src ) {
		if ( ! $src ) {
			return 0;
		}

		$id = attachment_url_to_postid( $src );
		if ( $id ) {
			return (int) $id;
		}

		$clean = preg_replace( '/-\d+x\d+(?=\.[a-zA-Z]{3,4}$)/', '', $src );
		if ( $clean && $clean !== $src ) {
			return (int) attachment_url_to_postid( $clean );
		}

		return 0;
	}

	/**
	 * Score site-wide totals.
	 *
	 * @param array<string, int> $totals Counts.
	 * @return int
	 */
	private function score_from_totals( $totals ) {
		$penalty  = ( $totals['missing_alt'] * 6 )
			+ ( $totals['weak_alt'] * 3 )
			+ ( $totals['heading'] * 5 )
			+ ( $totals['label'] * 8 )
			+ ( $totals['link'] * 2 )
			+ ( $totals['iframe'] * 4 );
		$scanned  = max( 1, (int) $totals['pages_scanned'] );
		$adjusted = (int) round( $penalty / $scanned * 6 );

		return max( 15, min( 100, 100 - $adjusted ) );
	}

	/**
	 * Score label.
	 *
	 * @param int $score Score.
	 * @return string
	 */
	private function label( $score ) {
		if ( $score >= 85 ) {
			return __( 'Strong', 'spg-by-ppros' );
		}
		if ( $score >= 60 ) {
			return __( 'Fair', 'spg-by-ppros' );
		}

		return __( 'Needs work', 'spg-by-ppros' );
	}

	/**
	 * Headline summary.
	 *
	 * @param int                $score  Score.
	 * @param array<string, int> $totals Counts.
	 * @return string
	 */
	private function summary( $score, $totals ) {
		if ( $score >= 85 ) {
			return __( 'Most scanned pages look solid. Review any remaining image or form warnings before you publish an EAA statement.', 'spg-by-ppros' );
		}

		return sprintf(
			/* translators: 1: missing alt count, 2: unlabeled fields */
			__( 'Found %1$d images needing better alt text and %2$d form-label issues. Approve fixes before anything is changed.', 'spg-by-ppros' ),
			(int) $totals['missing_alt'] + (int) $totals['weak_alt'],
			(int) $totals['label']
		);
	}

	/**
	 * Plain-English next steps.
	 *
	 * @param array<string, int> $totals Counts.
	 * @return string[]
	 */
	private function recommendations( $totals ) {
		$recs = array();

		if ( $totals['missing_alt'] || $totals['weak_alt'] ) {
			$recs[] = __( 'Approve suggested alt text for images. Screen readers and EAA audits look for this first.', 'spg-by-ppros' );
		}
		if ( $totals['label'] ) {
			$recs[] = __( 'Add a visible label (or aria-label) to every form field. The visitor widget cannot fix this.', 'spg-by-ppros' );
		}
		if ( $totals['heading'] ) {
			$recs[] = __( 'Use one H1 and do not skip heading levels. This helps keyboard and AT users scan the page.', 'spg-by-ppros' );
		}
		if ( $totals['iframe'] ) {
			$recs[] = __( 'Give embeds (maps, videos) a short title that describes the content.', 'spg-by-ppros' );
		}
		if ( ! $recs ) {
			$recs[] = __( 'Publish an accessibility statement and keep the visitor widget available as a complement — not a substitute — for real fixes.', 'spg-by-ppros' );
		}

		return $recs;
	}

	/**
	 * Unique suggestion rows by attachment.
	 *
	 * @param array<int, array<string, mixed>> $items Suggestions.
	 * @return array<int, array<string, mixed>>
	 */
	private function unique_suggestions( $items ) {
		$out = array();
		foreach ( $items as $item ) {
			$id = isset( $item['attachment_id'] ) ? (int) $item['attachment_id'] : 0;
			if ( $id && ! isset( $out[ $id ] ) ) {
				$out[ $id ] = $item;
			}
		}

		return array_values( $out );
	}

	/**
	 * Remove a suggestion after apply.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void
	 */
	private function drop_suggestion( $attachment_id ) {
		$items = $this->suggestions();
		$items = array_values(
			array_filter(
				$items,
				function ( $item ) use ( $attachment_id ) {
					return (int) ( $item['attachment_id'] ?? 0 ) !== (int) $attachment_id;
				}
			)
		);
		update_option( 'spg_by_ppros_alt_suggestions', $items, false );
	}

	/**
	 * Minimal XPath escape for an id token.
	 *
	 * @param string $value ID.
	 * @return string
	 */
	private function css_escape( $value ) {
		return str_replace( array( '"', '\\' ), '', $value );
	}

	/**
	 * Default statement HTML.
	 *
	 * @return string
	 */
	private function statement_content() {
		$site = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$email = get_option( 'admin_email' );

		$paragraphs = array(
			'<p>' . esc_html(
				sprintf(
					/* translators: %s: site name */
					__( '%s is committed to making this website accessible to as many people as possible, including people with disabilities. We aim to meet WCAG 2.2 Level AA and to support the European Accessibility Act where it applies.', 'spg-by-ppros' ),
					$site
				)
			) . '</p>',
			'<h2>' . esc_html__( 'Measures we take', 'spg-by-ppros' ) . '</h2>',
			'<ul>',
			'<li>' . esc_html__( 'Provide text alternatives for meaningful images.', 'spg-by-ppros' ) . '</li>',
			'<li>' . esc_html__( 'Keep a logical heading structure and keyboard-accessible navigation.', 'spg-by-ppros' ) . '</li>',
			'<li>' . esc_html__( 'Associate labels with form controls.', 'spg-by-ppros' ) . '</li>',
			'<li>' . esc_html__( 'Offer a visitor accessibility widget for font size and contrast preferences.', 'spg-by-ppros' ) . '</li>',
			'</ul>',
			'<h2>' . esc_html__( 'Known limitations', 'spg-by-ppros' ) . '</h2>',
			'<p>' . esc_html__( 'Some older content, third-party embeds, or page-builder blocks may not yet meet every success criterion. We review issues reported to us and prioritize real content fixes over overlay-only workarounds.', 'spg-by-ppros' ) . '</p>',
			'<h2>' . esc_html__( 'Feedback', 'spg-by-ppros' ) . '</h2>',
			'<p>' . esc_html(
				sprintf(
					/* translators: %s: admin email */
					__( 'If you find a barrier, please email %s and include the page URL and a short description of the problem. We aim to reply within five working days.', 'spg-by-ppros' ),
					$email
				)
			) . '</p>',
			'<p><em>' . esc_html__( 'This statement was generated with SitePulse Guard and should be reviewed by the site owner before it is treated as a legal document.', 'spg-by-ppros' ) . '</em></p>',
		);

		return implode( "\n", $paragraphs );
	}
}
