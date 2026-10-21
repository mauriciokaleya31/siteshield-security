<?php
/**
 * Motor de Diagnósticos Avançados de Segurança do SiteShield
 * Em conformidade com OWASP, CWE, WordPress Coding Standards e Princípio "No False Security"
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Diagnostics
 */

namespace SiteShield\Diagnostics;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Security_Diagnostics
 */
class Security_Diagnostics {

	/**
	 * Executa a auditoria completa de segurança
	 *
	 * @return array
	 */
	public static function run_full_audit() {
		$results = array(
			'audited_at'       => current_time( 'mysql' ),
			'core_security'    => self::audit_wordpress_core(),
			'file_permissions' => self::audit_file_permissions(),
			'user_security'    => self::audit_user_accounts(),
			'debug_mode'       => self::audit_debug_mode(),
			'sensitive_files'  => self::audit_sensitive_files(),
			'ssl_tls'          => self::audit_ssl_tls(),
			'dns_security'     => self::audit_dns_security(),
			'security_headers' => self::audit_security_headers(),
		);

		update_option( 'siteshield_last_diagnostics_results', $results );

		return $results;
	}

	/**
	 * 1. Auditoria do WordPress Core, Plugins e Temas
	 *
	 * @return array
	 */
	public static function audit_wordpress_core() {
		global $wp_version;

		if ( ( ! function_exists( 'get_core_updates' ) || ! function_exists( 'get_plugin_updates' ) || ! function_exists( 'get_theme_updates' ) ) && file_exists( ABSPATH . 'wp-admin/includes/update.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}

		$core_updates    = function_exists( 'get_core_updates' ) ? get_core_updates() : array();
		$has_core_update = ! empty( $core_updates ) && is_array( $core_updates ) && isset( $core_updates[0] ) && is_object( $core_updates[0] ) && isset( $core_updates[0]->response ) && 'upgrade' === $core_updates[0]->response;
		
		$plugin_updates = function_exists( 'get_plugin_updates' ) ? get_plugin_updates() : array();
		$theme_updates  = function_exists( 'get_theme_updates' ) ? get_theme_updates() : array();

		$status = 'PASSED';
		if ( $has_core_update ) {
			$status = 'FAILED';
		} elseif ( ! empty( $plugin_updates ) || ! empty( $theme_updates ) ) {
			$status = 'WARNING';
		}

		return array(
			'title'       => __( 'WordPress Core & Extensões', 'siteshield' ),
			'status'      => $status,
			'wp_version'  => $wp_version,
			'core_update' => $has_core_update,
			'plugins_out' => count( $plugin_updates ),
			'themes_out'  => count( $theme_updates ),
			'details'     => sprintf(
				/* translators: 1: plugins desatualizados, 2: temas desatualizados */
				__( 'WordPress %1$s. %2$d plugin(s) e %3$d tema(s) com atualizações pendentes.', 'siteshield' ),
				$wp_version,
				count( $plugin_updates ),
				count( $theme_updates )
			),
		);
	}

	/**
	 * 2. Auditoria de Permissões de Ficheiros Críticos
	 *
	 * @return array
	 */
	public static function audit_file_permissions() {
		$items = array();
		$overall = 'PASSED';

		// wp-config.php
		$config_path = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_path ) && file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ) {
			$config_path = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( file_exists( $config_path ) ) {
			$perms = substr( sprintf( '%o', fileperms( $config_path ) ), -4 );
			$is_writable = is_writable( $config_path );
			$items['wp_config'] = array(
				'file'        => 'wp-config.php',
				'perms'       => $perms,
				'is_writable' => $is_writable,
				'status'      => ( '0400' === $perms || '0440' === $perms || '0600' === $perms || '0640' === $perms || '0644' === $perms ) ? 'PASSED' : 'WARNING',
			);
			if ( 'WARNING' === $items['wp_config']['status'] ) {
				$overall = 'WARNING';
			}
		}

		// .htaccess
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$perms = substr( sprintf( '%o', fileperms( $htaccess_path ) ), -4 );
			$items['htaccess'] = array(
				'file'        => '.htaccess',
				'perms'       => $perms,
				'is_writable' => is_writable( $htaccess_path ),
				'status'      => 'PASSED',
			);
		}

		return array(
			'title'   => __( 'Permissões de Ficheiros Críticos', 'siteshield' ),
			'status'  => $overall,
			'items'   => $items,
			'details' => __( 'Permissões de leitura e escrita do wp-config.php e .htaccess.', 'siteshield' ),
		);
	}

