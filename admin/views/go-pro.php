<?php
/**
 * Pro upsell. Free features stay useful on their own.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.
?>
<section class="acg-card">
	<h2><?php esc_html_e( 'AIcite Guard Pro', 'aicite-guard' ); ?></h2>
	<p><?php esc_html_e( 'The free plugin is meant to stay installed. Pro unlocks volume, automation, and agency workflow — not the core value.', 'aicite-guard' ); ?></p>
	<div class="acg-grid acg-grid--2">
		<div>
			<h3><?php esc_html_e( 'You already have', 'aicite-guard' ); ?></h3>
			<ul class="acg-list">
				<li><?php esc_html_e( 'llms.txt and llms-full.txt generation', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'AI crawler control', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'AI Readiness Score', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Accessibility scan + approval-based alt text', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Visitor widget and statement generator', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Plugin bloat, conflicts, and health score', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( '20 AI generations / month with your own key', 'aicite-guard' ); ?></li>
			</ul>
		</div>
		<div>
			<h3><?php esc_html_e( 'Pro adds', 'aicite-guard' ); ?></h3>
			<ul class="acg-list">
				<li><?php esc_html_e( 'Unlimited AI generations and higher-quality rewrites', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Full accessibility remediation with an approval queue', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Advanced AI citation tracking', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Detailed AI crawler logs and analytics', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Multi-site and agency tools', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'White-label option', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Deeper WooCommerce, Rank Math, and Yoast integrations', 'aicite-guard' ); ?></li>
				<li><?php esc_html_e( 'Priority support and advanced reports', 'aicite-guard' ); ?></li>
			</ul>
		</div>
	</div>
	<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=aicite-guard' ) ); ?>"><?php esc_html_e( 'Keep using the free features', 'aicite-guard' ); ?></a></p>
	<p class="description"><?php esc_html_e( 'Pro checkout is not bundled in 1.0. This screen is the roadmap so the free plugin stays honest.', 'aicite-guard' ); ?></p>
</section>
