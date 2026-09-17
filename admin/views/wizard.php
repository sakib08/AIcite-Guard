<?php
/**
 * One-click setup wizard.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 *
 * @var array $settings Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$types = get_post_types( array( 'public' => true ), 'objects' );
?>
<section class="spg-by-ppros-card spg-by-ppros-wizard" data-spg-by-ppros-wizard>
	<ol class="spg-by-ppros-steps" aria-hidden="true">
		<li class="is-active"><?php esc_html_e( 'Welcome', 'spg-by-ppros' ); ?></li>
		<li><?php esc_html_e( 'AI Visibility', 'spg-by-ppros' ); ?></li>
		<li><?php esc_html_e( 'Accessibility', 'spg-by-ppros' ); ?></li>
		<li><?php esc_html_e( 'Health', 'spg-by-ppros' ); ?></li>
	</ol>

	<div class="spg-by-ppros-step is-active" data-step="1">
		<h2><?php esc_html_e( 'Welcome to SitePulse Guard', 'spg-by-ppros' ); ?></h2>
		<p><?php esc_html_e( 'A lightweight companion for Yoast or Rank Math. In a minute we will turn on AI files, accessibility tools, and a health check — nothing is locked behind Pro.', 'spg-by-ppros' ); ?></p>
		<button type="button" class="button button-primary" data-spg-by-ppros-next><?php esc_html_e( 'Continue', 'spg-by-ppros' ); ?></button>
	</div>

	<div class="spg-by-ppros-step" data-step="2" hidden>
		<h2><?php esc_html_e( 'AI search visibility', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="wiz_ai_enabled" value="1" <?php checked( ! empty( $settings['ai_visibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Generate llms.txt automatically', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="wiz_allow_engines" value="1" <?php checked( ! empty( $settings['crawler']['allow_answer_engines'] ) ); ?> />
			<span><?php esc_html_e( 'Allow answer engines in robots.txt', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="wiz_block_training" value="1" <?php checked( ! empty( $settings['crawler']['block_training'] ) ); ?> />
			<span><?php esc_html_e( 'Block training-only scrapers', 'spg-by-ppros' ); ?></span>
		</label>
		<fieldset>
			<legend><?php esc_html_e( 'Include these post types', 'spg-by-ppros' ); ?></legend>
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
			<button type="button" class="button" data-spg-by-ppros-prev><?php esc_html_e( 'Back', 'spg-by-ppros' ); ?></button>
			<button type="button" class="button button-primary" data-spg-by-ppros-next><?php esc_html_e( 'Continue', 'spg-by-ppros' ); ?></button>
		</p>
	</div>

	<div class="spg-by-ppros-step" data-step="3" hidden>
		<h2><?php esc_html_e( 'Accessibility', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="wiz_a11y" value="1" <?php checked( ! empty( $settings['accessibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Scan for missing alt text and form labels', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="wiz_widget" value="1" <?php checked( ! empty( $settings['accessibility']['widget_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Show the visitor accessibility widget', 'spg-by-ppros' ); ?></span>
		</label>
		<p class="description"><?php esc_html_e( 'Suggested alt text is never applied until you approve it.', 'spg-by-ppros' ); ?></p>
		<p>
			<button type="button" class="button" data-spg-by-ppros-prev><?php esc_html_e( 'Back', 'spg-by-ppros' ); ?></button>
			<button type="button" class="button button-primary" data-spg-by-ppros-next><?php esc_html_e( 'Continue', 'spg-by-ppros' ); ?></button>
		</p>
	</div>

	<div class="spg-by-ppros-step" data-step="4" hidden>
		<h2><?php esc_html_e( 'Site health', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="wiz_health" value="1" <?php checked( ! empty( $settings['health']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Show unused plugins, conflicts, and a health score', 'spg-by-ppros' ); ?></span>
		</label>
		<p><?php esc_html_e( 'Finish to generate your first llms.txt and open the dashboard.', 'spg-by-ppros' ); ?></p>
		<p>
			<button type="button" class="button" data-spg-by-ppros-prev><?php esc_html_e( 'Back', 'spg-by-ppros' ); ?></button>
			<button type="button" class="button button-primary" data-spg-by-ppros-finish><?php esc_html_e( 'Finish setup', 'spg-by-ppros' ); ?></button>
		</p>
	</div>
</section>
