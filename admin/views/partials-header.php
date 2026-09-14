<?php
/**
 * Shared admin header.
 *
 * @package Aicite_Guard
 *
 * @var string $view Current view slug (optional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$screen = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'aicite-guard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nav    = array(
	'aicite-guard'          => __( 'Dashboard', 'aicite-guard' ),
	'aicite-guard-ai'       => __( 'AI Visibility', 'aicite-guard' ),
	'aicite-guard-a11y'     => __( 'Accessibility', 'aicite-guard' ),
	'aicite-guard-health'   => __( 'Site Health', 'aicite-guard' ),
	'aicite-guard-settings' => __( 'Settings', 'aicite-guard' ),
	'aicite-guard-wizard'   => __( 'Wizard', 'aicite-guard' ),
	'aicite-guard-pro'      => __( 'Go Pro', 'aicite-guard' ),
);
?>
<div class="wrap aicite-guard">
	<div class="acg-top">
		<div class="acg-brand">
			<span class="acg-logo" aria-hidden="true">A</span>
			<div>
				<h1><?php esc_html_e( 'AIcite Guard', 'aicite-guard' ); ?></h1>
				<p><?php esc_html_e( 'AI visibility, real accessibility, and a lighter WordPress site — alongside Yoast or Rank Math.', 'aicite-guard' ); ?></p>
			</div>
		</div>
		<nav class="acg-nav" aria-label="<?php esc_attr_e( 'AIcite Guard', 'aicite-guard' ); ?>">
			<?php foreach ( $nav as $slug => $label ) : ?>
				<a class="<?php echo $screen === $slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>
	<div class="acg-notice" data-acg-notice hidden></div>
