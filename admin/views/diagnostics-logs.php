<?php
/**
 * View do Centro de Diagnóstico, Logs e Explicação de Erros do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$debug_status = \SiteShield\Diagnostics\Debug_Log_Parser::get_debug_status();
$system_status= \SiteShield\Diagnostics\Security_Diagnostics::get_overall_system_status();
$parsed_logs  = \SiteShield\Diagnostics\Debug_Log_Parser::parse( 500 );
$stats        = $parsed_logs['stats'];
$groups       = $parsed_logs['groups'];
$internal_logs= \SiteShield\Diagnostics\Log_Engine::get_logs( 'all', 50 );

$export_csv_url  = wp_nonce_url( admin_url( 'admin.php?siteshield_action=export_diagnostics_csv' ), 'siteshield_export_nonce' );
$export_json_url = wp_nonce_url( admin_url( 'admin.php?siteshield_action=export_diagnostics_json' ), 'siteshield_export_nonce' );
$export_pdf_url  = wp_nonce_url( admin_url( 'admin.php?siteshield_action=print_report' ), 'siteshield_report_nonce' );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Diagnóstico & Logs do Sistema', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Centro integrado de monitoramento, leitura estruturada do debug.log, auditoria interna e explicação de erros.', 'siteshield' ); ?></p>
	</div>
	<div class="content-header-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
		<button type="button" class="ss-btn ss-btn-primary" id="btn-run-full-diagnostic">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
			<span><?php esc_html_e( 'Executar Diagnóstico', 'siteshield' ); ?></span>
		</button>
		<button type="button" class="ss-btn ss-btn-secondary" id="btn-clear-diagnostic-logs">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
			<span><?php esc_html_e( 'Limpar Logs', 'siteshield' ); ?></span>
		</button>
		<div class="ss-dropdown-wrap" style="position: relative; display: inline-block;">
			<button type="button" class="ss-btn ss-btn-secondary" id="btn-toggle-export-menu">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
				<span><?php esc_html_e( 'Exportar', 'siteshield' ); ?> ▾</span>
			</button>
			<div class="ss-dropdown-menu" id="export-dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 4px; background: #FFF; border: 1px solid #E2E8F0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); z-index: 100; min-width: 160px;">
				<a href="<?php echo esc_url( $export_csv_url ); ?>" class="ss-dropdown-item" style="display: block; padding: 8px 14px; color: #0F172A; text-decoration: none; font-size: 13px; border-bottom: 1px solid #F1F5F9;"><?php esc_html_e( 'Exportar CSV', 'siteshield' ); ?></a>
				<a href="<?php echo esc_url( $export_json_url ); ?>" class="ss-dropdown-item" style="display: block; padding: 8px 14px; color: #0F172A; text-decoration: none; font-size: 13px; border-bottom: 1px solid #F1F5F9;"><?php esc_html_e( 'Exportar JSON', 'siteshield' ); ?></a>
				<a href="<?php echo esc_url( $export_pdf_url ); ?>" target="_blank" class="ss-dropdown-item" style="display: block; padding: 8px 14px; color: #0F172A; text-decoration: none; font-size: 13px;"><?php esc_html_e( 'Relatório PDF', 'siteshield' ); ?></a>
			</div>
		</div>
	</div>
</div>

<!-- 1. CARDS DE RESUMO MÉTRICO & ESTADO GERAL -->
<div class="ss-metrics-grid" style="margin-bottom: 25px;">
	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Erros Críticos', 'siteshield' ); ?></span>
		<strong class="m-value" style="color: <?php echo $stats['critical'] > 0 ? '#EF4444' : '#0F172A'; ?>;">
			<?php echo esc_html( $stats['critical'] ); ?>
		</strong>
		<span class="m-sub"><?php esc_html_e( 'Falhas graves e interrupções PHP', 'siteshield' ); ?></span>
	</div>

	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Erros Gerais', 'siteshield' ); ?></span>
		<strong class="m-value"><?php echo esc_html( $stats['errors'] ); ?></strong>
		<span class="m-sub"><?php esc_html_e( 'Exceções e falhas de banco', 'siteshield' ); ?></span>
	</div>

	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Avisos & Alertas', 'siteshield' ); ?></span>
		<strong class="m-value"><?php echo esc_html( $stats['warnings'] ); ?></strong>
		<span class="m-sub"><?php esc_html_e( 'Warnings e depreciações', 'siteshield' ); ?></span>
	</div>

	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Estado Geral do Sistema', 'siteshield' ); ?></span>
		<strong class="m-value" style="font-size: 16px; color: <?php echo esc_attr( $system_status['color'] ); ?>;">
			<?php echo esc_html( $system_status['label'] ); ?>
		</strong>
		<span class="m-sub"><?php echo esc_html( $system_status['summary'] ); ?></span>
	</div>
</div>

<!-- 2. PAINEL DE MONITORAMENTO DAS DIRETIVAS WP_DEBUG -->
<div class="ss-card" style="margin-bottom: 25px;">
	<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
		<h3><?php esc_html_e( 'Monitor de Depuração do WordPress (WP_DEBUG)', 'siteshield' ); ?></h3>
		<span class="ss-badge <?php echo $debug_status['wp_debug_log'] ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>">
			<?php echo $debug_status['wp_debug_log'] ? esc_html__( 'debug.log Ativo', 'siteshield' ) : esc_html__( 'debug.log Inativo', 'siteshield' ); ?>
		</span>
	</div>
	<div class="card-body">
		<p style="font-size: 13px; color: #475569; margin-bottom: 15px;">
			<?php esc_html_e( 'O WordPress utiliza o WP_DEBUG para registrar avisos e falhas em wp-content/debug.log. O SiteShield analisa esse arquivo de forma segura, organizando os dados e oferecendo explicações com ações recomendadas.', 'siteshield' ); ?>
		</p>

		<div class="ss-grid-3" style="margin-bottom: 15px;">
			<div class="diag-stat-card" style="background: #F8FAFC; padding: 12px 16px; border-radius: 8px; border: 1px solid #E2E8F0;">
				<div style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">WP_DEBUG</div>
				<div style="font-size: 14px; font-weight: 700; margin-top: 4px; color: <?php echo $debug_status['wp_debug'] ? '#0066FF' : '#64748B'; ?>;">
					<?php echo $debug_status['wp_debug'] ? esc_html__( 'ATIVADO', 'siteshield' ) : esc_html__( 'DESATIVADO', 'siteshield' ); ?>
				</div>
			</div>

			<div class="diag-stat-card" style="background: #F8FAFC; padding: 12px 16px; border-radius: 8px; border: 1px solid #E2E8F0;">
				<div style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">WP_DEBUG_LOG</div>
				<div style="font-size: 14px; font-weight: 700; margin-top: 4px; color: <?php echo $debug_status['wp_debug_log'] ? '#0066FF' : '#64748B'; ?>;">
					<?php echo $debug_status['wp_debug_log'] ? esc_html__( 'ATIVADO (' . $debug_status['log_size_human'] . ')', 'siteshield' ) : esc_html__( 'DESATIVADO', 'siteshield' ); ?>
				</div>
			</div>

			<div class="diag-stat-card" style="background: #F8FAFC; padding: 12px 16px; border-radius: 8px; border: 1px solid #E2E8F0;">
				<div style="font-size: 11px; font-weight: 600; color: #64748B; text-transform: uppercase;">WP_DEBUG_DISPLAY</div>
				<div style="font-size: 14px; font-weight: 700; margin-top: 4px; color: <?php echo $debug_status['wp_debug_display'] ? '#EF4444' : '#10B981'; ?>;">
					<?php echo $debug_status['wp_debug_display'] ? esc_html__( 'ATIVADO (Risco de Exposição)', 'siteshield' ) : esc_html__( 'DESATIVADO (Recomendado)', 'siteshield' ); ?>
				</div>
			</div>
		</div>

		<?php if ( $debug_status['wp_debug_display'] ) : ?>
			<div class="ss-alert ss-alert-warning" style="margin: 0;">
				<strong><?php esc_html_e( 'Recomendação de Segurança:', 'siteshield' ); ?></strong>
				<?php esc_html_e( 'Em ambiente de produção, mantenha WP_DEBUG_DISPLAY desativado para impedir que visitantes visualizem caminhos internos e códigos do servidor na tela.', 'siteshield' ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<!-- 3. ABAS PRINCIPAIS DE NAVEGAÇÃO ENTRE ORIGENS DE LOG -->
<div class="diag-tabs-nav" style="display: flex; gap: 10px; border-bottom: 2px solid #E2E8F0; margin-bottom: 20px;">
	<button type="button" class="diag-tab-btn active" data-tab="tab-debug-log" style="padding: 10px 18px; font-size: 14px; font-weight: 600; background: none; border: none; border-bottom: 3px solid #0066FF; color: #0066FF; cursor: pointer; margin-bottom: -2px;">
		<?php esc_html_e( 'Erros & Exceções (debug.log)', 'siteshield' ); ?> (<?php echo count( $groups ); ?>)
	</button>
	<button type="button" class="diag-tab-btn" data-tab="tab-siteshield-logs" style="padding: 10px 18px; font-size: 14px; font-weight: 600; background: none; border: none; color: #64748B; cursor: pointer; margin-bottom: -2px;">
		<?php esc_html_e( 'Log Estruturado SiteShield', 'siteshield' ); ?> (<?php echo count( $internal_logs ); ?>)
	</button>
	<button type="button" class="diag-tab-btn" data-tab="tab-health-matrix" style="padding: 10px 18px; font-size: 14px; font-weight: 600; background: none; border: none; color: #64748B; cursor: pointer; margin-bottom: -2px;">
		<?php esc_html_e( 'Matriz de Saúde dos Subsistemas', 'siteshield' ); ?>
	</button>
</div>

<!-- CONTEÚDO DA ABA 1: DEBUG.LOG COM AGRUPAMENTO E EXPLICAÇÕES -->
<div class="diag-tab-pane active" id="pane-tab-debug-log">
	<!-- BARRA DE FILTROS E PESQUISA -->
	<div class="ss-card" style="margin-bottom: 20px; padding: 15px 20px;">
		<div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: space-between; align-items: center;">
			<div style="flex: 1; min-width: 260px;">
				<input type="text" id="input-search-errors" class="ss-input" placeholder="<?php esc_attr_e( 'Pesquisar por mensagem, arquivo, módulo ou tipo de erro...', 'siteshield' ); ?>" style="width: 100%;" />
			</div>
			<div class="period-filter-group" id="filter-severity-group">
				<button type="button" class="filter-btn active" data-filter="ALL"><?php esc_html_e( 'TODOS', 'siteshield' ); ?></button>
				<button type="button" class="filter-btn" data-filter="CRITICAL"><?php esc_html_e( 'CRÍTICOS', 'siteshield' ); ?></button>
				<button type="button" class="filter-btn" data-filter="ERROR"><?php esc_html_e( 'ERROS', 'siteshield' ); ?></button>
				<button type="button" class="filter-btn" data-filter="WARNING"><?php esc_html_e( 'AVISOS', 'siteshield' ); ?></button>
			</div>
		</div>
	</div>

	<!-- LISTA AGRUPADA DE ERROS COM EXPLICAÇÃO E DETALHES TÉCNICOS -->
	<?php if ( empty( $groups ) ) : ?>
		<div class="ss-card" style="text-align: center; padding: 50px 20px;">
			<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2" style="margin: 0 auto 15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
			<h3 style="margin: 0 0 8px; color: #0F172A;"><?php esc_html_e( 'Nenhum Erro ou Falha Registrado no debug.log', 'siteshield' ); ?></h3>
			<p style="color: #64748B; font-size: 13px; max-width: 500px; margin: 0 auto;">
				<?php esc_html_e( 'O ambiente está limpo e sem registros recentes de erro fatal, warnings ou falhas de carregamento.', 'siteshield' ); ?>
			</p>
		</div>
	<?php else : ?>
		<div class="diag-error-groups-list">
			<?php foreach ( $groups as $idx => $grp ) : 
				$badge_color = 'CRITICAL' === $grp['severity'] ? '#EF4444' : ( 'ERROR' === $grp['severity'] ? '#F59E0B' : '#64748B' );
				$border_color = 'CRITICAL' === $grp['severity'] ? '#FEE2E2' : '#E2E8F0';
				$exp = $grp['explanation'];
			?>
				<div class="ss-card error-item-card" data-severity="<?php echo esc_attr( $grp['severity'] ); ?>" data-search="<?php echo esc_attr( strtolower( $grp['message'] . ' ' . $grp['file'] . ' ' . $grp['module'] . ' ' . $grp['type'] ) ); ?>" style="margin-bottom: 16px; border-left: 4px solid <?php echo esc_attr( $badge_color ); ?>;">
					<div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
						<div>
							<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
								<span class="ss-badge" style="background: <?php echo esc_attr( $badge_color ); ?>15; color: <?php echo esc_attr( $badge_color ); ?>; font-weight: 700;">
									<?php echo esc_html( $grp['severity'] ); ?>
								</span>
								<span class="ss-badge ss-badge-neutral"><?php echo esc_html( $grp['type'] ); ?></span>
								<span class="ss-badge ss-badge-neutral"><?php echo esc_html( $grp['origin'] ); ?></span>
								<?php if ( $grp['occurrences'] > 1 ) : ?>
									<span class="ss-badge ss-badge-primary">
										<?php echo esc_html( $grp['occurrences'] ); ?> <?php esc_html_e( 'ocorrências', 'siteshield' ); ?>
									</span>
								<?php endif; ?>
							</div>
							<h3 style="font-size: 15px; margin: 0; color: #0F172A;">
								<?php echo esc_html( $exp['title'] ); ?>
							</h3>
						</div>
						<div style="text-align: right; font-size: 12px; color: #64748B;">
							<div><strong><?php esc_html_e( 'Último registro:', 'siteshield' ); ?></strong> <?php echo esc_html( $grp['last_seen'] ); ?></div>
							<?php if ( $grp['occurrences'] > 1 ) : ?>
								<div><?php esc_html_e( 'Primeiro:', 'siteshield' ); ?> <?php echo esc_html( $grp['first_seen'] ); ?></div>
							<?php endif; ?>
						</div>
					</div>

					<div class="card-body">
						<!-- EXPLICAÇÃO AMIGÁVEL AUTOMÁTICA -->
						<div class="diag-explanation-box" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px 16px; margin-bottom: 14px;">
							<div style="font-size: 13px; color: #1E293B; margin-bottom: 8px;">
								<strong><?php esc_html_e( 'O que aconteceu:', 'siteshield' ); ?></strong> <?php echo esc_html( $exp['explanation'] ); ?>
							</div>
							<?php if ( ! empty( $exp['probable_causes'] ) ) : ?>
								<div style="font-size: 12px; color: #475569; margin-bottom: 8px;">
									<strong><?php esc_html_e( 'Possíveis causas:', 'siteshield' ); ?></strong>
									<ul style="margin: 4px 0 0 18px; padding: 0;">
										<?php foreach ( $exp['probable_causes'] as $cause ) : ?>
											<li><?php echo esc_html( $cause ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							<div style="font-size: 12px; color: #0066FF; font-weight: 600;">
								<strong><?php esc_html_e( 'Ação recomendada:', 'siteshield' ); ?></strong> <?php echo esc_html( $exp['recommendation'] ); ?>
							</div>
						</div>

						<!-- BOTÃO VER DETALHES TÉCNICOS -->
						<div style="display: flex; justify-content: space-between; align-items: center;">
							<div style="font-size: 12px; color: #64748B;">
								<?php if ( ! empty( $grp['file'] ) ) : ?>
									<code><?php echo esc_html( $grp['file'] ); ?><?php echo $grp['line'] ? ':' . esc_html( $grp['line'] ) : ''; ?></code>
								<?php endif; ?>
							</div>
							<button type="button" class="ss-btn ss-btn-sm ss-btn-secondary btn-toggle-tech-details" data-target="tech-details-<?php echo esc_attr( $idx ); ?>">
								<span><?php esc_html_e( 'Ver detalhes técnicos', 'siteshield' ); ?> ▾</span>
							</button>
						</div>

						<!-- GAVETA DE DETALHES TÉCNICOS OCULTA POR PADRÃO -->
						<div class="tech-details-drawer" id="tech-details-<?php echo esc_attr( $idx ); ?>" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #E2E8F0;">
							<div style="font-size: 12px; font-weight: 600; color: #64748B; margin-bottom: 6px;">
								<?php esc_html_e( 'Mensagem Original do PHP:', 'siteshield' ); ?>
							</div>
							<pre style="background: #0F172A; color: #E2E8F0; padding: 12px; border-radius: 6px; font-size: 11px; overflow-x: auto; white-space: pre-wrap; font-family: monospace;"><?php echo esc_html( $grp['message'] ); ?></pre>

							<?php if ( ! empty( $grp['instances'][0]['stack_trace'] ) ) : ?>
								<div style="font-size: 12px; font-weight: 600; color: #64748B; margin: 10px 0 6px;">
									<?php esc_html_e( 'Stack Trace (Pilha de Execução):', 'siteshield' ); ?>
								</div>
								<pre style="background: #0F172A; color: #94A3B8; padding: 12px; border-radius: 6px; font-size: 11px; overflow-x: auto; font-family: monospace;"><?php echo esc_html( implode( "\n", $grp['instances'][0]['stack_trace'] ) ); ?></pre>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

<!-- CONTEÚDO DA ABA 2: LOG ESTRUTURADO DO SITESHIELD -->
<div class="diag-tab-pane" id="pane-tab-siteshield-logs" style="display: none;">
	<div class="ss-card">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3><?php esc_html_e( 'Registros Internos Estruturados do SiteShield', 'siteshield' ); ?></h3>
			<span class="ss-badge ss-badge-primary"><?php esc_html_e( 'Pasta Segura: uploads/siteshield/logs', 'siteshield' ); ?></span>
		</div>
		<div class="card-body">
			<?php if ( empty( $internal_logs ) ) : ?>
				<p class="empty-text"><?php esc_html_e( 'Nenhum registro interno de log encontrado.', 'siteshield' ); ?></p>
			<?php else : ?>
				<div class="table-responsive">
					<table class="ss-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Data/Hora (UTC)', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Request ID', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Categoria', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Gravidade', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Evento', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Mensagem', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'IP', 'siteshield' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $internal_logs as $entry ) : 
								$sev_badge = 'CRITICAL' === $entry['severity'] ? 'ss-badge-danger' : ( 'WARNING' === $entry['severity'] ? 'ss-badge-warning' : 'ss-badge-neutral' );
							?>
								<tr>
									<td><?php echo esc_html( date( 'd/m H:i:s', strtotime( $entry['timestamp'] ) ) ); ?></td>
									<td><code><?php echo esc_html( $entry['request_id'] ); ?></code></td>
									<td><span class="ss-badge ss-badge-neutral"><?php echo esc_html( isset( $entry['category_label'] ) ? $entry['category_label'] : $entry['category'] ); ?></span></td>
									<td><span class="ss-badge <?php echo esc_attr( $sev_badge ); ?>"><?php echo esc_html( $entry['severity'] ); ?></span></td>
									<td><strong><?php echo esc_html( $entry['event'] ); ?></strong></td>
									<td><?php echo esc_html( $entry['message'] ); ?></td>
									<td><code><?php echo esc_html( $entry['ip'] ); ?></code></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- CONTEÚDO DA ABA 3: MATRIZ DE SAÚDE DOS COMPONENTES -->
<div class="diag-tab-pane" id="pane-tab-health-matrix" style="display: none;">
	<div class="ss-grid-2">
		<?php 
		$health_checks = \SiteShield\Diagnostics\Security_Diagnostics::run_full_system_health_check();
		foreach ( $health_checks as $key => $check ) :
			$badge_cls = 'OK' === $check['status'] ? 'ss-badge-primary' : ( 'ATENÇÃO' === $check['status'] ? 'ss-badge-warning' : 'ss-badge-danger' );
		?>
			<div class="ss-card">
				<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
					<h3><?php echo esc_html( $check['title'] ); ?></h3>
					<span class="ss-badge <?php echo esc_attr( $badge_cls ); ?>"><?php echo esc_html( $check['status'] ); ?></span>
				</div>
				<div class="card-body">
					<?php if ( ! empty( $check['version'] ) ) : ?>
						<div style="font-size: 13px; font-weight: 700; color: #0F172A; margin-bottom: 6px;">
							<?php echo esc_html( $check['version'] ); ?>
						</div>
					<?php endif; ?>
					<p style="font-size: 13px; color: #475569; margin: 0;"><?php echo esc_html( $check['description'] ); ?></p>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	// Alternância de Abas Principais
	const tabButtons = document.querySelectorAll('.diag-tab-btn');
	const tabPanes = document.querySelectorAll('.diag-tab-pane');

	tabButtons.forEach(btn => {
		btn.addEventListener('click', function() {
			tabButtons.forEach(b => {
				b.classList.remove('active');
				b.style.color = '#64748B';
				b.style.borderBottom = 'none';
			});
			tabPanes.forEach(p => p.style.display = 'none');

			this.classList.add('active');
			this.style.color = '#0066FF';
			this.style.borderBottom = '3px solid #0066FF';

			const targetPane = document.getElementById('pane-' + this.getAttribute('data-tab'));
			if (targetPane) targetPane.style.display = 'block';
		});
	});

	// Toggle Detalhes Técnicos
	document.querySelectorAll('.btn-toggle-tech-details').forEach(btn => {
		btn.addEventListener('click', function() {
			const targetId = this.getAttribute('data-target');
			const drawer = document.getElementById(targetId);
			if (drawer) {
				if (drawer.style.display === 'none' || drawer.style.display === '') {
					drawer.style.display = 'block';
					this.querySelector('span').innerText = 'Ocultar detalhes técnicos ▴';
				} else {
					drawer.style.display = 'none';
					this.querySelector('span').innerText = 'Ver detalhes técnicos ▾';
				}
			}
		});
	});

	// Filtros por Severidade
	const severityButtons = document.querySelectorAll('#filter-severity-group .filter-btn');
	const errorCards = document.querySelectorAll('.error-item-card');

	severityButtons.forEach(btn => {
		btn.addEventListener('click', function() {
			severityButtons.forEach(b => b.classList.remove('active'));
			this.classList.add('active');

			const filter = this.getAttribute('data-filter');
			errorCards.forEach(card => {
				if (filter === 'ALL' || card.getAttribute('data-severity') === filter) {
					card.style.display = 'block';
				} else {
					card.style.display = 'none';
				}
			});
		});
	});

	// Pesquisa em Tempo Real
	const searchInput = document.getElementById('input-search-errors');
	if (searchInput) {
		searchInput.addEventListener('input', function() {
			const query = this.value.toLowerCase().trim();
			errorCards.forEach(card => {
				const searchable = card.getAttribute('data-search') || '';
				if (query === '' || searchable.includes(query)) {
					card.style.display = 'block';
				} else {
					card.style.display = 'none';
				}
			});
		});
	}

	// Toggle Menu de Exportação
	const btnExport = document.getElementById('btn-toggle-export-menu');
	const menuExport = document.getElementById('export-dropdown-menu');
	if (btnExport && menuExport) {
		btnExport.addEventListener('click', function(e) {
			e.stopPropagation();
			menuExport.style.display = menuExport.style.display === 'none' ? 'block' : 'none';
		});
		document.addEventListener('click', function() {
			menuExport.style.display = 'none';
		});
	}

	// Executar Diagnóstico AJAX
	const btnDiag = document.getElementById('btn-run-full-diagnostic');
	if (btnDiag) {
		btnDiag.addEventListener('click', function() {
			btnDiag.disabled = true;
			btnDiag.innerHTML = '<span>Executando Diagnóstico...</span>';
			
			jQuery.post(ajaxurl, {
				action: 'siteshield_run_health_diagnostic',
				nonce: '<?php echo esc_js( wp_create_nonce( 'siteshield_admin_nonce' ) ); ?>'
			}, function(response) {
				btnDiag.disabled = false;
				btnDiag.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg> <span>Executar Diagnóstico</span>';
				alert('Diagnóstico concluído com sucesso! Atualizando painel...');
				window.location.reload();
			});
		});
	}

	// Limpar Logs AJAX
	const btnClear = document.getElementById('btn-clear-diagnostic-logs');
	if (btnClear) {
		btnClear.addEventListener('click', function() {
			if (!confirm('Deseja realmente limpar todos os registros de logs? Esta ação não pode ser desfeita.')) {
				return;
			}
			btnClear.disabled = true;
			jQuery.post(ajaxurl, {
				action: 'siteshield_clear_logs',
				nonce: '<?php echo esc_js( wp_create_nonce( 'siteshield_admin_nonce' ) ); ?>'
			}, function(response) {
				alert('Logs limpos com sucesso!');
				window.location.reload();
			});
		});
	}
});
</script>
