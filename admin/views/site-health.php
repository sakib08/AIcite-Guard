<?php
/**
 * Site Health Guardian.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$report = ( new Aicite_Guard_Health() )->report();
?>
<section class="acg-grid acg-grid--2">
	<article class="acg-card acg-score-card">
		<div class="acg-score <?php echo esc_attr( 'is-' . ( $report['score'] >= 80 ? 'good' : ( $report['score'] >= 55 ? 'ok' : 'bad' ) ) ); ?>">
			<strong><?php echo esc_html( (string) $report['score'] ); ?></strong>
			<span><?php esc_html_e( '/ 100', 'aicite-guard' ); ?></span>
		</div>
		<div>
			<h2><?php esc_html_e( 'Health Score', 'aicite-guard' ); ?></h2>
			<p class="acg-pill"><?php echo esc_html( $report['label'] ); ?></p>
			<p><?php echo esc_html( $report['summary'] ); ?></p>
			<p class="description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: active plugins, 2: installed plugins */
						__( '%1$d active of %2$d installed plugins.', 'aicite-guard' ),
						$report['active'],
						$report['installed']
					)
				);
				?>
			</p>
			<button type="button" class="button" data-acg-action="refresh-health"><?php esc_html_e( 'Refresh', 'aicite-guard' ); ?></button>
		</div>
	</article>
	<article class="acg-card">
		<h2><?php esc_html_e( 'Core Web Vitals impact', 'aicite-guard' ); ?></h2>
		<p><?php esc_html_e( 'This is a local overview — not a lab Lighthouse run. Use it to spot plugin weight before you open PageSpeed Insights.', 'aicite-guard' ); ?></p>
		<ul class="acg-checks">
			<?php foreach ( $report['cwv']['hints'] as $hint ) : ?>
				<li class="is-<?php echo esc_attr( $hint['status'] ); ?>"><?php echo esc_html( $hint['text'] ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p>
			<span class="description"><?php echo esc_html( sprintf( /* translators: %s: size */ __( 'Autoloaded options: %s', 'aicite-guard' ), $report['cwv']['autoload_label'] ) ); ?></span>
			<a class="button" href="<?php echo esc_url( $report['cwv']['insights_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open PageSpeed Insights', 'aicite-guard' ); ?></a>
		</p>
	</article>
</section>

<section class="acg-grid acg-grid--2">
	<article class="acg-card">
		<h2><?php esc_html_e( 'Unused or inactive plugins', 'aicite-guard' ); ?></h2>
		<?php if ( ! $report['unused'] ) : ?>
			<p class="description"><?php esc_html_e( 'Every installed plugin is active. That is tidy — just make sure you still need them all.', 'aicite-guard' ); ?></p>
		<?php else : ?>
			<ul class="acg-list">
				<?php foreach ( $report['unused'] as $plugin ) : ?>
					<li>
						<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
						<span class="description"><?php echo esc_html( $plugin['size_label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="description"><?php esc_html_e( 'Inactive plugins still add update and security surface. Delete what you do not plan to use.', 'aicite-guard' ); ?></p>
		<?php endif; ?>
	</article>
	<article class="acg-card">
		<h2><?php esc_html_e( 'Conflict detection', 'aicite-guard' ); ?></h2>
		<?php if ( ! $report['conflicts'] ) : ?>
			<p class="description"><?php esc_html_e( 'No common overlap detected (SEO, cache, security, or asset optimizers).', 'aicite-guard' ); ?></p>
		<?php else : ?>
			<?php foreach ( $report['conflicts'] as $conflict ) : ?>
				<div class="acg-callout">
					<strong><?php echo esc_html( $conflict['label'] ); ?></strong>
					<p><?php echo esc_html( $conflict['detail'] ); ?></p>
					<p class="description"><?php echo esc_html( implode( ', ', $conflict['plugins'] ) ); ?></p>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</article>
</section>

<section class="acg-card">
	<h2><?php esc_html_e( 'Security surface', 'aicite-guard' ); ?></h2>
	<ul class="acg-checks">
		<?php foreach ( $report['security'] as $item ) : ?>
			<li class="is-<?php echo esc_attr( $item['status'] ); ?>">
				<strong><?php echo esc_html( $item['title'] ); ?></strong>
				<span><?php echo esc_html( $item['detail'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</section>

<section class="acg-card">
	<h2><?php esc_html_e( 'Heaviest installed plugins', 'aicite-guard' ); ?></h2>
	<table class="widefat striped acg-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin', 'aicite-guard' ); ?></th>
				<th><?php esc_html_e( 'Size', 'aicite-guard' ); ?></th>
				<th><?php esc_html_e( 'Status', 'aicite-guard' ); ?></th>
				<th><?php esc_html_e( 'Notes', 'aicite-guard' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( array_slice( $report['plugins'], 0, 15 ) as $plugin ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
						<div class="description"><?php echo esc_html( $plugin['version'] ); ?></div>
					</td>
					<td><?php echo esc_html( $plugin['size_label'] ); ?></td>
					<td><?php echo $plugin['active'] ? esc_html__( 'Active', 'aicite-guard' ) : esc_html__( 'Inactive', 'aicite-guard' ); ?></td>
					<td>
						<?php
						$notes = array();
						if ( $plugin['update'] ) {
							$notes[] = sprintf( /* translators: %s: version */ __( 'Update to %s', 'aicite-guard' ), $plugin['new_version'] );
						}
						if ( $plugin['age_days'] > 730 && $plugin['active'] ) {
							$notes[] = __( 'No file changes in ~2 years', 'aicite-guard' );
						}
						echo $notes ? esc_html( implode( ' · ', $notes ) ) : '—';
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
