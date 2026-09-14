<?php
/**
 * Dashboard.
 *
 * @package Aicite_Guard
 *
 * @var array $settings Settings.
 * @var array $scores   Stored scores.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$companions = Aicite_Guard_Admin::companions();
$ai         = isset( $scores['ai'] ) ? $scores['ai'] : array();
$a11y       = isset( $scores['a11y'] ) ? $scores['a11y'] : array();
$health     = isset( $scores['health'] ) ? $scores['health'] : array();
$generated  = (int) get_option( 'aicite_guard_llms_generated_at', 0 );

if ( ! $ai && Aicite_Guard_Settings::get_path( 'ai_visibility.enabled', true ) ) {
	$ai = ( new Aicite_Guard_Ai_Score() )->calculate();
}
if ( ! $health && Aicite_Guard_Settings::get_path( 'health.enabled', true ) ) {
	$health_report = ( new Aicite_Guard_Health() )->report();
	$health        = array(
		'score'   => $health_report['score'],
		'label'   => $health_report['label'],
		'summary' => $health_report['summary'],
	);
}

$cards = array(
	array(
		'key'     => 'ai',
		'title'   => __( 'AI Readiness', 'aicite-guard' ),
		'score'   => isset( $ai['score'] ) ? (int) $ai['score'] : 0,
		'label'   => isset( $ai['label'] ) ? $ai['label'] : __( 'Not scored yet', 'aicite-guard' ),
		'summary' => isset( $ai['summary'] ) ? $ai['summary'] : __( 'Generate llms.txt to get your first score.', 'aicite-guard' ),
		'link'    => admin_url( 'admin.php?page=aicite-guard-ai' ),
		'cta'     => __( 'Open AI Visibility', 'aicite-guard' ),
	),
	array(
		'key'     => 'a11y',
		'title'   => __( 'Accessibility', 'aicite-guard' ),
		'score'   => isset( $a11y['score'] ) ? (int) $a11y['score'] : 0,
		'label'   => isset( $a11y['label'] ) ? $a11y['label'] : __( 'Not scanned yet', 'aicite-guard' ),
		'summary' => isset( $a11y['summary'] ) ? $a11y['summary'] : __( 'Run a scan to find missing alt text and form labels.', 'aicite-guard' ),
		'link'    => admin_url( 'admin.php?page=aicite-guard-a11y' ),
		'cta'     => __( 'Open Accessibility', 'aicite-guard' ),
	),
	array(
		'key'     => 'health',
		'title'   => __( 'Site Health', 'aicite-guard' ),
		'score'   => isset( $health['score'] ) ? (int) $health['score'] : 0,
		'label'   => isset( $health['label'] ) ? $health['label'] : __( 'Not reviewed yet', 'aicite-guard' ),
		'summary' => isset( $health['summary'] ) ? $health['summary'] : __( 'Check unused plugins and overlap.', 'aicite-guard' ),
		'link'    => admin_url( 'admin.php?page=aicite-guard-health' ),
		'cta'     => __( 'Open Site Health', 'aicite-guard' ),
	),
);
?>
<section class="acg-grid acg-grid--3">
	<?php foreach ( $cards as $card ) : ?>
		<article class="acg-card acg-score-card">
			<div class="acg-score <?php echo esc_attr( 'is-' . ( $card['score'] >= 80 ? 'good' : ( $card['score'] >= 55 ? 'ok' : 'bad' ) ) ); ?>">
				<strong><?php echo esc_html( (string) $card['score'] ); ?></strong>
				<span><?php esc_html_e( '/ 100', 'aicite-guard' ); ?></span>
			</div>
			<div>
				<h2><?php echo esc_html( $card['title'] ); ?></h2>
				<p class="acg-pill"><?php echo esc_html( $card['label'] ); ?></p>
				<p><?php echo esc_html( $card['summary'] ); ?></p>
				<a class="button" href="<?php echo esc_url( $card['link'] ); ?>"><?php echo esc_html( $card['cta'] ); ?></a>
			</div>
		</article>
	<?php endforeach; ?>
</section>

<section class="acg-grid acg-grid--2">
	<article class="acg-card">
		<h2><?php esc_html_e( 'Quick actions', 'aicite-guard' ); ?></h2>
		<ul class="acg-actions">
			<li>
				<button type="button" class="button button-primary" data-acg-action="regenerate-llms">
					<?php esc_html_e( 'Regenerate llms.txt', 'aicite-guard' ); ?>
				</button>
				<span class="description">
					<?php
					echo $generated
						? esc_html( sprintf( /* translators: %s: time */ __( 'Last generated %s', 'aicite-guard' ), human_time_diff( $generated ) . ' ' . __( 'ago', 'aicite-guard' ) ) )
						: esc_html__( 'Not generated yet', 'aicite-guard' );
					?>
				</span>
			</li>
			<li>
				<a class="button" href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View llms.txt', 'aicite-guard' ); ?></a>
				<a class="button" href="<?php echo esc_url( home_url( '/llms-full.txt' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View llms-full.txt', 'aicite-guard' ); ?></a>
			</li>
			<li>
				<button type="button" class="button" data-acg-action="scan-a11y"><?php esc_html_e( 'Scan accessibility', 'aicite-guard' ); ?></button>
				<button type="button" class="button" data-acg-action="refresh-health"><?php esc_html_e( 'Refresh site health', 'aicite-guard' ); ?></button>
			</li>
		</ul>
	</article>

	<article class="acg-card">
		<h2><?php esc_html_e( 'Works alongside', 'aicite-guard' ); ?></h2>
		<p><?php esc_html_e( 'AIcite Guard does not replace your SEO plugin. It fills the gaps those plugins leave: AI files, accessibility fixes, and plugin bloat.', 'aicite-guard' ); ?></p>
		<ul class="acg-badges">
			<li class="<?php echo $companions['yoast'] ? 'is-on' : ''; ?>"><?php echo $companions['yoast'] ? esc_html__( 'Yoast detected', 'aicite-guard' ) : esc_html__( 'Yoast not active', 'aicite-guard' ); ?></li>
			<li class="<?php echo $companions['rankmath'] ? 'is-on' : ''; ?>"><?php echo $companions['rankmath'] ? esc_html__( 'Rank Math detected', 'aicite-guard' ) : esc_html__( 'Rank Math not active', 'aicite-guard' ); ?></li>
			<li class="<?php echo $companions['elementor'] ? 'is-on' : ''; ?>"><?php echo $companions['elementor'] ? esc_html__( 'Elementor detected', 'aicite-guard' ) : esc_html__( 'Elementor not active', 'aicite-guard' ); ?></li>
			<li class="<?php echo $companions['bricks'] ? 'is-on' : ''; ?>"><?php echo $companions['bricks'] ? esc_html__( 'Bricks detected', 'aicite-guard' ) : esc_html__( 'Bricks not active', 'aicite-guard' ); ?></li>
		</ul>
		<?php if ( empty( $settings['wizard_complete'] ) ) : ?>
			<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=aicite-guard-wizard' ) ); ?>"><?php esc_html_e( 'Start setup wizard', 'aicite-guard' ); ?></a></p>
		<?php endif; ?>
	</article>
</section>
