<?php
/**
 * Shared admin footer closer.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.
?>
	<p class="spg-by-ppros-footnote">
		<?php esc_html_e( 'SitePulse Guard stays out of Yoast and Rank Math schema. Free features are meant to be useful on their own.', 'spg-by-ppros' ); ?>
	</p>
</div>
