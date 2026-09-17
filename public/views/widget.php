<?php
/**
 * Visitor accessibility widget markup.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 *
 * @var string $position Widget corner.
 * @var string $link     Optional statement URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.
?>
<div class="spg-by-ppros spg-by-ppros-a11y spg-by-ppros-a11y--<?php echo esc_attr( $position ); ?>" data-spg-by-ppros-widget>
	<button type="button" class="spg-by-ppros-a11y__toggle" aria-expanded="false" aria-controls="spg-by-ppros-a11y-panel">
		<span class="spg-by-ppros-a11y__icon" aria-hidden="true">A</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Accessibility options', 'spg-by-ppros' ); ?></span>
	</button>
	<div id="spg-by-ppros-a11y-panel" class="spg-by-ppros-a11y__panel" hidden>
		<p class="spg-by-ppros-a11y__title"><?php esc_html_e( 'Accessibility', 'spg-by-ppros' ); ?></p>
		<div class="spg-by-ppros-a11y__row">
			<span><?php esc_html_e( 'Text size', 'spg-by-ppros' ); ?></span>
			<div class="spg-by-ppros-a11y__btns">
				<button type="button" data-spg-by-ppros-action="font-down" aria-label="<?php esc_attr_e( 'Decrease text size', 'spg-by-ppros' ); ?>">A−</button>
				<button type="button" data-spg-by-ppros-action="font-up" aria-label="<?php esc_attr_e( 'Increase text size', 'spg-by-ppros' ); ?>">A+</button>
			</div>
		</div>
		<button type="button" class="spg-by-ppros-a11y__opt" data-spg-by-ppros-action="contrast"><?php esc_html_e( 'Higher contrast', 'spg-by-ppros' ); ?></button>
		<button type="button" class="spg-by-ppros-a11y__opt" data-spg-by-ppros-action="links"><?php esc_html_e( 'Highlight links', 'spg-by-ppros' ); ?></button>
		<button type="button" class="spg-by-ppros-a11y__opt" data-spg-by-ppros-action="spacing"><?php esc_html_e( 'More spacing', 'spg-by-ppros' ); ?></button>
		<button type="button" class="spg-by-ppros-a11y__opt" data-spg-by-ppros-action="motion"><?php esc_html_e( 'Reduce motion', 'spg-by-ppros' ); ?></button>
		<button type="button" class="spg-by-ppros-a11y__reset" data-spg-by-ppros-action="reset"><?php esc_html_e( 'Reset', 'spg-by-ppros' ); ?></button>
		<?php if ( $link ) : ?>
			<p class="spg-by-ppros-a11y__foot">
				<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Accessibility statement', 'spg-by-ppros' ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</div>
