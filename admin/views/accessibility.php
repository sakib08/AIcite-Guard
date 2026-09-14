<?php
/**
 * Accessibility screen.
 *
 * @package Aicite_Guard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$scanner     = new Aicite_Guard_Accessibility();
$report      = $scanner->last_report();
$suggestions = $scanner->suggestions();
$statement   = (int) Aicite_Guard_Settings::get_path( 'accessibility.statement_page', 0 );
$remaining   = Aicite_Guard_Ai::remaining();
$has_key     = Aicite_Guard_Settings::has_ai_key();
?>
<section class="acg-grid acg-grid--2">
	<article class="acg-card acg-score-card">
		<div class="acg-score <?php echo esc_attr( 'is-' . ( ( $report['score'] ?? 0 ) >= 85 ? 'good' : ( ( $report['score'] ?? 0 ) >= 60 ? 'ok' : 'bad' ) ) ); ?>">
			<strong><?php echo esc_html( (string) ( $report['score'] ?? 0 ) ); ?></strong>
			<span><?php esc_html_e( '/ 100', 'aicite-guard' ); ?></span>
		</div>
		<div>
			<h2><?php esc_html_e( 'Accessibility Score', 'aicite-guard' ); ?></h2>
			<p class="acg-pill"><?php echo esc_html( $report['label'] ?? __( 'Not scanned yet', 'aicite-guard' ) ); ?></p>
			<p><?php echo esc_html( $report['summary'] ?? __( 'Scan published pages and the media library for issues that actually matter for EAA / WCAG.', 'aicite-guard' ) ); ?></p>
			<button type="button" class="button button-primary" data-acg-action="scan-a11y"><?php esc_html_e( 'Scan site', 'aicite-guard' ); ?></button>
		</div>
	</article>
	<article class="acg-card">
		<h2><?php esc_html_e( 'Statement & visitor widget', 'aicite-guard' ); ?></h2>
		<p><?php esc_html_e( 'The widget helps visitors. It does not replace real content fixes. Approve every alt-text change before it is saved.', 'aicite-guard' ); ?></p>
		<p>
			<button type="button" class="button" data-acg-action="statement"><?php echo $statement ? esc_html__( 'Update statement page', 'aicite-guard' ) : esc_html__( 'Generate statement page', 'aicite-guard' ); ?></button>
			<?php if ( $statement ) : ?>
				<a class="button" href="<?php echo esc_url( get_edit_post_link( $statement, 'raw' ) ); ?>"><?php esc_html_e( 'Edit statement', 'aicite-guard' ); ?></a>
			<?php endif; ?>
		</p>
		<p class="description">
			<?php
			echo $has_key
				? esc_html( sprintf( /* translators: %d: remaining generations */ __( 'AI alt text is on. %d free generations left this month.', 'aicite-guard' ), $remaining ) )
				: esc_html__( 'No AI key yet — suggestions use page titles and filenames. Add a key in Settings for context-aware alt text.', 'aicite-guard' );
			?>
		</p>
	</article>
</section>

<?php if ( ! empty( $report['recommendations'] ) ) : ?>
	<section class="acg-card">
		<h2><?php esc_html_e( 'Plain-English next steps', 'aicite-guard' ); ?></h2>
		<ul class="acg-list">
			<?php foreach ( $report['recommendations'] as $rec ) : ?>
				<li><?php echo esc_html( $rec ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<section class="acg-card">
	<h2><?php esc_html_e( 'Alt text approval queue', 'aicite-guard' ); ?></h2>
	<p><?php esc_html_e( 'Nothing is written to the media library until you click Apply.', 'aicite-guard' ); ?></p>
	<?php if ( ! $suggestions ) : ?>
		<p class="description"><?php esc_html_e( 'No pending suggestions. Run a scan after you upload images.', 'aicite-guard' ); ?></p>
	<?php else : ?>
		<table class="widefat striped acg-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Image', 'aicite-guard' ); ?></th>
					<th><?php esc_html_e( 'Current', 'aicite-guard' ); ?></th>
					<th><?php esc_html_e( 'Suggested', 'aicite-guard' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'aicite-guard' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $suggestions as $item ) : ?>
					<tr data-attachment="<?php echo esc_attr( (string) $item['attachment_id'] ); ?>">
						<td>
							<?php if ( ! empty( $item['thumb'] ) ) : ?>
								<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" width="48" height="48" />
							<?php endif; ?>
							<strong><?php echo esc_html( $item['title'] ); ?></strong>
						</td>
						<td><?php echo $item['current'] ? esc_html( $item['current'] ) : '<em>' . esc_html__( 'Missing', 'aicite-guard' ) . '</em>'; ?></td>
						<td>
							<input type="text" class="widefat acg-alt-field" value="<?php echo esc_attr( $item['suggested'] ); ?>" />
						</td>
						<td>
							<button type="button" class="button" data-acg-action="generate-alt"><?php esc_html_e( 'Improve', 'aicite-guard' ); ?></button>
							<button type="button" class="button button-primary" data-acg-action="apply-alt"><?php esc_html_e( 'Apply', 'aicite-guard' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</section>

<?php if ( ! empty( $report['pages'] ) ) : ?>
	<section class="acg-card">
		<h2><?php esc_html_e( 'Per-page scores', 'aicite-guard' ); ?></h2>
		<table class="widefat striped acg-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'aicite-guard' ); ?></th>
					<th><?php esc_html_e( 'Score', 'aicite-guard' ); ?></th>
					<th><?php esc_html_e( 'Issues', 'aicite-guard' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $report['pages'] as $page ) : ?>
					<tr>
						<td>
							<a href="<?php echo esc_url( $page['edit'] ); ?>"><?php echo esc_html( $page['title'] ); ?></a>
						</td>
						<td><?php echo esc_html( (string) $page['score'] ); ?></td>
						<td>
							<?php
							if ( empty( $page['issues'] ) ) {
								esc_html_e( 'No issues in stored HTML.', 'aicite-guard' );
							} else {
								$messages = wp_list_pluck( $page['issues'], 'message' );
								echo esc_html( implode( ' ', array_slice( $messages, 0, 3 ) ) );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
<?php endif; ?>
