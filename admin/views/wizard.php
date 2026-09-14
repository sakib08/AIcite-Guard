<?php
/**
 * One-click setup wizard.
 *
 * @package Aicite_Guard
 *
 * @var array $settings Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$types = get_post_types( array( 'public' => true ), 'objects' );
?>
<section class="acg-card acg-wizard" data-acg-wizard>
	<ol class="acg-steps" aria-hidden="true">
		<li class="is-active"><?php esc_html_e( 'Welcome', 'aicite-guard' ); ?></li>
		<li><?php esc_html_e( 'AI Visibility', 'aicite-guard' ); ?></li>
		<li><?php esc_html_e( 'Accessibility', 'aicite-guard' ); ?></li>
		<li><?php esc_html_e( 'Health', 'aicite-guard' ); ?></li>
	</ol>

	<div class="acg-step is-active" data-step="1">
		<h2><?php esc_html_e( 'Welcome to AIcite Guard', 'aicite-guard' ); ?></h2>
		<p><?php esc_html_e( 'A lightweight companion for Yoast or Rank Math. In a minute we will turn on AI files, accessibility tools, and a health check — nothing is locked behind Pro.', 'aicite-guard' ); ?></p>
		<button type="button" class="button button-primary" data-acg-next><?php esc_html_e( 'Continue', 'aicite-guard' ); ?></button>
	</div>

	<div class="acg-step" data-step="2" hidden>
		<h2><?php esc_html_e( 'AI search visibility', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="wiz_ai_enabled" value="1" <?php checked( ! empty( $settings['ai_visibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Generate llms.txt automatically', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="wiz_allow_engines" value="1" <?php checked( ! empty( $settings['crawler']['allow_answer_engines'] ) ); ?> />
			<span><?php esc_html_e( 'Allow answer engines in robots.txt', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="wiz_block_training" value="1" <?php checked( ! empty( $settings['crawler']['block_training'] ) ); ?> />
			<span><?php esc_html_e( 'Block training-only scrapers', 'aicite-guard' ); ?></span>
		</label>
		<fieldset>
			<legend><?php esc_html_e( 'Include these post types', 'aicite-guard' ); ?></legend>
			<?php foreach ( $types as $type ) : ?>
				<?php if ( 'attachment' === $type->name ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<label>
					<input type="checkbox" name="wiz_types[]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, $settings['ai_visibility']['post_types'], true ) ); ?> />
					<?php echo esc_html( $type->labels->name ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
		<p>
			<button type="button" class="button" data-acg-prev><?php esc_html_e( 'Back', 'aicite-guard' ); ?></button>
			<button type="button" class="button button-primary" data-acg-next><?php esc_html_e( 'Continue', 'aicite-guard' ); ?></button>
		</p>
	</div>

	<div class="acg-step" data-step="3" hidden>
		<h2><?php esc_html_e( 'Accessibility', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="wiz_a11y" value="1" <?php checked( ! empty( $settings['accessibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Scan for missing alt text and form labels', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="wiz_widget" value="1" <?php checked( ! empty( $settings['accessibility']['widget_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Show the visitor accessibility widget', 'aicite-guard' ); ?></span>
		</label>
		<p class="description"><?php esc_html_e( 'Suggested alt text is never applied until you approve it.', 'aicite-guard' ); ?></p>
		<p>
			<button type="button" class="button" data-acg-prev><?php esc_html_e( 'Back', 'aicite-guard' ); ?></button>
			<button type="button" class="button button-primary" data-acg-next><?php esc_html_e( 'Continue', 'aicite-guard' ); ?></button>
		</p>
	</div>

	<div class="acg-step" data-step="4" hidden>
		<h2><?php esc_html_e( 'Site health', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="wiz_health" value="1" <?php checked( ! empty( $settings['health']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Show unused plugins, conflicts, and a health score', 'aicite-guard' ); ?></span>
		</label>
		<p><?php esc_html_e( 'Finish to generate your first llms.txt and open the dashboard.', 'aicite-guard' ); ?></p>
		<p>
			<button type="button" class="button" data-acg-prev><?php esc_html_e( 'Back', 'aicite-guard' ); ?></button>
			<button type="button" class="button button-primary" data-acg-finish><?php esc_html_e( 'Finish setup', 'aicite-guard' ); ?></button>
		</p>
	</div>
</section>
