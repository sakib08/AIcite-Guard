<?php
/**
 * Pro upsell. Free features stay useful on their own.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.
?>
<section class="spg-by-ppros-card">
	<h2><?php esc_html_e( 'SitePulse Guard Pro', 'spg-by-ppros' ); ?></h2>
	<p><?php esc_html_e( 'The free plugin is meant to stay installed. Pro unlocks volume, automation, and agency workflow — not the core value.', 'spg-by-ppros' ); ?></p>
	<div class="spg-by-ppros-grid spg-by-ppros-grid--2">
		<div>
			<h3><?php esc_html_e( 'You already have', 'spg-by-ppros' ); ?></h3>
			<ul class="spg-by-ppros-list">
				<li><?php esc_html_e( 'llms.txt and llms-full.txt generation', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'AI crawler control', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'AI Readiness Score', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Accessibility scan + approval-based alt text', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Visitor widget and statement generator', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Plugin bloat, conflicts, and health score', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( '20 AI generations / month via the WordPress AI Client', 'spg-by-ppros' ); ?></li>
			</ul>
		</div>
		<div>
			<h3><?php esc_html_e( 'Pro adds', 'spg-by-ppros' ); ?></h3>
			<ul class="spg-by-ppros-list">
				<li><?php esc_html_e( 'Unlimited AI generations and higher-quality rewrites', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Full accessibility remediation with an approval queue', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Advanced AI citation tracking', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Detailed AI crawler logs and analytics', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Multi-site and agency tools', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'White-label option', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Deeper WooCommerce, Rank Math, and Yoast integrations', 'spg-by-ppros' ); ?></li>
				<li><?php esc_html_e( 'Priority support and advanced reports', 'spg-by-ppros' ); ?></li>
			</ul>
		</div>
	</div>
	<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=spg-by-ppros' ) ); ?>"><?php esc_html_e( 'Keep using the free features', 'spg-by-ppros' ); ?></a></p>
	<p class="description"><?php esc_html_e( 'Pro checkout is not bundled in 1.0. This screen is the roadmap so the free plugin stays honest.', 'spg-by-ppros' ); ?></p>
</section>
