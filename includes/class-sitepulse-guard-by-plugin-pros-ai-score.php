<?php
/**
 * Simple AI Readiness Score (0–100).
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rule-based GEO readiness checks in plain English.
 */
class Sitepulse_Guard_By_Plugin_Pros_Ai_Score {

	/**
	 * Calculate and persist the score.
	 *
	 * @return array<string, mixed>
	 */
	public function calculate() {
		$checks = $this->checks();
		$total  = 0;
		$max    = 0;

		foreach ( $checks as $check ) {
			$max   += (int) $check['weight'];
			$total += $check['pass'] ? (int) $check['weight'] : 0;
		}

		$score = $max > 0 ? (int) round( ( $total / $max ) * 100 ) : 0;
		$label = $this->label( $score );

		$result = array(
			'score'      => $score,
			'label'      => $label,
			'summary'    => $this->summary( $score ),
			'checks'     => $checks,
			'calculated' => time(),
		);

		update_option( 'spg_by_ppros_scores', $this->merge_score_option( 'ai', $result ), false );

		return $result;
	}

	/**
	 * Individual scored checks.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function checks() {
		$llms       = new Sitepulse_Guard_By_Plugin_Pros_Llms();
		$basic      = $llms->get_basic();
		$full       = $llms->get_full();
		$generated  = (int) get_option( 'spg_by_ppros_llms_generated_at', 0 );
		$fresh      = $generated && ( time() - $generated ) < WEEK_IN_SECONDS;
		$tagline    = (string) get_bloginfo( 'description' );
		$https      = is_ssl() || 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME );
		$sitemap   = $this->sitemap_exists();
		$seo       = $this->has_seo_plugin();
		$schema_ok = $seo || (bool) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'schema.enabled', true );
		$crawler_ok = Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.allow_answer_engines', true )
			&& Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.robots_integration', true );
		$public     = ( '1' === (string) get_option( 'blog_public' ) );
		$content    = $this->has_enough_content();
		$titles     = $this->pages_have_titles();

		return array(
			array(
				'id'     => 'llms',
				'title'  => __( 'llms.txt is generated', 'spg-by-ppros' ),
				'detail' => $basic
					? __( 'Answer engines can read a clean site map at /llms.txt.', 'spg-by-ppros' )
					: __( 'Generate llms.txt so assistants know what this site is about.', 'spg-by-ppros' ),
				'pass'   => strlen( $basic ) > 80,
				'weight' => 20,
			),
			array(
				'id'     => 'llms_full',
				'title'  => __( 'llms-full.txt has detail', 'spg-by-ppros' ),
				'detail' => strlen( $full ) > 160
					? __( 'The longer file gives assistants page-level context.', 'spg-by-ppros' )
					: __( 'Regenerate llms-full.txt after you publish core pages.', 'spg-by-ppros' ),
				'pass'   => strlen( $full ) > 160,
				'weight' => 10,
			),
			array(
				'id'     => 'fresh',
				'title'  => __( 'AI files are up to date', 'spg-by-ppros' ),
				'detail' => $fresh
					? __( 'Files were generated within the last 7 days.', 'spg-by-ppros' )
					: __( 'Regenerate the files after major content changes.', 'spg-by-ppros' ),
				'pass'   => (bool) $fresh,
				'weight' => 10,
			),
			array(
				'id'     => 'tagline',
				'title'  => __( 'Site has a clear description', 'spg-by-ppros' ),
				'detail' => $tagline
					? __( 'The tagline is used as the site-level summary.', 'spg-by-ppros' )
					: __( 'Add a tagline in Settings → General.', 'spg-by-ppros' ),
				'pass'   => strlen( trim( $tagline ) ) >= 12,
				'weight' => 10,
			),
			array(
				'id'     => 'https',
				'title'  => __( 'Site is served over HTTPS', 'spg-by-ppros' ),
				'detail' => $https
					? __( 'Secure URLs are easier for crawlers to trust and cite.', 'spg-by-ppros' )
					: __( 'Install an SSL certificate and serve the site over HTTPS.', 'spg-by-ppros' ),
				'pass'   => $https,
				'weight' => 10,
			),
			array(
				'id'     => 'indexable',
				'title'  => __( 'Search engines are allowed', 'spg-by-ppros' ),
				'detail' => $public
					? __( 'The site is not discouraging crawlers in Reading settings.', 'spg-by-ppros' )
					: __( 'Settings → Reading is blocking search engines. Answer engines will usually stay away too.', 'spg-by-ppros' ),
				'pass'   => $public,
				'weight' => 10,
			),
			array(
				'id'     => 'crawler',
				'title'  => __( 'Answer engines are allowed in robots.txt', 'spg-by-ppros' ),
				'detail' => $crawler_ok
					? __( 'SitePulse Guard is allowing citation-oriented crawlers.', 'spg-by-ppros' )
					: __( 'Turn on crawler control and allow answer engines.', 'spg-by-ppros' ),
				'pass'   => $crawler_ok,
				'weight' => 10,
			),
			array(
				'id'     => 'sitemap',
				'title'  => __( 'An XML sitemap is available', 'spg-by-ppros' ),
				'detail' => $sitemap
					? __( 'A sitemap helps both search and AI crawlers discover pages.', 'spg-by-ppros' )
					: __( 'WordPress, Yoast, or Rank Math can publish /sitemap.xml.', 'spg-by-ppros' ),
				'pass'   => $sitemap,
				'weight' => 5,
			),
			array(
				'id'     => 'schema',
				'title'  => __( 'Structured data is available', 'spg-by-ppros' ),
				'detail' => $seo
					? __( 'Yoast or Rank Math is handling schema. SitePulse Guard will not fight it.', 'spg-by-ppros' )
					: (
						$schema_ok
							? __( 'SitePulse Guard outputs JSON-LD (WebSite, Organization, Article/WebPage, FAQ) when no SEO plugin is active.', 'spg-by-ppros' )
							: __( 'Enable SitePulse Guard schema in Settings, or install Yoast / Rank Math.', 'spg-by-ppros' )
					),
				'pass'   => $schema_ok,
				'weight' => 5,
			),
			array(
				'id'     => 'content',
				'title'  => __( 'Enough public content to cite', 'spg-by-ppros' ),
				'detail' => $content
					? __( 'There are published pages or posts assistants can quote.', 'spg-by-ppros' )
					: __( 'Publish a homepage and a few clear service or article pages.', 'spg-by-ppros' ),
				'pass'   => $content,
				'weight' => 5,
			),
			array(
				'id'     => 'titles',
				'title'  => __( 'Key pages have unique titles', 'spg-by-ppros' ),
				'detail' => $titles
					? __( 'Recent pages use real titles instead of “Untitled”.', 'spg-by-ppros' )
					: __( 'Give every important page a unique, descriptive title.', 'spg-by-ppros' ),
				'pass'   => $titles,
				'weight' => 5,
			),
		);
	}

	/**
	 * Whether core sitemaps or a SEO sitemap exist.
	 *
	 * @return bool
	 */
	private function sitemap_exists() {
		if ( function_exists( 'wp_sitemaps_get_server' ) && (bool) get_option( 'blog_public' ) ) {
			return true;
		}

		return $this->has_seo_plugin();
	}