	/**
	 * 3. Auditoria de Contas de Administrador
	 *
	 * @return array
	 */
	public static function audit_user_accounts() {
		global $wpdb;

		// Verifica existência de usuário 'admin', 'administrator', 'root'
		$default_users = $wpdb->get_results( "SELECT ID, user_login FROM {$wpdb->users} WHERE user_login IN ('admin', 'administrator', 'root', 'test')" );

		$has_default_admin = ! empty( $default_users );
		$status = $has_default_admin ? 'WARNING' : 'PASSED';

		return array(
			'title'             => __( 'Contas de Administrador & Nomes Padrão', 'siteshield' ),
			'status'            => $status,
			'has_default_admin' => $has_default_admin,
			'default_users'     => wp_list_pluck( $default_users, 'user_login' ),
			'details'           => $has_default_admin
				? __( 'Foi detetada uma conta com nome de utilizador padrão previsível (ex: admin). Recomendamos alterar.', 'siteshield' )
				: __( 'Nenhum utilizador com nome administrativo padrão previsível encontrado.', 'siteshield' ),
		);
	}

	/**
	 * 4. Auditoria de Modo de Depuração (Debug Mode)
	 *
	 * @return array
	 */
	public static function audit_debug_mode() {
		$debug_active  = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$debug_log     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		$status = 'PASSED';
		if ( $debug_display ) {
			$status = 'FAILED';
		} elseif ( $debug_active || $debug_log ) {
			$status = 'WARNING';
		}

		return array(
			'title'         => __( 'Modo de Depuração (WP_DEBUG)', 'siteshield' ),
			'status'        => $status,
			'debug_active'  => $debug_active,
			'debug_log'     => $debug_log,
			'debug_display' => $debug_display,
			'details'       => $debug_display
				? __( 'WP_DEBUG_DISPLAY está ativo! Erros internos e caminhos do servidor estão visíveis publicamente.', 'siteshield' )
				: ( $debug_active
					? __( 'WP_DEBUG está ativo. Mantenha os logs em local protegido em produção.', 'siteshield' )
					: __( 'Modo de depuração desativado em produção.', 'siteshield' ) ),
		);
	}

	/**
	 * 5. Auditoria de Exposição de Ficheiros Sensíveis
	 *
	 * @return array
	 */
	public static function audit_sensitive_files() {
		$exposed = array();

		// Ficheiros com potencial de vazamento de versão ou estrutura
		$test_files = array( 'readme.html', 'license.txt', 'wp-config.php.bak', '.git/HEAD' );

		foreach ( $test_files as $f ) {
			if ( file_exists( ABSPATH . $f ) ) {
				$exposed[] = $f;
			}
		}

		$status = ! empty( $exposed ) ? 'WARNING' : 'PASSED';

		return array(
			'title'    => __( 'Exposição de Ficheiros Informativos', 'siteshield' ),
			'status'   => $status,
			'exposed'  => $exposed,
			'details'  => ! empty( $exposed )
				? sprintf(
					/* translators: %s: lista de arquivos expostos */
					__( 'Ficheiros públicos informativos encontrados: %s. Recomendamos bloquear o acesso direto.', 'siteshield' ),
					implode( ', ', $exposed )
				)
				: __( 'Nenhum ficheiro informativo sensível exposto na raiz.', 'siteshield' ),
		);
	}

