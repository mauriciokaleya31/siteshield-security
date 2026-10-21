<?php
/**
 * View de Ferramentas, Diagnóstico e Backup de Configurações
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$recovery_token = \SiteShield\Core\Emergency_Mode::get_or_create_recovery_token();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Ferramentas, Diagnóstico & Backup', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Exportação/importação de configurações em JSON e mecanismos de recuperação de emergência.', 'siteshield' ); ?></p>
	</div>
</div>

<!-- BACKUP E RESTAURAÇÃO JSON -->
<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Backup & Restauração de Configurações (JSON)', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<p><?php esc_html_e( 'Exporte um snapshot de todas as suas configurações do SiteShield para migrar entre sites ou guardar um backup seguro.', 'siteshield' ); ?></p>
		
		<div class="ss-grid-2" style="margin-top: 20px;">
			<div class="backup-col">
				<h4><?php esc_html_e( 'Exportar Configurações (JSON):', 'siteshield' ); ?></h4>
				<button type="button" class="ss-btn ss-btn-secondary" id="btn-export-settings-json">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
					<span><?php esc_html_e( 'Gerar Backup JSON', 'siteshield' ); ?></span>
				</button>
				<textarea id="export-json-output" rows="6" style="margin-top: 10px; width:100%; display:none;" readonly></textarea>
			</div>

			<div class="backup-col">
				<h4><?php esc_html_e( 'Importar Configurações (JSON):', 'siteshield' ); ?></h4>
				<textarea id="import-json-input" rows="4" style="width:100%;" placeholder="<?php esc_attr_e( 'Cole o conteúdo JSON do backup aqui...', 'siteshield' ); ?>"></textarea>
				<button type="button" class="ss-btn ss-btn-primary" id="btn-import-settings-json" style="margin-top: 10px;">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
					<span><?php esc_html_e( 'Restaurar Configurações', 'siteshield' ); ?></span>
				</button>
			</div>
		</div>
	</div>
</div>

<!-- CHAVE DE DESBLOQUEIO DE EMERGÊNCIA (ANTI-LOCKOUT) -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Chave de Desbloqueio e Recuperação de Emergência', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<p><?php esc_html_e( 'Guarde este token de recuperação em local seguro. Caso você perca o acesso ou seja bloqueado acidentalmente, utilize a constante no seu wp-config.php:', 'siteshield' ); ?></p>
		
		<div class="recovery-token-box" style="background: #F8FAFC; padding: 15px; border-radius: 8px; border: 1px dashed #CBD5E1;">
			<p><strong><?php esc_html_e( 'Seu Token de Emergência:', 'siteshield' ); ?></strong> <code><?php echo esc_html( $recovery_token ); ?></code></p>
			<p><small><?php esc_html_e( 'Para desativar todas as proteções via wp-config.php adicione:', 'siteshield' ); ?> <code>define( 'SITESHIELD_DISABLE_ALL', true );</code></small></p>
		</div>
	</div>
</div>
