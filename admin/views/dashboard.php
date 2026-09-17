<?php
/**
 * Dashboard.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 *
 * @var array $settings Settings.
 * @var array $scores   Stored scores.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$companions = Sitepulse_Guard_By_Plugin_Pros_Admin::companions();
$ai         = isset( $scores['ai'] ) ? $scores['ai'] : array();
$a11y       = isset( $scores['a11y'] ) ? $scores['a11y'] : array();
$health     = isset( $scores['health'] ) ? $scores['health'] : array();
$generated  = (int) get_option( 'spg_by_ppros_llms_generated_at', 0 );

if ( ! $ai && Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'ai_visibility.enabled', true ) ) {
	$ai = ( new Sitepulse_Guard_By_Plugin_Pros_Ai_Score() )->calculate();
}
if ( ! $health && Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'health.enabled', true ) ) {
	$health_report = ( new Sitepulse_Guard_By_Plugin_Pros_Health() )->report();
	$health        = array(
		'score'   => $health_report['score'],
		'label'   => $health_report['label'],
		'summary' => $health_report['summary'],
	);
}

$cards = array(
	array(
		'key'     => 'ai',
		'title'   => __( 'AI Readiness', 'spg-by-ppros' ),
		'score'   => isset( $ai['score'] ) ? (int) $ai['score'] : 0,
		'label'   => isset( $ai['label'] ) ? $ai['label'] : __( 'Not scored yet', 'spg-by-ppros' ),
		'summary' => isset( $ai['summary'] ) ? $ai['summary'] : __( 'Generate llms.txt to get your first score.', 'spg-by-ppros' ),
		'link'    => admin_url( 'admin.php?page=spg-by-ppros-ai' ),
		'cta'     => __( 'Open AI Visibility', 'spg-by-ppros' ),
	),
	array(
		'key'     => 'a11y',
		'title'   => __( 'Accessibility', 'spg-by-ppros' ),
		'score'   => isset( $a11y['score'] ) ? (int) $a11y['score'] : 0,
		'label'   => isset( $a11y['label'] ) ? $a11y['label'] : __( 'Not scanned yet', 'spg-by-ppros' ),
		'summary' => isset( $a11y['summary'] ) ? $a11y['summary'] : __( 'Run a scan to find missing alt text and form labels.', 'spg-by-ppros' ),
		'link'    => admin_url( 'admin.php?page=spg-by-ppros-a11y' ),
		'cta'     => __( 'Open Accessibility', 'spg-by-ppros' ),
	),
	array(
		'key'     => 'health',
		'title'   => __( 'Site Health', 'spg-by-ppros' ),
		'score'   => isset( $health['score'] ) ? (int) $health['score'] : 0,
		'label'   => isset( $health['label'] ) ? $health['label'] : __( 'Not reviewed yet', 'spg-by-ppros' ),
		'summary' => isset( $health['summary'] ) ? $health['summary'] : __( 'Check unused plugins and overlap.', 'spg-by-ppros' ),
		'link'    => admin_url( 'admin.php?page=spg-by-ppros-health' ),
		'cta'     => __( 'Open Site Health', 'spg-by-ppros' ),
	),
);
?>
<section class="spg-by-ppros-grid spg-by-ppros-grid--3">
	<?php foreach ( $cards as $card ) : ?>
		<article class="spg-by-ppros-card spg-by-ppros-score-card">
			<div class="spg-by-ppros-score <?php echo esc_attr( 'is-' . ( $card['score'] >= 80 ? 'good' : ( $card['score'] >= 55 ? 'ok' : 'bad' ) ) ); ?>">
				<strong><?php echo esc_html( (string) $card['score'] ); ?></strong>
				<span><?php esc_html_e( '/ 100', 'spg-by-ppros' ); ?></span>
			</div>
			<div>
				<h2><?php echo esc_html( $card['title'] ); ?></h2>
				<p class="spg-by-ppros-pill"><?php echo esc_html( $card['label'] ); ?></p>
				<p><?php echo esc_html( $card['summary'] ); ?></p>
				<a class="button" href="<?php echo esc_url( $card['link'] ); ?>"><?php echo esc_html( $card['cta'] ); ?></a>
			</div>
		</article>
	<?php endforeach; ?>
</section>

<section class="spg-by-ppros-grid spg-by-ppros-grid--2">
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Quick actions', 'spg-by-ppros' ); ?></h2>
		<ul class="spg-by-ppros-actions">
			<li>
				<button type="button" class="button button-primary" data-spg-by-ppros-action="regenerate-llms">
					<?php esc_html_e( 'Regenerate llms.txt', 'spg-by-ppros' ); ?>
				</button>
				<span class="description">
					<?php
					echo $generated
						? esc_html( sprintf( /* translators: %s: time */ __( 'Last generated %s', 'spg-by-ppros' ), human_time_diff( $generated ) . ' ' . __( 'ago', 'spg-by-ppros' ) ) )
						: esc_html__( 'Not generated yet', 'spg-by-ppros' );
					?>
				</span>
			</li>
			<li>
				<a class="button" href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View llms.txt', 'spg-by-ppros' ); ?></a>
				<a class="button" href="<?php echo esc_url( home_url( '/llms-full.txt' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View llms-full.txt', 'spg-by-ppros' ); ?></a>
			</li>
			<li>
				<button type="button" class="button" data-spg-by-ppros-action="scan-a11y"><?php esc_html_e( 'Scan accessibility', 'spg-by-ppros' ); ?></button>
				<button type="button" class="button" data-spg-by-ppros-action="refresh-health"><?php esc_html_e( 'Refresh site health', 'spg-by-ppros' ); ?></button>
			</li>
		</ul>
	</article>

	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Works alongside', 'spg-by-ppros' ); ?></h2>
		<p><?php esc_html_e( 'SitePulse Guard does not replace your SEO plugin. It fills the gaps those plugins leave: AI files, accessibility fixes, and plugin bloat.', 'spg-by-ppros' ); ?></p>
		<ul class="spg-by-ppros-badges">
			<li class="<?php echo $companions['yoast'] ? 'is-on' : ''; ?>"><?php echo $companions['yoast'] ? esc_html__( 'Yoast detected', 'spg-by-ppros' ) : esc_html__( 'Yoast not active', 'spg-by-ppros' ); ?></li>
			<li class="<?php echo $companions['rankmath'] ? 'is-on' : ''; ?>"><?php echo $companions['rankmath'] ? esc_html__( 'Rank Math detected', 'spg-by-ppros' ) : esc_html__( 'Rank Math not active', 'spg-by-ppros' ); ?></li>
			<li class="<?php echo $companions['elementor'] ? 'is-on' : ''; ?>"><?php echo $companions['elementor'] ? esc_html__( 'Elementor detected', 'spg-by-ppros' ) : esc_html__( 'Elementor not active', 'spg-by-ppros' ); ?></li>
			<li class="<?php echo $companions['bricks'] ? 'is-on' : ''; ?>"><?php echo $companions['bricks'] ? esc_html__( 'Bricks detected', 'spg-by-ppros' ) : esc_html__( 'Bricks not active', 'spg-by-ppros' ); ?></li>
		</ul>
		<?php if ( empty( $settings['wizard_complete'] ) ) : ?>
			<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=spg-by-ppros-wizard' ) ); ?>"><?php esc_html_e( 'Start setup wizard', 'spg-by-ppros' ); ?></a></p>
		<?php endif; ?>
	</article>
</section>
