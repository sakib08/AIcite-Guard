<?php
/**
 * Settings form.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 *
 * @var array $settings Settings.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$option    = Sitepulse_Guard_By_Plugin_Pros_Settings::OPTION_KEY;
$types     = get_post_types( array( 'public' => true ), 'objects' );
$remaining = Sitepulse_Guard_By_Plugin_Pros_Ai::remaining();
$ai_on     = Sitepulse_Guard_By_Plugin_Pros_Ai::is_available();
?>
<form method="post" action="options.php" class="spg-by-ppros-settings">
	<?php
	settings_fields( 'spg_by_ppros_settings' );
	settings_errors( 'spg_by_ppros_settings' );
	?>

	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'AI Visibility', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][enabled]" value="1" <?php checked( ! empty( $settings['ai_visibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Serve llms.txt and llms-full.txt', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][include_excerpts]" value="1" <?php checked( ! empty( $settings['ai_visibility']['include_excerpts'] ) ); ?> />
			<span><?php esc_html_e( 'Include short excerpts', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai_visibility][auto_update]" value="1" <?php checked( ! empty( $settings['ai_visibility']['auto_update'] ) ); ?> />
			<span><?php esc_html_e( 'Invalidate files when content is saved', 'spg-by-ppros' ); ?></span>
		</label>
		<p>
			<label for="spg-by-ppros-max-items"><?php esc_html_e( 'Maximum items per file', 'spg-by-ppros' ); ?></label><br />
			<input id="spg-by-ppros-max-items" type="number" min="5" max="200" name="<?php echo esc_attr( $option ); ?>[ai_visibility][max_items]" value="<?php echo esc_attr( (string) $settings['ai_visibility']['max_items'] ); ?>" />
		</p>
		<fieldset>
			<legend><?php esc_html_e( 'Post types to include', 'spg-by-ppros' ); ?></legend>
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

	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'AI Crawler Control', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[crawler][robots_integration]" value="1" <?php checked( ! empty( $settings['crawler']['robots_integration'] ) ); ?> />
			<span><?php esc_html_e( 'Write rules into robots.txt', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[crawler][allow_answer_engines]" value="1" <?php checked( ! empty( $settings['crawler']['allow_answer_engines'] ) ); ?> />
			<span><?php esc_html_e( 'Allow answer engines (ChatGPT, Claude, Perplexity, and similar)', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[crawler][block_training]" value="1" <?php checked( ! empty( $settings['crawler']['block_training'] ) ); ?> />
			<span><?php esc_html_e( 'Block known training-only scrapers', 'spg-by-ppros' ); ?></span>
		</label>
	</section>

	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Structured data (JSON-LD)', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[schema][enabled]" value="1" <?php checked( ! empty( $settings['schema']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Output WebSite, Organization, Article/WebPage, and FAQ schema', 'spg-by-ppros' ); ?></span>
		</label>
		<p class="description"><?php esc_html_e( 'SitePulse Guard only prints JSON-LD when Yoast, Rank Math, or a similar SEO plugin is not active — so nothing conflicts.', 'spg-by-ppros' ); ?></p>
	</section>

	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Accessibility', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[accessibility][enabled]" value="1" <?php checked( ! empty( $settings['accessibility']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Enable accessibility tools', 'spg-by-ppros' ); ?></span>
		</label>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[accessibility][widget_enabled]" value="1" <?php checked( ! empty( $settings['accessibility']['widget_enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Show visitor accessibility widget', 'spg-by-ppros' ); ?></span>
		</label>
		<p>
			<label for="spg-by-ppros-widget-pos"><?php esc_html_e( 'Widget position', 'spg-by-ppros' ); ?></label><br />
			<select id="spg-by-ppros-widget-pos" name="<?php echo esc_attr( $option ); ?>[accessibility][widget_position]">
				<?php
				$positions = array(
					'bottom-right' => __( 'Bottom right', 'spg-by-ppros' ),
					'bottom-left'  => __( 'Bottom left', 'spg-by-ppros' ),
					'top-right'    => __( 'Top right', 'spg-by-ppros' ),
					'top-left'     => __( 'Top left', 'spg-by-ppros' ),
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

	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Site Health', 'spg-by-ppros' ); ?></h2>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[health][enabled]" value="1" <?php checked( ! empty( $settings['health']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Enable Site Health Guardian', 'spg-by-ppros' ); ?></span>
		</label>
	</section>

	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Optional AI alt text', 'spg-by-ppros' ); ?></h2>
		<p><?php esc_html_e( 'Core tools never call an AI provider. Optional alt-text improvement uses the WordPress AI Client and the provider the site owner already configured under Settings → Connectors. This plugin does not store API keys.', 'spg-by-ppros' ); ?></p>
		<label class="spg-by-ppros-switch">
			<input type="checkbox" name="<?php echo esc_attr( $option ); ?>[ai][enabled]" value="1" <?php checked( ! empty( $settings['ai']['enabled'] ) ); ?> />
			<span><?php esc_html_e( 'Allow AI-assisted alt text when a site-wide AI provider is available', 'spg-by-ppros' ); ?></span>
		</label>
		<p class="description"><?php echo esc_html( Sitepulse_Guard_By_Plugin_Pros_Ai::status_message() ); ?></p>
		<?php if ( $ai_on ) : ?>
			<p class="description"><?php echo esc_html( sprintf( /* translators: %d: remaining */ __( '%d free AI generations remaining this month.', 'spg-by-ppros' ), $remaining ) ); ?></p>
		<?php endif; ?>
	</section>

	<?php submit_button( __( 'Save settings', 'spg-by-ppros' ) ); ?>
</form>
