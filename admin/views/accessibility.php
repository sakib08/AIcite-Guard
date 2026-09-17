<?php
/**
 * Accessibility screen.
 *
 * @package Sitepulse_Guard_By_Plugin_Pros
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template variables are local via admin include.

$scanner     = new Sitepulse_Guard_By_Plugin_Pros_Accessibility();
$report      = $scanner->last_report();
$suggestions = $scanner->suggestions();
$statement   = (int) Sitepulse_Guard_By_Plugin_Pros_Settings::get_path( 'accessibility.statement_page', 0 );
$remaining   = Sitepulse_Guard_By_Plugin_Pros_Ai::remaining();
$ai_on       = Sitepulse_Guard_By_Plugin_Pros_Ai::is_available();
?>
<section class="spg-by-ppros-grid spg-by-ppros-grid--2">
	<article class="spg-by-ppros-card spg-by-ppros-score-card">
		<div class="spg-by-ppros-score <?php echo esc_attr( 'is-' . ( ( $report['score'] ?? 0 ) >= 85 ? 'good' : ( ( $report['score'] ?? 0 ) >= 60 ? 'ok' : 'bad' ) ) ); ?>">
			<strong><?php echo esc_html( (string) ( $report['score'] ?? 0 ) ); ?></strong>
			<span><?php esc_html_e( '/ 100', 'spg-by-ppros' ); ?></span>
		</div>
		<div>
			<h2><?php esc_html_e( 'Accessibility Score', 'spg-by-ppros' ); ?></h2>
			<p class="spg-by-ppros-pill"><?php echo esc_html( $report['label'] ?? __( 'Not scanned yet', 'spg-by-ppros' ) ); ?></p>
			<p><?php echo esc_html( $report['summary'] ?? __( 'Scan published pages and the media library for issues that actually matter for EAA / WCAG.', 'spg-by-ppros' ) ); ?></p>
			<button type="button" class="button button-primary" data-spg-by-ppros-action="scan-a11y"><?php esc_html_e( 'Scan site', 'spg-by-ppros' ); ?></button>
		</div>
	</article>
	<article class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Statement & visitor widget', 'spg-by-ppros' ); ?></h2>
		<p><?php esc_html_e( 'The widget helps visitors. It does not replace real content fixes. Approve every alt-text change before it is saved.', 'spg-by-ppros' ); ?></p>
		<p>
			<button type="button" class="button" data-spg-by-ppros-action="statement"><?php echo $statement ? esc_html__( 'Update statement page', 'spg-by-ppros' ) : esc_html__( 'Generate statement page', 'spg-by-ppros' ); ?></button>
			<?php if ( $statement ) : ?>
				<a class="button" href="<?php echo esc_url( get_edit_post_link( $statement, 'raw' ) ); ?>"><?php esc_html_e( 'Edit statement', 'spg-by-ppros' ); ?></a>
			<?php endif; ?>
		</p>
		<p class="description">
			<?php
			echo $ai_on
				? esc_html( sprintf( /* translators: %d: remaining generations */ __( 'AI alt text uses the WordPress AI Client. %d free generations left this month.', 'spg-by-ppros' ), $remaining ) )
				: esc_html__( 'AI alt text is off or no site-wide AI provider is configured. Suggestions use page titles and filenames.', 'spg-by-ppros' );
			?>
		</p>
	</article>
</section>

<?php if ( ! empty( $report['recommendations'] ) ) : ?>
	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Plain-English next steps', 'spg-by-ppros' ); ?></h2>
		<ul class="spg-by-ppros-list">
			<?php foreach ( $report['recommendations'] as $rec ) : ?>
				<li><?php echo esc_html( $rec ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<section class="spg-by-ppros-card">
	<h2><?php esc_html_e( 'Alt text approval queue', 'spg-by-ppros' ); ?></h2>
	<p><?php esc_html_e( 'Nothing is written to the media library until you click Apply.', 'spg-by-ppros' ); ?></p>
	<?php if ( ! $suggestions ) : ?>
		<p class="description"><?php esc_html_e( 'No pending suggestions. Run a scan after you upload images.', 'spg-by-ppros' ); ?></p>
	<?php else : ?>
		<table class="widefat striped spg-by-ppros-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Image', 'spg-by-ppros' ); ?></th>
					<th><?php esc_html_e( 'Current', 'spg-by-ppros' ); ?></th>
					<th><?php esc_html_e( 'Suggested', 'spg-by-ppros' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'spg-by-ppros' ); ?></th>
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
						<td><?php echo $item['current'] ? esc_html( $item['current'] ) : '<em>' . esc_html__( 'Missing', 'spg-by-ppros' ) . '</em>'; ?></td>
						<td>
							<input type="text" class="widefat spg-by-ppros-alt-field" value="<?php echo esc_attr( $item['suggested'] ); ?>" />
						</td>
						<td>
							<button type="button" class="button" data-spg-by-ppros-action="generate-alt"><?php esc_html_e( 'Improve', 'spg-by-ppros' ); ?></button>
							<button type="button" class="button button-primary" data-spg-by-ppros-action="apply-alt"><?php esc_html_e( 'Apply', 'spg-by-ppros' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</section>

<?php if ( ! empty( $report['pages'] ) ) : ?>
	<section class="spg-by-ppros-card">
		<h2><?php esc_html_e( 'Per-page scores', 'spg-by-ppros' ); ?></h2>
		<table class="widefat striped spg-by-ppros-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'spg-by-ppros' ); ?></th>
					<th><?php esc_html_e( 'Score', 'spg-by-ppros' ); ?></th>
					<th><?php esc_html_e( 'Issues', 'spg-by-ppros' ); ?></th>
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
								esc_html_e( 'No issues in stored HTML.', 'spg-by-ppros' );
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
