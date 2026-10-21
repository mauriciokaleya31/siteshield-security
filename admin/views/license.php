<?php
/**
 * View de Gestão de Licença — Edição Demo Gratuita (1.ª Versão)
 *
 * @package SiteShield_Security
 * @author Maurício Kaleya <kaleyapt@gmail.com>
 */

use SiteShield\Cloud\Cloud_Client;
use SiteShield\Core\Feature_Manager;
use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lic       = Cloud_Client::get_license_info();
$identity  = Cloud_Client::get_identity();
$cloud_url = Cloud_Client::get_cloud_url();
?>
<div class="wrap siteshield-admin-wrap">
	<div class="siteshield-header">
		<div>
			<h1><?php esc_html_e( 'Licença & Edição Demo Gratuita (1.ª Versão)', 'siteshield' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Esta é a Primeira Versão Pública de Demonstração (Demo Edition) criada por Maurício Kaleya — todos os recursos estão 100% liberados gratuitamente.', 'siteshield' ); ?>
			</p>
		</div>
		<div>
			<span class="siteshield-plan-badge" style="font-size: 0.95rem; padding: 6px 14px; border-radius: 20px; font-weight: 700; background: #059669; color: #FFF;">
				🎉 DEMO FREE — 100% DESBLOQUEADO
			</span>
		</div>
	</div>

	<div class="notice notice-success" style="border-left-color: #10B981; padding: 14px 18px; margin: 15px 0; background: #ECFDF5;">
		<p style="font-size: 0.95rem; font-weight: 600; color: #065F46; margin: 0;">
			✓ <?php esc_html_e( 'Versão Demo (1.ª Versão) Ativa: Não é necessária nenhuma chave paga. Todos os módulos Enterprise & PRO (WAF 4 Níveis, 2FA TOTP, Scanner Profundo, Quarentena, Integridade SHA-256, Anti-Clone, Relatórios PDF e Selo SiteShield Verified) estão liberados gratuitamente!', 'siteshield' ); ?>
		</p>
	</div>

	<div class="siteshield-dashboard-grid" style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 20px;">
		<!-- COLUNA PRINCIPAL: ESTADO DA LICENÇA DEMO -->
		<div>
			<div class="siteshield-card" style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 25px; margin-bottom: 24px;">
				<h3 style="margin-top: 0; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; font-size: 1.15rem;">
					<?php esc_html_e( 'Informações da Edição Atual', 'siteshield' ); ?>
				</h3>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Edição / Plano:', 'siteshield' ); ?></th>
						<td>
							<strong style="font-size: 1.1rem; color: #059669;">DEMO FREE — PRIMEIRA VERSÃO (FULL UNLOCKED)</strong>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Estado dos Recursos:', 'siteshield' ); ?></th>
						<td>
							<span style="display: inline-flex; align-items: center; gap: 6px; color: #059669; font-weight: 700; background: #ECFDF5; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;">
								<span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span>
								100% ATIVOS E LIBERADOS GRATUITAMENTE
							</span>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Chave de Demonstração:', 'siteshield' ); ?></th>
						<td>
							<code style="font-size: 0.95rem; padding: 4px 10px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 4px;">
								<?php echo esc_html( $lic['license_key'] ); ?>
							</code>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Criador / Autor:', 'siteshield' ); ?></th>
						<td>
							<strong>Maurício Kaleya</strong> (<a href="https://github.com/mauriciokaleya31/siteshield-security" target="_blank" rel="noopener noreferrer">@mauriciokaleya31</a>)
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Recursos Incluídos:', 'siteshield' ); ?></th>
						<td>
							<ul style="margin: 0; padding-left: 18px; color: #334155; line-height: 1.8;">
								<li>✓ Firewall WAF Inteligente (Modos OFF, MONITOR e BLOCK com 4 Níveis)</li>
								<li>✓ Segurança de Login (URL Personalizada, Anti-Brute Force e 2FA TOTP)</li>
								<li>✓ Scanner Heurístico de Malware (Rápido e Profundo) + Quarentena Segura</li>
								<li>✓ Monitor de Integridade de Arquivos (Baseline SHA-256)</li>
								<li>✓ Proteção de Conteúdo Anti-Clone, Anti-Hotlink e Anti-Scraping</li>
								<li>✓ Relatórios Executivos em PDF, CSV e JSON</li>
								<li>✓ Selo de Segurança Flutuante <strong>SiteShield Verified</strong></li>
							</ul>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<!-- BARRA LATERAL: IDENTIDADE & REPOSITÓRIO -->
		<div>
			<div class="siteshield-card" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
				<h4 style="margin-top: 0; font-size: 0.95rem; color: #0F172A;">Identificadores Desta Instalação</h4>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 6px;">
					<strong>Verification ID:</strong><br>
					<code><?php echo esc_html( $lic['verification_id'] ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 6px; word-break: break-all;">
					<strong>Installation ID:</strong><br>
					<code><?php echo esc_html( substr( $identity['installation_id'], 0, 24 ) . '...' ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 6px;">
					<strong>Domínio:</strong><br>
					<code><?php echo esc_html( $identity['domain'] ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 0;">
					<strong>Versão:</strong><br>
					<code>v<?php echo esc_html( $identity['plugin_version'] ); ?> (Demo 1.ª Versão)</code>
				</p>
			</div>

			<div class="siteshield-card" style="background: linear-gradient(135deg, #0066FF 0%, #0047BA 100%); color: #FFF; border-radius: 10px; padding: 22px; text-align: center;">
				<h4 style="color: #FFF; margin-top: 0; font-size: 1.1rem;">Repositório Oficial GitHub</h4>
				<p style="color: #BFDBFE; font-size: 0.85rem; line-height: 1.5; margin-bottom: 18px;">
					Acompanhe atualizações, documentação completa e reporte sugestões no GitHub oficial do projeto.
				</p>
				<a href="https://github.com/mauriciokaleya31/siteshield-security" target="_blank" rel="noopener noreferrer" class="button button-primary" style="background: #FFF; color: #0066FF; border-color: #FFF; font-weight: 700; width: 100%;">
					Ver no GitHub &rarr;
				</a>
			</div>
		</div>
	</div>
</div>
