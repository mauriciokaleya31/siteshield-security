<?php
/**
 * View de Relatórios Executivos em PDF do SiteShield
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

use SiteShield\Core\Feature_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$can_use_reports = Feature_Manager::can_use( 'pdf_reports' );
$print_url       = wp_nonce_url( admin_url( 'admin.php?siteshield_action=print_report' ), 'siteshield_report_nonce' );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Relatórios Executivos de Cibersegurança', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Gere documentos formais e consolidados da postura de segurança para clientes, auditorias e conformidade.', 'siteshield' ); ?></p>
	</div>
	<?php if ( $can_use_reports ) : ?>
		<div class="content-header-actions">
			<a href="<?php echo esc_url( $print_url ); ?>" target="_blank" class="ss-btn ss-btn-primary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
					<polyline points="7 10 12 15 17 10"></polyline>
					<line x1="12" y1="15" x2="12" y2="3"></line>
				</svg>
				<span><?php esc_html_e( 'Baixar Relatório em PDF', 'siteshield' ); ?></span>
			</a>
		</div>
	<?php endif; ?>
</div>

<?php if ( ! $can_use_reports ) : ?>
	<?php
	Feature_Manager::render_premium_lock_box(
		'pdf_reports',
		__( 'Relatórios Executivos em PDF', 'siteshield' ),
		__( 'A geração de relatórios consolidados em formato PDF com dados de auditoria, eventos WAF e conformidade de integridade é um recurso exclusivo dos planos PRO, Business e Agency.', 'siteshield' )
	);
	?>
<?php else : ?>
	<div class="ss-card">
		<div class="card-header">
			<h3><?php esc_html_e( 'Sumário Executivo do Relatório', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="report-overview-box">
				<div class="report-brand-row">
					<div>
						<h4>SiteShield Security Report</h4>
						<p><?php echo esc_html( get_site_url() ); ?> — <?php echo esc_html( date( 'd/m/Y H:i' ) ); ?></p>
					</div>
					<div class="report-score-pill">
						<span>Security Score:</span>
						<strong><?php echo esc_html( $score_data['score'] ); ?>/100</strong>
					</div>
				</div>

				<div class="report-sections-list" style="margin-top: 20px;">
					<div class="report-section-row">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
						<span><strong>Web Application Firewall (WAF):</strong> Modos ativos e registro de ameaças bloqueadas.</span>
					</div>
					<div class="report-section-row">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
						<span><strong>Heuristic Malware Scanner:</strong> Detecção estática em plugins, temas e core.</span>
					</div>
					<div class="report-section-row">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
						<span><strong>File Integrity Baselining:</strong> Monitoramento de hashes criptográficos SHA-256.</span>
					</div>
					<div class="report-section-row">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
						<span><strong>Traffic & Visitor Overview:</strong> Resumo de visitantes únicos e requisições suspeitas.</span>
					</div>
				</div>

				<div style="margin-top: 30px; border-top: 1px solid #E2E8F0; padding-top: 20px;">
					<a href="<?php echo esc_url( $print_url ); ?>" target="_blank" class="ss-btn ss-btn-primary ss-btn-lg">
						📥 <?php esc_html_e( 'Gerar e Visualizar Relatório Completo (PDF)', 'siteshield' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>