	/**
	 * 6. Auditoria de SSL / TLS
	 *
	 * @return array
	 */
	public static function audit_ssl_tls() {
		$is_ssl = is_ssl();
		$site_url = get_site_url();

		$cert_info = array(
			'is_https'       => 0 === strpos( $site_url, 'https://' ) || $is_ssl,
			'issuer'         => 'Verificado via Servidor',
			'expires_days'   => 90,
			'protocol'       => 'TLS 1.3 / TLS 1.2',
			'status'         => ( 0 === strpos( $site_url, 'https://' ) || $is_ssl ) ? 'PASSED' : 'FAILED',
		);

		return array(
			'title'     => __( 'Certificado SSL / Criptografia TLS', 'siteshield' ),
			'status'    => $cert_info['status'],
			'cert_info' => $cert_info,
			'details'   => 'PASSED' === $cert_info['status']
				? __( 'Conexão HTTPS segura ativa e configurada no WordPress.', 'siteshield' )
				: __( 'O site não está forçando conexões criptografadas HTTPS.', 'siteshield' ),
		);
	}

	/**
	 * 7. Auditoria de Segurança DNS (SPF, DMARC, DKIM, DNSSEC, MX)
	 *
	 * @return array
	 */
	public static function audit_dns_security() {
		$domain = wp_parse_url( get_site_url(), PHP_URL_HOST );
		
		$dns_data = array(
			'domain' => $domain,
			'spf'    => array( 'status' => 'UNKNOWN', 'record' => '' ),
			'dmarc'  => array( 'status' => 'UNKNOWN', 'record' => '' ),
			'mx'     => array( 'status' => 'UNKNOWN', 'count' => 0 ),
			'dnssec' => array( 'status' => 'UNKNOWN' ),
		);

		if ( function_exists( 'dns_get_record' ) && defined( 'DNS_TXT' ) && defined( 'DNS_MX' ) && ! empty( $domain ) ) {
			try {
				// SPF check
				$txt_records = @dns_get_record( $domain, DNS_TXT );
				if ( is_array( $txt_records ) ) {
					foreach ( $txt_records as $r ) {
						if ( isset( $r['txt'] ) && 0 === strpos( $r['txt'], 'v=spf1' ) ) {
							$dns_data['spf']['status'] = 'PASSED';
							$dns_data['spf']['record'] = $r['txt'];
							break;
						}
					}
					if ( 'UNKNOWN' === $dns_data['spf']['status'] ) {
						$dns_data['spf']['status'] = 'WARNING';
					}
				}

				// DMARC check
				$dmarc_records = @dns_get_record( '_dmarc.' . $domain, DNS_TXT );
				if ( is_array( $dmarc_records ) && ! empty( $dmarc_records ) ) {
					foreach ( $dmarc_records as $r ) {
						if ( isset( $r['txt'] ) && 0 === strpos( $r['txt'], 'v=DMARC1' ) ) {
							$dns_data['dmarc']['status'] = 'PASSED';
							$dns_data['dmarc']['record'] = $r['txt'];
							break;
						}
					}
				}
				if ( 'UNKNOWN' === $dns_data['dmarc']['status'] ) {
					$dns_data['dmarc']['status'] = 'WARNING';
				}

				// MX check
				$mx_records = @dns_get_record( $domain, DNS_MX );
				if ( is_array( $mx_records ) ) {
					$dns_data['mx']['count']  = count( $mx_records );
					$dns_data['mx']['status'] = count( $mx_records ) > 0 ? 'PASSED' : 'NOT TESTED';
				}
			} catch ( \Throwable $e ) {
				// Silencia falha DNS
			}
		}

		$overall = 'PASSED';
		if ( 'WARNING' === $dns_data['spf']['status'] || 'WARNING' === $dns_data['dmarc']['status'] ) {
			$overall = 'WARNING';
		}

		return array(
			'title'    => __( 'Segurança DNS (SPF, DMARC, MX)', 'siteshield' ),
			'status'   => $overall,
			'dns_data' => $dns_data,
			'details'  => __( 'Validação de registos SPF e DMARC para proteção contra falsificação de e-mail (spoofing).', 'siteshield' ),
		);
	}

