<?php
/**
 * View do Scanner Heurístico de Malware e Quarentena
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$tables   = \SiteShield\Core\Database::get_table_names();
$findings = array();
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['scan_findings']}'" ) === $tables['scan_findings'] ) {
	$findings = $wpdb->get_results( "SELECT * FROM {$tables['scan_findings']} WHERE status = 'active' ORDER BY id DESC LIMIT 50" );
}
$quarantined_files = \SiteShield\Scanner\Quarantine::get_all();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Malware Scanner & Quarantine', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Varredura heurística estática em plugins, temas, uploads e core em busca de Web Shells e backdoors.', 'siteshield' ); ?></p>
	</div>
</div>

<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Scan Operations', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<div class="scanner-actions-row">
			<button type="button" class="ss-btn ss-btn-primary ss-btn-lg" id="btn-run-quick-scan">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				<span><?php esc_html_e( 'Quick Scan (Plugins & Themes)', 'siteshield' ); ?></span>
			</button>
			<button type="button" class="ss-btn ss-btn-secondary ss-btn-lg" id="btn-run-full-scan">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
				<span><?php esc_html_e( 'Full Scan (Entire WordPress)', 'siteshield' ); ?></span>
			</button>
		</div>

		<div class="scan-progress-wrapper" id="scanner-progress-box" style="display:none; margin-top: 20px;">
			<div class="ss-progress-bar">
				<div class="ss-progress-fill" id="scanner-progress-fill" style="width: 100%;"></div>
			</div>
			<p id="scanner-status-text" style="margin-top: 10px; font-weight: 600; color: #0066FF;">
				<?php esc_html_e( 'Analisando arquivos...', 'siteshield' ); ?>
			</p>
		</div>
	</div>
</div>

<!-- TABELA DE AMEAÇAS ENCONTRADAS -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Detected Suspicious Files', 'siteshield' ); ?> (<?php echo count( $findings ); ?>)</h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $findings ) ) : ?>
			<div class="empty-statee-card text-center">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" style="margin: 0 auto 10px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
				<h4><?php esc_html_e( 'No Threats Detected', 'siteshield' ); ?></h4>
				<p><?php esc_html_e( 'Seu sistema está limpo e sem padrões de malware conhecidos.', 'siteshield' ); ?></p>
			</div>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Severidade', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Arquivo', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Linha', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Assinatura', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Trecho Suspeito', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Ações', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $findings as $item ) : ?>
							<tr>
								<td><span class="ss-badge ss-badge-primary"><?php echo esc_html( strtoupper( $item->severity ) ); ?></span></td>
								<td><code><?php echo esc_html( $item->file_path ); ?></code></td>
								<td><?php echo esc_html( $item->line_number ); ?></td>
								<td><?php echo esc_html( $item->signature_name ); ?></td>
								<td><code class="code-snippet"><?php echo esc_html( $item->code_snippet ); ?></code></td>
								<td>
									<button type="button" class="ss-btn ss-btn-sm ss-btn-danger btn-quarantine-file" data-file="<?php echo esc_attr( $item->file_path ); ?>">
										<?php esc_html_e( 'Quarantine', 'siteshield' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- TABELA DE ARQUIVOS EM QUARENTENA -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Quarantine Vault', 'siteshield' ); ?> (<?php echo count( $quarantined_files ); ?>)</h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $quarantined_files ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhum arquivo isolado na quarentena.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Data', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Caminho Original', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Motivo', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Status', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Ações', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $quarantined_files as $q ) : ?>
							<tr>
								<td><?php echo esc_html( date( 'd/m H:i', strtotime( $q->created_at ) ) ); ?></td>
								<td><code><?php echo esc_html( $q->original_path ); ?></code></td>
								<td><?php echo esc_html( $q->reason ); ?></td>
								<td>
									<span class="ss-badge ss-badge-neutral"><?php echo $q->restored_at ? esc_html__( 'Restaurado', 'siteshield' ) : esc_html__( 'Isolado', 'siteshield' ); ?></span>
								</td>
								<td>
									<?php if ( ! $q->restored_at ) : ?>
										<button type="button" class="ss-btn ss-btn-sm ss-btn-secondary btn-restore-file" data-id="<?php echo esc_attr( $q->id ); ?>">
											<?php esc_html_e( 'Restore', 'siteshield' ); ?>
										</button>
									<?php endif; ?>
									<button type="button" class="ss-btn ss-btn-sm ss-btn-outline-danger btn-delete-quarantine" data-id="<?php echo esc_attr( $q->id ); ?>">
										<?php esc_html_e( 'Delete', 'siteshield' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
