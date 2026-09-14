<?php
/**
 * Settings form.
 *
 * @package Aicite_Guard
 *
 * @var array $settings Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$option     = Aicite_Guard_Settings::OPTION_KEY;
$types      = get_post_types( array( 'public' => true ), 'objects' );
$has_key    = Aicite_Guard_Settings::get_ai_key();
$remaining  = Aicite_Guard_Ai::remaining();
?>
<form method="post" action="options.php" class="acg-settings">
	<?php
	settings_fields( 'aicite_guard_settings' );
	settings_errors( 'aicite_guard_settings' );
	?>

	<section class="acg-card">
		<h2><?php esc_html_e( 'AI Visibility', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][enabled]" value="1" <?php checked( ! empty( $settings['ai_visibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Serve llms.txt and llms-full.txt', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][include_excerpts]" value="1" <?php checked( ! empty( $settings['ai_visibility']['include_excerpts'] ) ); ?> />
			<span><?php esc_html_e( 'Include short excerpts', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][auto_update]" value="1" <?php checked( ! empty( $settings['ai_visibility']['auto_update'] ) ); ?> />
			<span><?php esc_html_e( 'Invalidate files when content is saved', 'aicite-guard' ); ?></span>
		</label>
		<p>
			<label for="acg-max-items"><?php esc_html_e( 'Maximum items per file', 'aicite-guard' ); ?></label><br />
			<input id="acg-max-items" type="number" min="5" max="200" name="<?php echo esc_attr( $option ); ?>[ai_visibility][max_items]" value="<?php echo esc_attr( (string) $settings['ai_visibility']['max_items'] ); ?>" />
		</p>
		<fieldset>
			<legend><?php esc_html_e( 'Post types to include', 'aicite-guard' ); ?></legend>
			<?php foreach ( $types as $type ) : ?>
				<?php if ( 'attachment' === $type->name ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][post_types][]" value="<?php echo esc_attr( $type->name ); ?>" <?php checked( in_array( $type->name, $settings['ai_visibility']['post_types'], true ) ); ?> />
					<?php echo esc_html( $type->labels->name ); ?>
				</label>
			<?php endforeach; ?>
		</fieldset>
	</section>

	<section class="acg-card">
		<h2><?php esc_html_e( 'AI Crawler Control', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[crawler][robots_integration]" value="1" <?php checked( ! empty( $settings['crawler']['robots_integration'] ) ); ?> />
			<span><?php esc_html_e( 'Write rules into robots.txt', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[crawler][allow_answer_engines]" value="1" <?php checked( ! empty( $settings['crawler']['allow_answer_engines'] ) ); ?> />
			<span><?php esc_html_e( 'Allow answer engines (ChatGPT, Claude, Perplexity, and similar)', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[crawler][block_training]" value="1" <?php checked( ! empty( $settings['crawler']['block_training'] ) ); ?> />
			<span><?php esc_html_e( 'Block known training-only scrapers', 'aicite-guard' ); ?></span>
		</label>
	</section>

	<section class="acg-card">
		<h2><?php esc_html_e( 'Structured data (JSON-LD)', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[schema][enabled]" value="1" <?php checked( ! empty( $settings['schema']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Output WebSite, Organization, Article/WebPage, and FAQ schema', 'aicite-guard' ); ?></span>
		</label>
		<p class="description"><?php esc_html_e( 'AIcite Guard only prints JSON-LD when Yoast, Rank Math, or a similar SEO plugin is not active — so nothing conflicts.', 'aicite-guard' ); ?></p>
	</section>

	<section class="acg-card">
		<h2><?php esc_html_e( 'Accessibility', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[accessibility][enabled]" value="1" <?php checked( ! empty( $settings['accessibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Enable accessibility tools', 'aicite-guard' ); ?></span>
		</label>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[accessibility][widget_enabled]" value="1" <?php checked( ! empty( $settings['accessibility']['widget_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Show visitor accessibility widget', 'aicite-guard' ); ?></span>
		</label>
		<p>
			<label for="acg-widget-pos"><?php esc_html_e( 'Widget position', 'aicite-guard' ); ?></label><br />
			<select id="acg-widget-pos" name="<?php echo esc_attr( $option ); ?>[accessibility][widget_position]">
				<?php
				$positions = array(
					'bottom-right' => __( 'Bottom right', 'aicite-guard' ),
					'bottom-left'  => __( 'Bottom left', 'aicite-guard' ),
					'top-right'    => __( 'Top right', 'aicite-guard' ),
					'top-left'     => __( 'Top left', 'aicite-guard' ),
				);
				foreach ( $positions as $value => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $value ),
						selected( $settings['accessibility']['widget_position'], $value, false ),
						esc_html( $label )
					);
				}
				?>
			</select>
		</p>
	</section>

	<section class="acg-card">
		<h2><?php esc_html_e( 'Site Health', 'aicite-guard' ); ?></h2>
		<label class="acg-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[health][enabled]" value="1" <?php checked( ! empty( $settings['health']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Enable Site Health Guardian', 'aicite-guard' ); ?></span>
		</label>
	</section>

	<section class="acg-card">
		<h2><?php esc_html_e( 'Bring Your Own AI Key', 'aicite-guard' ); ?></h2>
		<p><?php esc_html_e( 'Optional. Rule-based generation works without a key. A key unlocks better alt text. The free plan includes 20 AI generations per month.', 'aicite-guard' ); ?></p>
		<p>
			<label for="acg-ai-provider"><?php esc_html_e( 'Provider', 'aicite-guard' ); ?></label><br />
			<select id="acg-ai-provider" name="<?php echo esc_attr( $option ); ?>[ai][provider]">
				<option value="none" <?php selected( $settings['ai']['provider'], 'none' ); ?>><?php esc_html_e( 'None (rule-based only)', 'aicite-guard' ); ?></option>
				<option value="openai" <?php selected( $settings['ai']['provider'], 'openai' ); ?>><?php esc_html_e( 'OpenAI', 'aicite-guard' ); ?></option>
			</select>
		</p>
		<p>
			<label for="acg-ai-key"><?php esc_html_e( 'API key', 'aicite-guard' ); ?></label><br />
			<input id="acg-ai-key" class="regular-text" type="password" name="aicite_guard_ai_key" value="<?php echo $has_key ? '********' : ''; ?>" autocomplete="off" />
		</p>
		<p class="description"><?php echo esc_html( sprintf( /* translators: %d: remaining */ __( '%d free AI generations remaining this month.', 'aicite-guard' ), $remaining ) ); ?></p>
	</section>

	<?php submit_button( __( 'Save settings', 'aicite-guard' ) ); ?>
</form>
