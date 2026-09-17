<?php
/**
 * AI Visibility screen.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$llms        = new Sitepulse_Guard_By_Plugin_Pros_Llms();
$basic       = $llms->get_basic();
$full        = $llms->get_full();
$generated   = (int) get_option( 'spg_by_ppros_llms_generated_at', 0 );
$score       = ( new Sitepulse_Guard_By_Plugin_Pros_Ai_Score() )->calculate();
$schema_obj  = new Sitepulse_Guard_By_Plugin_Pros_Schema();
$schema      = $schema_obj->suggestions();
$schema_status = $schema_obj->status();
$crawler     = ( new Sitepulse_Guard_By_Plugin_Pros_Crawler() )->summary();
$tab         = isset( $_GET['file'] ) && 'full' === $_GET['file'] ? 'full' : 'basic'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<section class="spg-by-ppros-grid spg-by-ppros-grid--2">
	<article class="spg-by-ppros-card spg-by-ppros-score-card">
		<div class="spg-by-ppros-score <?php echo esc_attr( 'is-' . ( $score['score'] >= 80 ? 'good' : ( $score['score'] >= 55 ? 'ok' : 'bad' ) ) ); ?>">
			<strong><?php echo esc_html( (string) $score['score'] ); ?></strong>
			<span><?php esc_html_e( '/ 100', 'spg-by-ppros' ); ?></span>
		</div>
		<div>
			<h2><?php esc_html_e( 'AI Readiness Score', 'spg-by-ppros' ); ?></h2>
			<p class="spg-by-ppros-pill"><?php echo esc_html( $score['label'] ); ?></p>
			<p><?php echo esc_html( $score['summary'] ); ?></p>
			<button type="button" class="button button-primary" data-spg-by-ppros-action="regenerate-llms"><?php esc_html_e( 'One-click regenerate', 'spg-by-ppros' ); ?></button>
		</div>
	</article>
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'AI Crawler Control', 'spg-by-ppros' ); ?></h2>
		<p><?php esc_html_e( 'Allow answer engines that cite sources, and block known training-only scrapers. Rules are appended to robots.txt — they do not replace Yoast or Rank Math robots settings.', 'spg-by-ppros' ); ?></p>
		<ul class="spg-by-ppros-list">
			<li><?php echo $crawler['allow_engines'] ? esc_html__( 'Answer engines: allowed', 'spg-by-ppros' ) : esc_html__( 'Answer engines: not allowed', 'spg-by-ppros' ); ?> (<?php echo esc_html( (string) $crawler['answer_count'] ); ?>)</li>
			<li><?php echo $crawler['block_training'] ? esc_html__( 'Training scrapers: blocked', 'spg-by-ppros' ) : esc_html__( 'Training scrapers: not blocked', 'spg-by-ppros' ); ?> (<?php echo esc_html( (string) $crawler['training_count'] ); ?>)</li>
		</ul>
		<p>
			<a class="button" href="<?php echo esc_url( $crawler['robots_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View robots.txt', 'spg-by-ppros' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=spg-by-ppros-settings' ) ); ?>"><?php esc_html_e( 'Change crawler rules', 'spg-by-ppros' ); ?></a>
		</p>
	</article>
</section>

<section class="spg-by-ppros-card">
	<div class="spg-by-ppros-card__head">
		<h2><?php esc_html_e( 'Preview', 'spg-by-ppros' ); ?></h2>
		<p class="description">
			<?php
			echo $generated
				? esc_html( sprintf( /* translators: %s: human time */ __( 'Generated %s ago from published content. No paid AI required.', 'spg-by-ppros' ), human_time_diff( $generated ) ) )
				: esc_html__( 'Generate files to preview them.', 'spg-by-ppros' );
			?>
		</p>
	</div>
	<p class="spg-by-ppros-tabs">
		<a class="button <?php echo 'basic' === $tab ? 'button-primary' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=spg-by-ppros-ai' ) ); ?>"><?php esc_html_e( 'llms.txt', 'spg-by-ppros' ); ?></a>
		<a class="button <?php echo 'full' === $tab ? 'button-primary' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=spg-by-ppros-ai&file=full' ) ); ?>"><?php esc_html_e( 'llms-full.txt', 'spg-by-ppros' ); ?></a>
		<a class="button" href="<?php echo esc_url( home_url( 'basic' === $tab ? '/llms.txt' : '/llms-full.txt' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open in new tab', 'spg-by-ppros' ); ?></a>
	</p>
	<pre class="spg-by-ppros-preview" id="spg-by-ppros-llms-preview"><?php echo esc_html( 'full' === $tab ? $full : $basic ); ?></pre>
</section>

<section class="spg-by-ppros-grid spg-by-ppros-grid--2">
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'What the score checked', 'spg-by-ppros' ); ?></h2>
		<ul class="spg-by-ppros-checks">
			<?php foreach ( $score['checks'] as $check ) : ?>
				<li class="<?php echo ! empty( $check['pass'] ) ? 'is-pass' : 'is-fail'; ?>">
					<strong><?php echo esc_html( $check['title'] ); ?></strong>
					<span><?php echo esc_html( $check['detail'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</article>
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Structured data', 'spg-by-ppros' ); ?></h2>
		<?php if ( ! empty( $schema_status['seo_active'] ) ) : ?>
			<p class="spg-by-ppros-pill"><?php echo esc_html( sprintf( /* translators: %s: SEO plugin name */ __( 'Deferred to %s', 'spg-by-ppros' ), $schema_status['provider_name'] ? $schema_status['provider_name'] : __( 'your SEO plugin', 'spg-by-ppros' ) ) ); ?></p>
			<p><?php esc_html_e( 'SitePulse Guard is not printing JSON-LD because an SEO plugin already handles structured data. That avoids duplicate markup.', 'spg-by-ppros' ); ?></p>
		<?php elseif ( ! empty( $schema_status['outputting'] ) ) : ?>
			<p class="spg-by-ppros-pill"><?php esc_html_e( 'SitePulse Guard outputting JSON-LD', 'spg-by-ppros' ); ?></p>
			<p><?php esc_html_e( 'Publishing WebSite, Organization, Article/WebPage, and FAQ schema in the page head when relevant.', 'spg-by-ppros' ); ?></p>
		<?php else : ?>
			<p class="spg-by-ppros-pill"><?php esc_html_e( 'Schema output is off', 'spg-by-ppros' ); ?></p>
			<p><?php esc_html_e( 'Turn on structured data in Settings so answer engines can identify your pages.', 'spg-by-ppros' ); ?></p>
		<?php endif; ?>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=spg-by-ppros-settings' ) ); ?>"><?php esc_html_e( 'Schema settings', 'spg-by-ppros' ); ?></a></p>
		<ul class="spg-by-ppros-checks">
			<?php foreach ( $schema as $item ) : ?>
				<li class="is-<?php echo esc_attr( $item['status'] ); ?>">
					<strong><?php echo esc_html( $item['title'] ); ?></strong>
					<span><?php echo esc_html( $item['detail'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</article>
</section>
