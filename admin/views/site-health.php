<?php
/**
 * Site Health Guardian.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$report = ( new Sitepulse_Guard_By_Plugin_Pros_Health() )->report();
?>
<section class="spg-by-ppros-grid spg-by-ppros-grid--2">
	<article class="spg-by-ppros-card spg-by-ppros-score-card">
		<div class="spg-by-ppros-score <?php echo esc_attr( 'is-' . ( $report['score'] >= 80 ? 'good' : ( $report['score'] >= 55 ? 'ok' : 'bad' ) ) ); ?>">
			<strong><?php echo esc_html( (string) $report['score'] ); ?></strong>
			<span><?php esc_html_e( '/ 100', 'spg-by-ppros' ); ?></span>
		</div>
		<div>
			<h2><?php esc_html_e( 'Health Score', 'spg-by-ppros' ); ?></h2>
			<p class="spg-by-ppros-pill"><?php echo esc_html( $report['label'] ); ?></p>
			<p><?php echo esc_html( $report['summary'] ); ?></p>
			<p class="description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: active plugins, 2: installed plugins */
						__( '%1$d active of %2$d installed plugins.', 'spg-by-ppros' ),
						$report['active'],
						$report['installed']
					)
				);
				?>
			</p>
			<button type="button" class="button" data-spg-by-ppros-action="refresh-health"><?php esc_html_e( 'Refresh', 'spg-by-ppros' ); ?></button>
		</div>
	</article>
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Core Web Vitals impact', 'spg-by-ppros' ); ?></h2>
		<p><?php esc_html_e( 'This is a local overview — not a lab Lighthouse run. Use it to spot plugin weight before you open PageSpeed Insights.', 'spg-by-ppros' ); ?></p>
		<ul class="spg-by-ppros-checks">
			<?php foreach ( $report['cwv']['hints'] as $hint ) : ?>
				<li class="is-<?php echo esc_attr( $hint['status'] ); ?>"><?php echo esc_html( $hint['text'] ); ?></li>
			<?php endforeach; ?>
		</ul>
		<p>
			<span class="description"><?php echo esc_html( sprintf( /* translators: %s: size */ __( 'Autoloaded options: %s', 'spg-by-ppros' ), $report['cwv']['autoload_label'] ) ); ?></span>
			<a class="button" href="<?php echo esc_url( $report['cwv']['insights_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open PageSpeed Insights', 'spg-by-ppros' ); ?></a>
		</p>
	</article>
</section>

<section class="spg-by-ppros-grid spg-by-ppros-grid--2">
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Unused or inactive plugins', 'spg-by-ppros' ); ?></h2>
		<?php if ( ! $report['unused'] ) : ?>
			<p class="description"><?php esc_html_e( 'Every installed plugin is active. That is tidy — just make sure you still need them all.', 'spg-by-ppros' ); ?></p>
		<?php else : ?>
			<ul class="spg-by-ppros-list">
				<?php foreach ( $report['unused'] as $plugin ) : ?>
					<li>
						<strong><?php echo esc_html( $plugin['name'] ); ?></strong>
						<span class="description"><?php echo esc_html( $plugin['size_label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
			<p class="description"><?php esc_html_e( 'Inactive plugins still add update and security surface. Delete what you do not plan to use.', 'spg-by-ppros' ); ?></p>
		<?php endif; ?>
	</article>
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Conflict detection', 'spg-by-ppros' ); ?></h2>
		<?php if ( ! $report['conflicts'] ) : ?>
			<p class="description"><?php esc_html_e( 'No common overlap detected (SEO, cache, security, or asset optimizers).', 'spg-by-ppros' ); ?></p>
		<?php else : ?>
			<?php foreach ( $report['conflicts'] as $conflict ) : ?>
				<div class="spg-by-ppros-callout">
					<strong><?php echo esc_html( $conflict['label'] ); ?></strong>
					<p><?php echo esc_html( $conflict['detail'] ); ?></p>
					<p class="description"><?php echo esc_html( implode( ', ', $conflict['plugins'] ) ); ?></p>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</article>
</section>

<section class="spg-by-ppros-card">
	<h2><?php esc_html_e( 'Security surface', 'spg-by-ppros' ); ?></h2>
	<ul class="spg-by-ppros-checks">
		<?php foreach ( $report['security'] as $item ) : ?>
			<li class="is-<?php echo esc_attr( $item['status'] ); ?>">
				<strong><?php echo esc_html( $item['title'] ); ?></strong>
				<span><?php echo esc_html( $item['detail'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</section>

<section class="spg-by-ppros-card">
	<h2><?php esc_html_e( 'Heaviest installed plugins', 'spg-by-ppros' ); ?></h2>
	<table class="widefat striped spg-by-ppros-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin', 'spg-by-ppros' ); ?></th>
				<th><?php esc_html_e( 'Size', 'spg-by-ppros' ); ?></th>
				<th><?php esc_html_e( 'Status', 'spg-by-ppros' ); ?></th>
				<th><?php esc_html_e( 'Notes', 'spg-by-ppros' ); ?></th>
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
					<td><?php echo $plugin['active'] ? esc_html__( 'Active', 'spg-by-ppros' ) : esc_html__( 'Inactive', 'spg-by-ppros' ); ?></td>
					<td>
						<?php
						$notes = array();
						if ( $plugin['update'] ) {
							$notes[] = sprintf( /* translators: %s: version */ __( 'Update to %s', 'spg-by-ppros' ), $plugin['new_version'] );
						}
						if ( $plugin['age_days'] > 730 && $plugin['active'] ) {
							$notes[] = __( 'No file changes in ~2 years', 'spg-by-ppros' );
						}
						echo $notes ? esc_html( implode( ' · ', $notes ) ) : '—';
						?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</section>
