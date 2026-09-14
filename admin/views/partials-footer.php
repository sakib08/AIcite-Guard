<?php
/**
 * Shared admin footer closer.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.
?>
	<p class="acg-footnote">
		<?php esc_html_e( 'AIcite Guard stays out of Yoast and Rank Math schema. Free features are meant to be useful on their own.', 'aicite-guard' ); ?>
	</p>
</div>
