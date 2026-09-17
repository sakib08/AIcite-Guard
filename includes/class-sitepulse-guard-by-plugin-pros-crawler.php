<?php
/**
 * AI crawler allow/block rules for robots.txt.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes focused robots.txt rules for answer engines vs training scrapers.
 */
class Sitepulse_Guard_By_Plugin_Pros_Crawler {

	/**
	 * Append SitePulse Guard rules to robots.txt.
	 *
	 * @param string $output Current robots.txt.
	 * @param bool   $public Whether the blog is public.
	 * @return string
	 */
	public function filter_robots( $output, $public ) {
		if ( ! $public || ! Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.robots_integration', true ) ) {
			return $output;
		}

		$lines   = array();
		$lines[] = '';
		$lines[] = '# SitePulse Guard — AI crawler control';

		if ( Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.allow_answer_engines', true ) ) {
			$lines[] = '# Allow answer / citation engines';
			foreach ( $this->answer_engines() as $bot ) {
				$lines[] = 'User-agent: ' . $bot;
				$lines[] = 'Allow: /';
				$lines[] = '';
			}
		}

		if ( Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.block_training', true ) ) {
			$lines[] = '# Block known training-only scrapers';
			foreach ( $this->training_scrapers() as $bot ) {
				$lines[] = 'User-agent: ' . $bot;
				$lines[] = 'Disallow: /';
				$lines[] = '';
			}
		}

		$lines[] = '# Helpful AI files';
		$lines[] = 'User-agent: *';
		$lines[] = 'Allow: /llms.txt';
		$lines[] = 'Allow: /llms-full.txt';
		$lines[] = '';

		return rtrim( (string) $output ) . "\n" . implode( "\n", $lines );
	}

	/**
	 * Answer-engine user agents we allow by default.
	 *
	 * @return string[]
	 */
	public function answer_engines() {
		return array(
			'OAI-SearchBot',
			'ChatGPT-User',
			'GPTBot',
			'Claude-SearchBot',
			'Claude-User',
			'PerplexityBot',
			'Perplexity-User',
			'Google-Extended',
			'Applebot-Extended',
			'Amazonbot',
			'meta-externalagent',
		);
	}

	/**
	 * Training-oriented scrapers we block by default.
	 *
	 * @return string[]
	 */
	public function training_scrapers() {
		return array(
			'CCBot',
			'Bytespider',
			'Diffbot',
			'ImagesiftBot',
			'OmigiliBot',
			'YouBot',
			'cohere-ai',
			'DataForSeoBot',
			'Timpibot',
			'Webzio-Extended',
		);
	}

	/**
	 * Human-readable summary for the admin UI.
	 *
	 * @return array<string, mixed>
	 */
	public function summary() {
		return array(
			'enabled'         => (bool) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.robots_integration', true ),
			'allow_engines'   => (bool) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.allow_answer_engines', true ),
			'block_training'  => (bool) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'crawler.block_training', true ),
			'answer_count'    => count( $this->answer_engines() ),
			'training_count'  => count( $this->training_scrapers() ),
			'robots_url'      => home_url( '/robots.txt' ),
		);
	}
}