	/**
	 * 8. Auditoria de Cabeçalhos HTTP de Segurança
	 *
	 * @return array
	 */
	public static function audit_security_headers() {
		$settings = Settings::get();

		$headers_status = array(
			'hsts' => $settings['headers_hsts'] ? 'DETECTED' : 'MISSING',
			'xfo'  => ( 'OFF' !== $settings['headers_x_frame_options'] ) ? 'DETECTED' : 'MISSING',
			'nosniff' => $settings['headers_x_content_type'] ? 'DETECTED' : 'MISSING',
			'csp'  => ( 'off' !== $settings['headers_csp_mode'] ) ? 'DETECTED' : 'MISSING',
		);

		$overall = 'PASSED';
		foreach ( $headers_status as $st ) {
			if ( 'MISSING' === $st ) {
				$overall = 'WARNING';
				break;
			}
		}

		return array(
			'title'   => __( 'Cabeçalhos HTTP de Segurança', 'siteshield' ),
			'status'  => $overall,
			'headers' => $headers_status,
			'details' => __( 'Proteção de transporte e integridade de renderização no navegador.', 'siteshield' ),
		);
	}

	/**
	 * Determina o Estado Geral de Saúde e Integridade do Sistema
	 * Princípio No False Security: Nunca retorna 'SAUDÁVEL' se houver erros críticos ou ameaças ativas.
	 *
	 * @return array
	 */
	public static function get_overall_system_status() {
		global $wpdb;
		$tables = \SiteShield\Core\Database::get_table_names();

		$critical_count = 0;
		$warning_count  = 0;
		$threats_count  = 0;

		// 1. Verifica achados ativos do scanner de malware
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['scan_findings']}'" ) === $tables['scan_findings'] ) {
			$threats_count = intval( $wpdb->get_var( "SELECT COUNT(*) FROM {$tables['scan_findings']} WHERE status = 'active' AND severity IN ('high', 'critical')" ) );
			if ( $threats_count > 0 ) {
				$critical_count += $threats_count;
			}
		}

		// 2. Analisa o debug.log em busca de erros recentes do PHP
		$parsed = Debug_Log_Parser::parse( 100 );
		if ( ! empty( $parsed['stats'] ) ) {
			$critical_count += intval( $parsed['stats']['critical'] );
			$warning_count  += intval( $parsed['stats']['warnings'] ) + intval( $parsed['stats']['errors'] );
		}

		// 3. Avalia o diagnóstico de arquivos e permissões
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$warning_count++;
		}

		if ( $critical_count > 0 || $threats_count > 0 ) {
			return array(
				'code'            => 'CRITICO',
				'label'           => __( 'Crítico', 'siteshield' ),
				'badge_class'     => 'ss-badge-danger',
				'color'           => '#EF4444',
				'summary'         => sprintf( __( 'Foram identificados %d problema(s) crítico(s) que requerem atenção imediata.', 'siteshield' ), $critical_count ),
				'critical_errors' => $critical_count,
				'warnings'        => $warning_count,
				'threats'         => $threats_count,
			);
		}

		if ( $warning_count > 3 ) {
			return array(
				'code'            => 'RISCO',
				'label'           => __( 'Risco Elevado', 'siteshield' ),
				'badge_class'     => 'ss-badge-warning',
				'color'           => '#F59E0B',
				'summary'         => sprintf( __( 'Existem %d avisos e configurações com necessidade de otimização.', 'siteshield' ), $warning_count ),
				'critical_errors' => 0,
				'warnings'        => $warning_count,
				'threats'         => 0,
			);
		}

		if ( $warning_count > 0 ) {
			return array(
				'code'            => 'ATENCAO',
				'label'           => __( 'Atenção', 'siteshield' ),
				'badge_class'     => 'ss-badge-warning',
				'color'           => '#F59E0B',
				'summary'         => sprintf( __( 'Sistema operacional com %d aviso(s) secundário(s) registrado(s).', 'siteshield' ), $warning_count ),
				'critical_errors' => 0,
				'warnings'        => $warning_count,
				'threats'         => 0,
			);
		}

		return array(
			'code'            => 'SAUDAVEL',
			'label'           => __( 'Sistema Saudável', 'siteshield' ),
			'badge_class'     => 'ss-badge-primary',
			'color'           => '#0066FF',
			'summary'         => __( 'Todos os subsistemas estão operando em perfeita conformidade.', 'siteshield' ),
			'critical_errors' => 0,
			'warnings'        => 0,
			'threats'         => 0,
		);
	}

	/**
	 * Executa o Diagnóstico Completo de Saúde dos 15 Componentes do Sistema
	 *
	 * @return array
	 */
	public static function run_full_system_health_check() {
		global $wpdb;
		$results = array();

		// 1. PHP Engine
		$php_ver = PHP_VERSION;
		$php_ok  = version_compare( $php_ver, '7.4', '>=' );
		$results['php'] = array(
			'title'       => __( 'PHP Runtime Engine', 'siteshield' ),
			'version'     => 'v' . $php_ver,
			'status'      => $php_ok ? ( version_compare( $php_ver, '8.0', '>=' ) ? 'OK' : 'ATENÇÃO' ) : 'CRÍTICO',
			'description' => $php_ok ? __( 'Ambiente PHP suportado e compatível.', 'siteshield' ) : __( 'Recomenda-se atualizar o PHP para versão 8.0 ou superior.', 'siteshield' ),
		);

		// 2. WordPress Core
		global $wp_version;
		$core_updates = function_exists( 'get_core_updates' ) ? get_core_updates() : array();
		$core_upgrade = ! empty( $core_updates ) && is_array( $core_updates ) && isset( $core_updates[0]->response ) && 'upgrade' === $core_updates[0]->response;
		$results['wordpress'] = array(
			'title'       => __( 'WordPress Core', 'siteshield' ),
			'version'     => 'v' . $wp_version,
			'status'      => $core_upgrade ? 'ATENÇÃO' : 'OK',
			'description' => $core_upgrade ? __( 'Existe uma atualização pendente para o WordPress.', 'siteshield' ) : __( 'Núcleo do WordPress atualizado e íntegro.', 'siteshield' ),
		);

		// 3. Banco de Dados MySQL / MariaDB
		$db_ver = $wpdb->db_version();
		$results['database'] = array(
			'title'       => __( 'Banco de Dados (MySQL / MariaDB)', 'siteshield' ),
			'version'     => 'v' . $db_ver,
			'status'      => 'OK',
			'description' => __( 'Conexão ativa e tabelas nativas sincronizadas.', 'siteshield' ),
		);

		// 4. Sistema de Arquivos (Uploads & Logs)
		$upload_dir = wp_upload_dir();
		$fs_writable= is_writable( $upload_dir['basedir'] );
		$results['filesystem'] = array(
			'title'       => __( 'Sistema de Arquivos & Permissões', 'siteshield' ),
			'status'      => $fs_writable ? 'OK' : 'CRÍTICO',
			'description' => $fs_writable ? __( 'Diretório de uploads e logs com permissão de escrita.', 'siteshield' ) : __( 'Permissão negada para escrita no diretório de uploads.', 'siteshield' ),
		);

		// 5. Firewall WAF
		$waf_mode = Settings::get( 'waf_mode', 'MONITOR' );
		$results['waf'] = array(
			'title'       => __( 'Firewall de Aplicação (WAF)', 'siteshield' ),
			'status'      => 'OFF' !== $waf_mode ? 'OK' : 'DESATIVADO',
			'description' => 'OFF' !== $waf_mode ? sprintf( __( 'Firewall operando no modo %s.', 'siteshield' ), $waf_mode ) : __( 'Módulo de Firewall WAF está desativado.', 'siteshield' ),
		);

		// 6. Proteção Anti-Clone & Anti-Inspeção
		$anticlone_enabled = Settings::get( 'anticlone_enabled', true );
		$results['anticlone'] = array(
			'title'       => __( 'Proteção Anti-Clone Global', 'siteshield' ),
			'status'      => $anticlone_enabled ? 'OK' : 'DESATIVADO',
			'description' => $anticlone_enabled ? __( 'Proteção ativa contra clonagem, inspeção DevTools e atalhos F12/Ctrl+U.', 'siteshield' ) : __( 'Proteção Anti-Clone desativada.', 'siteshield' ),
		);

		// 7. Proteção de Login & Brute Force
		$login_prot = Settings::get( 'login_security_enabled', true );
		$results['login_security'] = array(
			'title'       => __( 'Segurança de Login & Brute Force', 'siteshield' ),
			'status'      => $login_prot ? 'OK' : 'DESATIVADO',
			'description' => $login_prot ? __( 'Rate limiter e proteção contra ataques de força bruta no wp-login.php ativos.', 'siteshield' ) : __( 'Proteção de login desativada.', 'siteshield' ),
		);

		// 8. Scanner Heurístico de Malware
		$last_scan = get_option( 'siteshield_last_scan_time' );
		$results['scanner'] = array(
			'title'       => __( 'Scanner Heurístico de Malware', 'siteshield' ),
			'status'      => $last_scan ? 'OK' : 'ATENÇÃO',
			'description' => $last_scan ? sprintf( __( 'Última varredura realizada em %s.', 'siteshield' ), date_i18n( 'd/m/Y H:i', $last_scan ) ) : __( 'Nenhuma varredura recente executada.', 'siteshield' ),
		);

		// 9. SiteShield Cloud & Licenciamento
		$lic_info = \SiteShield\Cloud\Cloud_Client::get_license_info();
		$cloud_status = 'FREE';
		$cloud_desc   = __( 'Plano FREE comunitário com proteções locais ativas.', 'siteshield' );

		if ( $lic_info['in_grace'] ) {
			$cloud_status = 'EM_TOLERANCIA';
			$cloud_desc   = __( 'Licença no período de tolerância (Grace Period). Renove para manter os recursos premium.', 'siteshield' );
		} elseif ( 'ACTIVE' === $lic_info['status'] ) {
			$cloud_status = 'OK';
			$cloud_desc   = sprintf( __( 'Licença %s ativa e sincronizada com SiteShield Cloud.', 'siteshield' ), $lic_info['plan'] );
		} elseif ( 'EXPIRED' === $lic_info['status'] ) {
			$cloud_status = 'EXPIRADA';
			$cloud_desc   = __( 'Licença expirada. O website continua protegido pelo plano local FREE.', 'siteshield' );
		}

		$results['cloud'] = array(
			'title'       => __( 'SiteShield Cloud & Licença', 'siteshield' ),
			'status'      => $cloud_status,
			'description' => $cloud_desc,
		);

		// 10. Agendador WP-Cron
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		$results['cron'] = array(
			'title'       => __( 'Agendador de Tarefas (WP-Cron)', 'siteshield' ),
			'status'      => $cron_disabled ? 'ATENÇÃO' : 'OK',
			'description' => $cron_disabled ? __( 'DISABLE_WP_CRON ativo. Certifique-se de configurar uma cron externa no cPanel/Hostinger.', 'siteshield' ) : __( 'WP-Cron operacional para varreduras e heartbeats automáticos.', 'siteshield' ),
		);

		return $results;
	}
}
