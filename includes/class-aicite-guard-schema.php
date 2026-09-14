<?php
/**
 * AI-oriented structured data (JSON-LD) + admin suggestions.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs WebSite, Organization, Article/WebPage, and FAQ JSON-LD
 * only when Yoast / Rank Math (and similar) are not already in charge.
 */
class Aicite_Guard_Schema {

	/**
	 * Print JSON-LD in wp_head when this plugin should own schema.
	 *
	 * @return void
	 */
	public function output() {
		if ( is_admin() || wp_is_json_request() || is_feed() || is_robots() || is_trackback() ) {
			return;
		}

		if ( ! Aicite_Guard_Settings::get_path( 'schema.enabled', true ) ) {
			return;
		}

		if ( $this->seo_plugin_handles_schema() ) {
			return;
		}

		$graph = $this->build_graph();
		if ( empty( $graph ) ) {
			return;
		}

		$payload = array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		);

		$json = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( ! $json ) {
			return;
		}

		echo '<script type="application/ld+json" id="aicite-guard-schema">' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $json . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-LD from wp_json_encode.
		echo '</script>' . "\n";
	}

	/**
	 * Build suggestion list for the AI Visibility screen.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function suggestions() {
		$has_seo     = $this->seo_plugin_handles_schema();
		$enabled     = (bool) Aicite_Guard_Settings::get_path( 'schema.enabled', true );
		$suggestions = array();

		if ( $has_seo ) {
			$suggestions[] = array(
				'status' => 'ok',
				'title'  => __( 'SEO plugin owns structured data', 'aicite-guard' ),
				'detail' => __( 'Yoast, Rank Math, or another SEO plugin is active. AIcite Guard will not print competing JSON-LD.', 'aicite-guard' ),
			);
		} elseif ( $enabled ) {
			$suggestions[] = array(
				'status' => 'ok',
				'title'  => __( 'AIcite Guard is outputting JSON-LD', 'aicite-guard' ),
				'detail' => __( 'WebSite, Organization, Article/WebPage, and FAQ markup are printed when relevant. Pair with Yoast or Rank Math later if you want deeper SEO schema.', 'aicite-guard' ),
			);
		} else {
			$suggestions[] = array(
				'status' => 'warn',
				'title'  => __( 'Structured data output is turned off', 'aicite-guard' ),
				'detail' => __( 'Enable schema in Settings, or install Yoast / Rank Math, so answer engines can identify your pages.', 'aicite-guard' ),
			);
		}

		if ( ! get_bloginfo( 'description' ) ) {
			$suggestions[] = array(
				'status' => 'warn',
				'title'  => __( 'Set a clear site tagline', 'aicite-guard' ),
				'detail' => __( 'A one-sentence description in Settings → General improves WebSite schema and llms.txt.', 'aicite-guard' ),
			);
		}

		$posts = wp_count_posts( 'post' );
		$published_posts = $posts && isset( $posts->publish ) ? (int) $posts->publish : 0;
		if ( $published_posts > 0 ) {
			$suggestions[] = array(
				'status' => ( $has_seo || $enabled ) ? 'ok' : 'info',
				'title'  => __( 'Article schema on posts', 'aicite-guard' ),
				'detail' => $has_seo
					? __( 'Your SEO plugin marks posts as Article. Keep author names and dates visible.', 'aicite-guard' )
					: (
						$enabled
							? __( 'AIcite Guard outputs Article JSON-LD on single posts (headline, dates, author, image).', 'aicite-guard' )
							: __( 'Enable AIcite Guard schema or an SEO plugin so posts are marked as Article.', 'aicite-guard' )
					),
			);
		}

		if ( is_singular() ) {
			$post = get_post();
			if ( $post && count( $this->extract_faq_pairs( $post->post_content ) ) >= 2 ) {
				$suggestions[] = array(
					'status' => 'ok',
					'title'  => __( 'FAQ-style content detected on this page', 'aicite-guard' ),
					'detail' => $has_seo
						? __( 'Consider enabling FAQ schema in your SEO plugin for this page.', 'aicite-guard' )
						: __( 'AIcite Guard will include FAQPage JSON-LD when two or more Q&A pairs are found.', 'aicite-guard' ),
				);
			}
		} elseif ( $this->site_has_faq_content() ) {
			$suggestions[] = array(
				'status' => 'info',
				'title'  => __( 'FAQ-style content found on the site', 'aicite-guard' ),
				'detail' => __( 'Pages with clear question headings can get FAQPage markup for better AI citations.', 'aicite-guard' ),
			);
		}

		$suggestions[] = array(
			'status' => 'info',
			'title'  => __( 'Cite-friendly pages', 'aicite-guard' ),
			'detail' => __( 'Use a unique H1, a short intro, visible authorship, and an updated date. Structured data works best with clear on-page content.', 'aicite-guard' ),
		);

		return $suggestions;
	}

	/**
	 * Status summary for admin UI.
	 *
	 * @return array<string, mixed>
	 */
	public function status() {
		$seo = $this->seo_plugin_handles_schema();

		return array(
			'enabled'       => (bool) Aicite_Guard_Settings::get_path( 'schema.enabled', true ),
			'seo_active'    => $seo,
			'outputting'    => ! $seo && Aicite_Guard_Settings::get_path( 'schema.enabled', true ),
			'provider_name' => $this->active_seo_label(),
		);
	}

	/**
	 * Whether a major SEO plugin is already outputting schema.
	 *
	 * @return bool
	 */
	public function seo_plugin_handles_schema() {
		$plugins = array(
			'wordpress-seo/wp-seo.php',
			'seo-by-rank-math/rank-math.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'wp-seopress/seopress.php',
			'the-seo-framework-extension-manager/the-seo-framework-extension-manager.php',
			'autodescription/autodescription.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// The SEO Framework main plugin file.
		if ( $this->is_plugin_active( 'autodescription/autodescription.php' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Build the @graph nodes for the current request.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function build_graph() {
		$graph   = array();
		$site_id = trailingslashit( home_url( '/' ) ) . '#website';
		$org_id  = trailingslashit( home_url( '/' ) ) . '#organization';

		$graph[] = $this->website_node( $site_id, $org_id );
		$graph[] = $this->organization_node( $org_id );

		if ( is_singular() ) {
			$post = get_queried_object();
			if ( $post instanceof WP_Post && 'publish' === $post->post_status ) {
				$page_node = $this->singular_node( $post, $site_id, $org_id );
				if ( $page_node ) {
					$graph[] = $page_node;
				}

				$faq = $this->faq_node( $post );
				if ( $faq ) {
					$graph[] = $faq;
				}
			}
		} elseif ( is_front_page() ) {
			$graph[] = array(
				'@type'           => 'WebPage',
				'@id'             => trailingslashit( home_url( '/' ) ) . '#webpage',
				'url'             => home_url( '/' ),
				'name'            => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
				'description'     => wp_specialchars_decode( get_bloginfo( 'description' ), ENT_QUOTES ),
				'isPartOf'        => array( '@id' => $site_id ),
				'about'           => array( '@id' => $org_id ),
				'inLanguage'      => get_bloginfo( 'language' ),
			);
		}

		/**
		 * Filter the JSON-LD @graph before output.
		 *
		 * @param array $graph Schema.org nodes.
		 */
		$graph = apply_filters( 'aicite_guard_schema_graph', $graph );

		return array_values( array_filter( $graph ) );
	}

	/**
	 * WebSite node.
	 *
	 * @param string $site_id Site @id.
	 * @param string $org_id  Org @id.
	 * @return array<string, mixed>
	 */
	private function website_node( $site_id, $org_id ) {
		$node = array(
			'@type'       => 'WebSite',
			'@id'         => $site_id,
			'url'         => home_url( '/' ),
			'name'        => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'description' => wp_specialchars_decode( get_bloginfo( 'description' ), ENT_QUOTES ),
			'publisher'   => array( '@id' => $org_id ),
			'inLanguage'  => get_bloginfo( 'language' ),
		);

		$search = home_url( '/?s={search_term_string}' );
		$node['potentialAction'] = array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => $search,
			),
			'query-input' => 'required name=search_term_string',
		);

		return $node;
	}

	/**
	 * Organization (or Person for single-author blogs) node.
	 *
	 * @param string $org_id Org @id.
	 * @return array<string, mixed>
	 */
	private function organization_node( $org_id ) {
		$name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$logo = $this->site_logo_url();

		$node = array(
			'@type' => 'Organization',
			'@id'   => $org_id,
			'name'  => $name,
			'url'   => home_url( '/' ),
		);

		if ( $logo ) {
			$node['logo'] = array(
				'@type' => 'ImageObject',
				'url'   => $logo,
			);
			$node['image'] = $logo;
		}

		$email = get_option( 'admin_email' );
		if ( is_email( $email ) ) {
			$node['email'] = $email;
		}

		return $node;
	}

	/**
	 * Article or WebPage for a singular post.
	 *
	 * @param WP_Post $post    Post.
	 * @param string  $site_id Site @id.
	 * @param string  $org_id  Org @id.
	 * @return array<string, mixed>|null
	 */
	private function singular_node( $post, $site_id, $org_id ) {
		$url   = get_permalink( $post );
		$title = wp_specialchars_decode( get_the_title( $post ), ENT_QUOTES );
		$desc  = $this->plain_excerpt( $post, 40 );
		$image = get_the_post_thumbnail_url( $post, 'full' );

		if ( 'post' === $post->post_type ) {
			$author = get_userdata( (int) $post->post_author );
			$node   = array(
				'@type'            => 'Article',
				'@id'              => trailingslashit( $url ) . '#article',
				'headline'         => $title,
				'description'      => $desc,
				'datePublished'    => get_the_date( DATE_W3C, $post ),
				'dateModified'     => get_the_modified_date( DATE_W3C, $post ),
				'mainEntityOfPage' => array(
					'@type' => 'WebPage',
					'@id'   => $url,
				),
				'isPartOf'         => array( '@id' => $site_id ),
				'publisher'        => array( '@id' => $org_id ),
				'inLanguage'       => get_bloginfo( 'language' ),
			);

			if ( $author ) {
				$node['author'] = array(
					'@type' => 'Person',
					'name'  => $author->display_name,
					'url'   => get_author_posts_url( $author->ID ),
				);
			}
		} else {
			$node = array(
				'@type'       => 'WebPage',
				'@id'         => trailingslashit( $url ) . '#webpage',
				'url'         => $url,
				'name'        => $title,
				'description' => $desc,
				'datePublished' => get_the_date( DATE_W3C, $post ),
				'dateModified'  => get_the_modified_date( DATE_W3C, $post ),
				'isPartOf'    => array( '@id' => $site_id ),
				'about'       => array( '@id' => $org_id ),
				'inLanguage'  => get_bloginfo( 'language' ),
			);
		}

		if ( $image ) {
			$node['image'] = array(
				'@type' => 'ImageObject',
				'url'   => $image,
			);
		}

		return $node;
	}

	/**
	 * FAQPage node when enough Q&A pairs exist in content.
	 *
	 * @param WP_Post $post Post.
	 * @return array<string, mixed>|null
	 */
	private function faq_node( $post ) {
		$pairs = $this->extract_faq_pairs( $post->post_content );
		if ( count( $pairs ) < 2 ) {
			return null;
		}

		$entities = array();
		foreach ( $pairs as $pair ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $pair['question'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $pair['answer'],
				),
			);
		}

		return array(
			'@type'      => 'FAQPage',
			'@id'        => trailingslashit( get_permalink( $post ) ) . '#faq',
			'mainEntity' => $entities,
		);
	}

	/**
	 * Extract Q&A pairs from heading + following paragraph patterns.
	 *
	 * @param string $html Post content.
	 * @return array<int, array{question:string,answer:string}>
	 */
	private function extract_faq_pairs( $html ) {
		$html = (string) $html;
		if ( '' === trim( $html ) || ! class_exists( 'DOMDocument' ) ) {
			return array();
		}

		$dom  = new DOMDocument();
		$prev = libxml_use_internal_errors( true );
		$ok   = $dom->loadHTML( '<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );

		if ( ! $ok ) {
			return array();
		}

		$xpath = new DOMXPath( $dom );
		$nodes = $xpath->query( '//h2|//h3|//h4' );
		$pairs = array();

		if ( ! $nodes ) {
			return array();
		}

		foreach ( $nodes as $heading ) {
			$question = $this->plain_text( $heading->textContent );
			if ( ! $this->looks_like_question( $question ) ) {
				continue;
			}

			$answer_parts = array();
			for ( $sibling = $heading->nextSibling; $sibling; $sibling = $sibling->nextSibling ) {
				if ( XML_ELEMENT_NODE !== $sibling->nodeType ) {
					continue;
				}
				$name = strtolower( $sibling->nodeName );
				if ( in_array( $name, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ) {
					break;
				}
				if ( in_array( $name, array( 'p', 'ul', 'ol', 'div' ), true ) ) {
					$text = $this->plain_text( $sibling->textContent );
					if ( $text ) {
						$answer_parts[] = $text;
					}
				}
				if ( count( $answer_parts ) >= 3 ) {
					break;
				}
			}

			$answer = trim( implode( ' ', $answer_parts ) );
			if ( strlen( $answer ) < 12 ) {
				continue;
			}

			$pairs[] = array(
				'question' => $question,
				'answer'   => wp_html_excerpt( $answer, 500, '…' ),
			);

			if ( count( $pairs ) >= 8 ) {
				break;
			}
		}

		return $pairs;
	}

	/**
	 * Whether text looks like a question heading.
	 *
	 * @param string $text Heading text.
	 * @return bool
	 */
	private function looks_like_question( $text ) {
		$text = trim( $text );
		if ( strlen( $text ) < 8 ) {
			return false;
		}

		if ( '?' === substr( $text, -1 ) ) {
			return true;
		}

		return (bool) preg_match( '/^(what|why|how|when|where|who|which|can|do|does|is|are|should|will)\b/i', $text );
	}

	/**
	 * Custom logo or site icon URL.
	 *
	 * @return string
	 */
	private function site_logo_url() {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		if ( $logo_id ) {
			$url = wp_get_attachment_image_url( $logo_id, 'full' );
			if ( $url ) {
				return $url;
			}
		}

		$icon = get_site_icon_url( 512 );
		return $icon ? $icon : '';
	}

	/**
	 * Plain excerpt helper.
	 *
	 * @param WP_Post $post  Post.
	 * @param int     $words Word count.
	 * @return string
	 */
	private function plain_excerpt( $post, $words ) {
		$text = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
		$text = strip_shortcodes( $text );
		if ( function_exists( 'excerpt_remove_blocks' ) ) {
			$text = excerpt_remove_blocks( $text );
		}
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( '/\s+/', ' ', (string) $text );

		return trim( wp_trim_words( $text, $words, '…' ) );
	}

	/**
	 * Collapse whitespace in DOM text.
	 *
	 * @param string $text Raw text.
	 * @return string
	 */
	private function plain_text( $text ) {
		$text = html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( '/\s+/', ' ', $text );

		return trim( (string) $text );
	}

	/**
	 * Rough site-wide FAQ presence for admin suggestions.
	 *
	 * @return bool
	 */
	private function site_has_faq_content() {
		$query = new WP_Query(
			array(
				'post_type'              => array( 'page', 'post' ),
				'post_status'            => 'publish',
				'posts_per_page'         => 6,
				's'                      => 'faq',
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		$found = $query->have_posts();
		wp_reset_postdata();

		return $found;
	}

	/**
	 * Human label for the active SEO plugin, if any.
	 *
	 * @return string
	 */
	private function active_seo_label() {
		$map = array(
			'wordpress-seo/wp-seo.php'           => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php'      => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'wp-seopress/seopress.php'            => 'SEOPress',
			'autodescription/autodescription.php'=> 'The SEO Framework',
		);

		foreach ( $map as $file => $label ) {
			if ( $this->is_plugin_active( $file ) ) {
				return $label;
			}
		}

		if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
			return 'The SEO Framework';
		}

		return '';
	}

	/**
	 * Safe active-plugin check.
	 *
	 * @param string $plugin Plugin basename.
	 * @return bool
	 */
	private function is_plugin_active( $plugin ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( $plugin );
	}
}
