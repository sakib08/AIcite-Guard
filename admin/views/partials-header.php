<?php
/**
 * Shared admin header.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 *
 * @var string $view Current view slug (optional).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$screen = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : SPG_BY_PPROS_SLUG; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$nav    = array(
	SPG_BY_PPROS_SLUG               => __( 'Dashboard', 'spg-by-ppros' ),
	SPG_BY_PPROS_SLUG . '-ai'       => __( 'AI Visibility', 'spg-by-ppros' ),
	SPG_BY_PPROS_SLUG . '-a11y'     => __( 'Accessibility', 'spg-by-ppros' ),
	SPG_BY_PPROS_SLUG . '-health'   => __( 'Site Health', 'spg-by-ppros' ),
	SPG_BY_PPROS_SLUG . '-settings' => __( 'Settings', 'spg-by-ppros' ),
	SPG_BY_PPROS_SLUG . '-wizard'   => __( 'Wizard', 'spg-by-ppros' ),
	SPG_BY_PPROS_SLUG . '-pro'      => __( 'Go Pro', 'spg-by-ppros' ),
);
?>
<div class="wrap spg-by-ppros">
	<div class="spg-by-ppros-top">
		<div class="spg-by-ppros-brand">
			<span class="spg-by-ppros-logo" aria-hidden="true">S</span>
			<div>
				<h1><?php esc_html_e( 'SitePulse Guard', 'spg-by-ppros' ); ?></h1>
				<p><?php esc_html_e( 'AI visibility, real accessibility, and a lighter WordPress site — alongside Yoast or Rank Math.', 'spg-by-ppros' ); ?></p>
			</div>
		</div>
		<nav class="spg-by-ppros-nav" aria-label="<?php esc_attr_e( 'SitePulse Guard', 'spg-by-ppros' ); ?>">
			<?php foreach ( $nav as $slug => $label ) : ?>
				<a class="<?php echo $screen === $slug ? 'is-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	</div>
	<div class="spg-by-ppros-notice" data-spg-by-ppros-notice hidden></div>
