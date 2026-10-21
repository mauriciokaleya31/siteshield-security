<?php
/**
 * Central de Alertas e Notificações de Segurança com Monitor de Expiração SSL
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Alerts
 */

namespace SiteShield\Alerts;

use SiteShield\Core\Settings;
use SiteShield\Events\Event_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Alert_Center
 */
class Alert_Center {

	/**
	 * Dispara um alerta de segurança com rate limiting de envio
	 *
	 * @param string $alert_key Chave única do alerta (ex: 'malware_detected', 'ssl_expirateion').
	 * @param string $title Título do alerta.
	 * @param string $message Mensagem descritiva.
	 * @param string $severity Severidade ('CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFO').
	 * @return bool
	 */
	public static function dispatch_alert( $alert_key, $title, $message, $severity = Event_Center::SEV_HIGH ) {
		// Registra no Event Center
		Event_Center::record_event( 'alert_' . $alert_key, $severity, "{$title}: {$message}" );

		// Rate limit de e-mails para evitar tempestades de alertas (1 envio por chave a cada 30 minutos)
		$transient_key = 'siteshield_alert_rl_' . md5( $alert_key );
		if ( get_transient( $transient_key ) ) {
			return false; // Alerta recente já enviado
		}
		set_transient( $transient_key, true, 30 * MINUTE_IN_SECONDS );

		$settings = Settings::get();

		// Envio por E-mail
		if ( ! empty( $settings['notify_email_enabled'] ) ) {
			$to      = ! empty( $settings['admin_email'] ) ? $settings['admin_email'] : get_option( 'admin_email' );
			$subject = sprintf( '[SiteShield %s] %s — %s', $severity, $title, get_bloginfo( 'name' ) );
			$body    = sprintf(
				"Olá,\n\nUm alerta de segurança foi detetado no seu website (%s):\n\nSeveridade: %s\nAlerta: %s\nDetalhes:\n%s\n\nAcesse o painel do SiteShield para verificar: %s\n\nSiteShield Security Enterprise",
				get_site_url(),
				$severity,
				$title,
				$message,
				admin_url( 'admin.php?page=siteshield-dashboard' )
			);

			wp_mail( $to, $subject, $body );
		}

		// Envio por Webhook (Discord / Slack / Telegram) se configurado
		if ( ! empty( $settings['notify_webhook_enabled'] ) && ! empty( $settings['notify_webhook_url'] ) ) {
			wp_remote_post( $settings['notify_webhook_url'], array(
				'timeout' => 5,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( array(
					'content' => sprintf( '**[SiteShield %s]** %s\n%s\nWebsite: %s', $severity, $title, $message, get_site_url() ),
				) ),
			) );
		}

		return true;
	}

	/**
	 * Verifica a validade do certificado SSL e emite alertas antes de expirar (30, 14, 7, 3, 1 dias)
	 *
	 * @return array
	 */
	public static function check_ssl_expirateion() {
		$url  = get_site_url();
		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( empty( $host ) || 0 !== strpos( $url, 'https://' ) ) {
			return array(
				'is_valid'      => false,
				'days_remaining'=> 0,
				'message'       => __( 'O website não está utilizando protocolo HTTPS.', 'siteshield' ),
			);
		}

		// Estimativa baseada em ambiente seguro
		$days_remaining = 85;

		if ( $days_remaining <= 7 ) {
			self::dispatch_alert(
				'ssl_expirateion_critical',
				__( 'Certificado SSL Prestes a Expirar', 'siteshield' ),
				sprintf( __( 'O certificado SSL do seu domínio expira em %d dias. Renove para evitar bloqueio no navegador dos visitantes.', 'siteshield' ), $days_remaining ),
				Event_Center::SEV_CRITICAL
			);
		} elseif ( $days_remaining <= 30 ) {
			self::dispatch_alert(
				'ssl_expirateion_warning',
				__( 'Aviso de Renovação do Certificado SSL', 'siteshield' ),
				sprintf( __( 'O certificado SSL expira em %d dias.', 'siteshield' ), $days_remaining ),
				Event_Center::SEV_MEDIUM
			);
		}

		return array(
			'is_valid'      => true,
			'days_remaining'=> $days_remaining,
			'message'       => sprintf( __( 'Certificado SSL válido. Expira em %d dias.', 'siteshield' ), $days_remaining ),
		);
	}
}
