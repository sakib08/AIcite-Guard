<?php
/**
 * AI Visibility screen.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$llms        = new Aicite_Guard_Llms();
$basic       = $llms->get_basic();
$full        = $llms->get_full();
$generated   = (int) get_option( 'aicite_guard_llms_generated_at', 0 );
$score       = ( new Aicite_Guard_Ai_Score() )->calculate();
$schema_obj  = new Aicite_Guard_Schema();
$schema      = $schema_obj->suggestions();
$schema_status = $schema_obj->status();
$crawler     = ( new Aicite_Guard_Crawler() )->summary();
$tab         = isset( $_GET['file'] ) && 'full' === $_GET['file'] ? 'full' : 'basic'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<section class="acg-grid acg-grid--2">
	<article class="acg-card acg-score-card">
		<div class="acg-score <?php echo esc_attr( 'is-' . ( $score['score'] >= 80 ? 'good' : ( $score['score'] >= 55 ? 'ok' : 'bad' ) ) ); ?>">
			<strong><?php echo esc_html( (string) $score['score'] ); ?></strong>
			<span><?php esc_html_e( '/ 100', 'aicite-guard' ); ?></span>
		</div>
		<div>
			<h2><?php esc_html_e( 'AI Readiness Score', 'aicite-guard' ); ?></h2>
			<p class="acg-pill"><?php echo esc_html( $score['label'] ); ?></p>
			<p><?php echo esc_html( $score['summary'] ); ?></p>
			<button type="button" class="button button-primary" data-acg-action="regenerate-llms"><?php esc_html_e( 'One-click regenerate', 'aicite-guard' ); ?></button>
		</div>
	</article>
	<article class="acg-card">
		<h2><?php esc_html_e( 'AI Crawler Control', 'aicite-guard' ); ?></h2>
		<p><?php esc_html_e( 'Allow answer engines that cite sources, and block known training-only scrapers. Rules are appended to robots.txt — they do not replace Yoast or Rank Math robots settings.', 'aicite-guard' ); ?></p>
		<ul class="acg-list">
			<li><?php echo $crawler['allow_engines'] ? esc_html__( 'Answer engines: allowed', 'aicite-guard' ) : esc_html__( 'Answer engines: not allowed', 'aicite-guard' ); ?> (<?php echo esc_html( (string) $crawler['answer_count'] ); ?>)</li>
			<li><?php echo $crawler['block_training'] ? esc_html__( 'Training scrapers: blocked', 'aicite-guard' ) : esc_html__( 'Training scrapers: not blocked', 'aicite-guard' ); ?> (<?php echo esc_html( (string) $crawler['training_count'] ); ?>)</li>
		</ul>
		<p>
			<a class="button" href="<?php echo esc_url( $crawler['robots_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View robots.txt', 'aicite-guard' ); ?></a>
			<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aicite-guard-settings' ) ); ?>"><?php esc_html_e( 'Change crawler rules', 'aicite-guard' ); ?></a>
		</p>
	</article>
</section>

<section class="acg-card">
	<div class="acg-card__head">
		<h2><?php esc_html_e( 'Preview', 'aicite-guard' ); ?></h2>
		<p class="description">
			<?php
			echo $generated
				? esc_html( sprintf( /* translators: %s: human time */ __( 'Generated %s ago from published content. No paid AI required.', 'aicite-guard' ), human_time_diff( $generated ) ) )
				: esc_html__( 'Generate files to preview them.', 'aicite-guard' );
			?>
		</p>
	</div>
	<p class="acg-tabs">
		<a class="button <?php echo 'basic' === $tab ? 'button-primary' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=aicite-guard-ai' ) ); ?>"><?php esc_html_e( 'llms.txt', 'aicite-guard' ); ?></a>
		<a class="button <?php echo 'full' === $tab ? 'button-primary' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=aicite-guard-ai&file=full' ) ); ?>"><?php esc_html_e( 'llms-full.txt', 'aicite-guard' ); ?></a>
		<a class="button" href="<?php echo esc_url( home_url( 'basic' === $tab ? '/llms.txt' : '/llms-full.txt' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open in new tab', 'aicite-guard' ); ?></a>
	</p>
	<pre class="acg-preview" id="acg-llms-preview"><?php echo esc_html( 'full' === $tab ? $full : $basic ); ?></pre>
</section>

<section class="acg-grid acg-grid--2">
	<article class="acg-card">
		<h2><?php esc_html_e( 'What the score checked', 'aicite-guard' ); ?></h2>
		<ul class="acg-checks">
			<?php foreach ( $score['checks'] as $check ) : ?>
				<li class="<?php echo ! empty( $check['pass'] ) ? 'is-pass' : 'is-fail'; ?>">
					<strong><?php echo esc_html( $check['title'] ); ?></strong>
					<span><?php echo esc_html( $check['detail'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</article>
	<article class="acg-card">
		<h2><?php esc_html_e( 'Structured data', 'aicite-guard' ); ?></h2>
		<?php if ( ! empty( $schema_status['seo_active'] ) ) : ?>
			<p class="acg-pill"><?php echo esc_html( sprintf( /* translators: %s: SEO plugin name */ __( 'Deferred to %s', 'aicite-guard' ), $schema_status['provider_name'] ? $schema_status['provider_name'] : __( 'your SEO plugin', 'aicite-guard' ) ) ); ?></p>
			<p><?php esc_html_e( 'AIcite Guard is not printing JSON-LD because an SEO plugin already handles structured data. That avoids duplicate markup.', 'aicite-guard' ); ?></p>
		<?php elseif ( ! empty( $schema_status['outputting'] ) ) : ?>
			<p class="acg-pill"><?php esc_html_e( 'AIcite Guard outputting JSON-LD', 'aicite-guard' ); ?></p>
			<p><?php esc_html_e( 'Publishing WebSite, Organization, Article/WebPage, and FAQ schema in the page head when relevant.', 'aicite-guard' ); ?></p>
		<?php else : ?>
			<p class="acg-pill"><?php esc_html_e( 'Schema output is off', 'aicite-guard' ); ?></p>
			<p><?php esc_html_e( 'Turn on structured data in Settings so answer engines can identify your pages.', 'aicite-guard' ); ?></p>
		<?php endif; ?>
		<p><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=aicite-guard-settings' ) ); ?>"><?php esc_html_e( 'Schema settings', 'aicite-guard' ); ?></a></p>
		<ul class="acg-checks">
			<?php foreach ( $schema as $item ) : ?>
				<li class="is-<?php echo esc_attr( $item['status'] ); ?>">
					<strong><?php echo esc_html( $item['title'] ); ?></strong>
					<span><?php echo esc_html( $item['detail'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</article>
</section>
