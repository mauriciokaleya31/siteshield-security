<?php
/**
 * View do Firewall de Aplicação (WAF) e Rate Limiting
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$tables = \SiteShield\Core\Database::get_table_names();
$events = array();
if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['events']}'" ) === $tables['events'] ) {
	$events = $wpdb->get_results( "SELECT * FROM {$tables['events']} ORDER BY id DESC LIMIT 20" );
}
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Web Application Firewall (WAF)', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Inspeção e mitigação em tempo real de ataques SQLi, XSS, Path Traversal e exploits.', 'siteshield' ); ?></p>
	</div>
</div>

<form id="form-firewall-settings" class="ss-settings-form">
	<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

	<!-- WAF MODE SELECTION -->
	<div class="ss-card">
		<div class="card-header">
			<h3><?php esc_html_e( 'Firewall Operating Mode', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="waf-mode-selector">
				<label class="waf-radio-card <?php echo 'OFF' === $settings['waf_mode'] ? 'active' : ''; ?>">
					<input type="radio" name="waf_mode" value="OFF" <?php checked( $settings['waf_mode'], 'OFF' ); ?> />
					<div class="radio-content">
						<strong><?php esc_html_e( 'Disabled (OFF)', 'siteshield' ); ?></strong>
						<p><?php esc_html_e( 'Nenhuma regra de firewall é aplicada.', 'siteshield' ); ?></p>
					</div>
				</label>
				<label class="waf-radio-card <?php echo 'MONITOR' === $settings['waf_mode'] ? 'active' : ''; ?>">
					<input type="radio" name="waf_mode" value="MONITOR" <?php checked( $settings['waf_mode'], 'MONITOR' ); ?> />
					<div class="radio-content">
						<strong><?php esc_html_e( 'Monitor Mode (Learning)', 'siteshield' ); ?></strong>
						<p><?php esc_html_e( 'Inspeciona e registra tentativas suspeitas sem bloquear os visitantes.', 'siteshield' ); ?></p>
					</div>
				</label>
				<label class="waf-radio-card <?php echo 'BLOCK' === $settings['waf_mode'] ? 'active' : ''; ?>">
					<input type="radio" name="waf_mode" value="BLOCK" <?php checked( $settings['waf_mode'], 'BLOCK' ); ?> />
					<div class="radio-content">
						<strong><?php esc_html_e( 'Block Mode (Protection Active)', 'siteshield' ); ?></strong>
						<p><?php esc_html_e( 'Bloqueia imediatamente ameaças conhecidas e ataques maliciosos.', 'siteshield' ); ?></p>
					</div>
				</label>
			</div>
		</div>
	</div>

	<!-- WAF LEVEL & LEARNING MODE -->
	<div class="ss-card" style="margin-top: 20px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Firewall Sensitivity Level & Learning Mode', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-grid-2">
				<div class="ss-input-group">
					<label><?php esc_html_e( 'Nível de Sensibilidade do Firewall:', 'siteshield' ); ?></label>
					<select name="waf_level" class="ss-select">
						<option value="low" <?php selected( isset( $settings['waf_level'] ) ? $settings['waf_level'] : 'medium', 'low' ); ?>>Low (Apenas assinaturas críticas conhecidas)</option>
						<option value="medium" <?php selected( isset( $settings['waf_level'] ) ? $settings['waf_level'] : 'medium', 'medium' ); ?>>Medium (Recomendado - Equilíbrio ideal)</option>
						<option value="high" <?php selected( isset( $settings['waf_level'] ) ? $settings['waf_level'] : 'medium', 'high' ); ?>>High (Inspeção profunda de cabeçalhos e payloads)</option>
						<option value="aggressive" <?php selected( isset( $settings['waf_level'] ) ? $settings['waf_level'] : 'medium', 'aggressive' ); ?>>Aggressive (Bloqueio estrito sob ataque ativo)</option>
					</select>
				</div>

				<div class="ss-input-group">
					<label><?php esc_html_e( 'Período do Modo de Aprendizado (Learning):', 'siteshield' ); ?></label>
					<select name="waf_learning_days" class="ss-select">
						<option value="7" <?php selected( isset( $settings['waf_learning_days'] ) ? $settings['waf_learning_days'] : 14, 7 ); ?>>7 Dias</option>
						<option value="14" <?php selected( isset( $settings['waf_learning_days'] ) ? $settings['waf_learning_days'] : 14, 14 ); ?>>14 Dias (Recomendado)</option>
						<option value="30" <?php selected( isset( $settings['waf_learning_days'] ) ? $settings['waf_learning_days'] : 14, 30 ); ?>>30 Dias</option>
					</select>
				</div>
			</div>
		</div>
	</div>

	<!-- RULES CHECKLIST -->
	<div class="ss-card" style="margin-top: 20px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Active Inspection Rules', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-grid-2">
				<label class="ss-toggle-card">
					<input type="checkbox" name="waf_block_sqli" value="1" <?php checked( $settings['waf_block_sqli'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'SQL Injection Protection (SQLi)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Bloqueia UNION SELECT, sleeps, concatenações e manipulação de query.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="waf_block_xss" value="1" <?php checked( $settings['waf_block_xss'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Cross-Site Scripting Protection (XSS)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Bloqueia tags script, javascript:, iframes e manipuladores de eventos.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="waf_block_path_traversal" value="1" <?php checked( $settings['waf_block_path_traversal'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Path Traversal & LFI/RFI', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Bloqueia ../, wrappers php:// e inclusão arbitrária de arquivos.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="waf_block_bad_user_agents" value="1" <?php checked( $settings['waf_block_bad_user_agents'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Malicious User Agents & Scanners', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Bloqueia sqlmap, nikto, dirbuster, hydra, acunetix e ferramentas de invasão.', 'siteshield' ); ?></small>
					</span>
				</label>
			</div>
			<div style="margin-top: 20px; text-align: right;">
				<button type="submit" class="ss-btn ss-btn-primary"><?php esc_html_e( 'Salvar Regras do Firewall', 'siteshield' ); ?></button>
			</div>
		</div>
	</div>
</form>

<!-- WAF EXCEPTIONS / ALLOWLIST DE ROTAS -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
		<h3><?php esc_html_e( 'WAF Exceptions & Safe Routes Allowlist', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<p style="font-size: 13px; color: #64748B; margin-bottom: 15px;">
			<?php esc_html_e( 'Adicione rotas ou IPs que devem ignorar a inspeção do Firewall para evitar falsos positivos.', 'siteshield' ); ?>
		</p>
		<div style="display: flex; gap: 10px; margin-bottom: 15px;">
			<input type="text" id="waf-exc-url" class="ss-input" placeholder="/exemplo-webhook/ ou IP" style="flex: 1;" />
			<input type="text" id="waf-exc-reason" class="ss-input" placeholder="Motivo da exceção" style="flex: 1;" />
			<button type="button" class="ss-btn ss-btn-primary" id="btn-add-waf-exception">
				<?php esc_html_e( 'Adicionar Exceção', 'siteshield' ); ?>
			</button>
		</div>

		<?php
		$exceptions = isset( $settings['waf_custom_exceptions'] ) && is_array( $settings['waf_custom_exceptions'] ) ? $settings['waf_custom_exceptions'] : array();
		?>
		<?php if ( empty( $exceptions ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhuma exceção manual configurada. Rotas padrão do WooCommerce e WP-Cron já são protegidas nativamente.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Valor / Rota', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Motivo', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Data', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Ação', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $exceptions as $exc ) : ?>
							<tr>
								<td><code><?php echo esc_html( $exc['value'] ); ?></code></td>
								<td><?php echo esc_html( $exc['reason'] ); ?></td>
								<td><?php echo esc_html( $exc['created_at'] ); ?></td>
								<td>
									<button type="button" class="ss-btn ss-btn-sm ss-btn-danger btn-remove-waf-exc" data-id="<?php echo esc_attr( $exc['id'] ); ?>">
										<?php esc_html_e( 'Remover', 'siteshield' ); ?>
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

<!-- RECENT FIREWALL EVENTS -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Recent Firewall Interceptions', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $events ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhum evento de ataque registrado até o momento.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Data/Hora', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Regra', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Severidade', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Ação', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Payload', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $events as $ev ) : ?>
							<tr>
								<td><?php echo esc_html( date( 'd/m H:i', strtotime( $ev->created_at ) ) ); ?></td>
								<td><code><?php echo esc_html( $ev->ip_address ); ?></code></td>
								<td><?php echo esc_html( $ev->rule_triggered ); ?></td>
								<td><span class="ss-badge ss-badge-neutral"><?php echo esc_html( strtoupper( $ev->severity ) ); ?></span></td>
								<td><span class="ss-badge ss-badge-primary"><?php echo esc_html( $ev->action_taken ); ?></span></td>
								<td><code class="code-snippet"><?php echo esc_html( substr( $ev->payload, 0, 50 ) ); ?></code></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
