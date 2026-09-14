<?php
/**
 * Visitor accessibility widget markup.
 *
 * @package Aicite_Guard
 *
 * @var string $position Widget corner.
 * @var string $link     Optional statement URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.
?>
<div class="aicite-guard aicite-guard-a11y aicite-guard-a11y--<?php echo esc_attr( $position ); ?>" data-aicite-guard-widget>
	<button type="button" class="aicite-guard-a11y__toggle" aria-expanded="false" aria-controls="aicite-guard-a11y-panel">
		<span class="aicite-guard-a11y__icon" aria-hidden="true">A</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Accessibility options', 'aicite-guard' ); ?></span>
	</button>
	<div id="aicite-guard-a11y-panel" class="aicite-guard-a11y__panel" hidden>
		<p class="aicite-guard-a11y__title"><?php esc_html_e( 'Accessibility', 'aicite-guard' ); ?></p>
		<div class="aicite-guard-a11y__row">
			<span><?php esc_html_e( 'Text size', 'aicite-guard' ); ?></span>
			<div class="aicite-guard-a11y__btns">
				<button type="button" data-acg-action="font-down" aria-label="<?php esc_attr_e( 'Decrease text size', 'aicite-guard' ); ?>">A−</button>
				<button type="button" data-acg-action="font-up" aria-label="<?php esc_attr_e( 'Increase text size', 'aicite-guard' ); ?>">A+</button>
			</div>
		</div>
		<button type="button" class="aicite-guard-a11y__opt" data-acg-action="contrast"><?php esc_html_e( 'Higher contrast', 'aicite-guard' ); ?></button>
		<button type="button" class="aicite-guard-a11y__opt" data-acg-action="links"><?php esc_html_e( 'Highlight links', 'aicite-guard' ); ?></button>
		<button type="button" class="aicite-guard-a11y__opt" data-acg-action="spacing"><?php esc_html_e( 'More spacing', 'aicite-guard' ); ?></button>
		<button type="button" class="aicite-guard-a11y__opt" data-acg-action="motion"><?php esc_html_e( 'Reduce motion', 'aicite-guard' ); ?></button>
		<button type="button" class="aicite-guard-a11y__reset" data-acg-action="reset"><?php esc_html_e( 'Reset', 'aicite-guard' ); ?></button>
		<?php if ( $link ) : ?>
			<p class="aicite-guard-a11y__foot">
				<a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Accessibility statement', 'aicite-guard' ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</div>