	/**
	 * Yoast or Rank Math active.
	 *
	 * @return bool
	 */
	private function has_seo_plugin() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active( 'wordpress-seo/wp-seo.php' )
			|| is_plugin_active( 'seo-by-rank-math/rank-math.php' );
	}

	/**
	 * At least a few published items.
	 *
	 * @return bool
	 */
	private function has_enough_content() {
		$pages = wp_count_posts( 'page' );
		$posts = wp_count_posts( 'post' );
		$count = 0;
		if ( $pages && isset( $pages->publish ) ) {
			$count += (int) $pages->publish;
		}
		if ( $posts && isset( $posts->publish ) ) {
			$count += (int) $posts->publish;
		}

		return $count >= 2;
	}

	/**
	 * Recent public posts have titles.
	 *
	 * @return bool
	 */
	private function pages_have_titles() {
		$query = new WP_Query(
			array(
				'post_type'              => array( 'post', 'page' ),
				'post_status'            => 'publish',
				'posts_per_page'         => 8,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( ! $query->have_posts() ) {
			wp_reset_postdata();
			return false;
		}

		$ok = true;
		foreach ( $query->posts as $post ) {
			$title = trim( wp_strip_all_tags( get_the_title( $post ) ) );
			if ( '' === $title || 0 === strcasecmp( $title, 'Untitled' ) ) {
				$ok = false;
				break;
			}
		}

		wp_reset_postdata();

		return $ok;
	}

	/**
	 * Score label.
	 *
	 * @param int $score Score.
	 * @return string
	 */
	private function label( $score ) {
		if ( $score >= 80 ) {
			return __( 'Ready', 'spg-by-ppros' );
		}
		if ( $score >= 55 ) {
			return __( 'Getting there', 'spg-by-ppros' );
		}

		return __( 'Needs work', 'spg-by-ppros' );
	}

	/**
	 * Plain-English headline.
	 *
	 * @param int $score Score.
	 * @return string
	 */
	private function summary( $score ) {
		if ( $score >= 80 ) {
			return __( 'Answer engines can find and understand this site. Keep the llms files fresh when you publish.', 'spg-by-ppros' );
		}
		if ( $score >= 55 ) {
			return __( 'The basics are in place. Fix the failed checks below to make citations more likely.', 'spg-by-ppros' );
		}

		return __( 'Start by generating llms.txt, allowing answer engines, and publishing a clear site description.', 'spg-by-ppros' );
	}

	/**
	 * Merge one pillar into the shared scores option.
	 *
	 * @param string               $key   Pillar key.
	 * @param array<string, mixed> $value Score payload.
	 * @return array<string, mixed>
	 */
	private function merge_score_option( $key, $value ) {
		$all = get_option( 'spg_by_ppros_scores', array() );
		if ( ! is_array( $all ) ) {
			$all = array();
		}
		$all[ $key ] = $value;

		return $all;
	}
}
