<?php
/**
 * Gerenciador de Sessões Administrativas Ativas e Logout Remoto do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Login
 */

namespace SiteShield\Login;

use SiteShield\Events\Event_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Session_Manager
 */
class Session_Manager {

	/**
	 * Retorna todas as sessões ativas dos administradores do site
	 *
	 * @return array
	 */
	public static function get_active_admin_sessions() {
		$sessions_list = array();

		$admins = get_users( array( 'role' => 'administrator' ) );

		foreach ( $admins as $admin ) {
			if ( class_exists( 'WP_Session_Tokens' ) ) {
				$manager = \WP_Session_Tokens::get_instance( $admin->ID );
				$tokens  = $manager->get_all();

				foreach ( $tokens as $verifier => $session_data ) {
					$ua = isset( $session_data['ua'] ) ? $session_data['ua'] : '';
					$ip = isset( $session_data['ip'] ) ? $session_data['ip'] : '127.0.0.1';

					$browser = 'Desconhecido';
					if ( false !== strpos( $ua, 'Chrome' ) ) {
						$browser = 'Google Chrome';
					} elseif ( false !== strpos( $ua, 'Firefox' ) ) {
						$browser = 'Mozilla Firefox';
					} elseif ( false !== strpos( $ua, 'Safari' ) ) {
						$browser = 'Apple Safari';
					} elseif ( false !== strpos( $ua, 'Edge' ) ) {
						$browser = 'Microsoft Edge';
					}

					$os = 'Desktop';
					if ( false !== strpos( $ua, 'Windows' ) ) {
						$os = 'Windows';
					} elseif ( false !== strpos( $ua, 'Macintosh' ) ) {
						$os = 'macOS';
					} elseif ( false !== strpos( $ua, 'Linux' ) ) {
						$os = 'Linux';
					} elseif ( false !== strpos( $ua, 'Android' ) ) {
						$os = 'Android';
					} elseif ( false !== strpos( $ua, 'iPhone' ) ) {
						$os = 'iOS';
					}

					$is_current = ( get_current_user_id() === $admin->ID && function_exists( 'wp_get_session_token' ) && wp_get_session_token() === $verifier );

					$sessions_list[] = array(
						'verifier'    => substr( $verifier, 0, 12 ),
						'user_id'     => $admin->ID,
						'user_login'  => $admin->user_login,
						'ip_address'  => $ip,
						'browser'     => $browser,
						'os'          => $os,
						'login_time'  => isset( $session_data['login'] ) ? date( 'd/m/Y H:i', $session_data['login'] ) : 'N/A',
						'expirateion'  => isset( $session_data['expirateion'] ) ? date( 'd/m/Y H:i', $session_data['expirateion'] ) : 'N/A',
						'is_current'  => $is_current,
					);
				}
			}
		}

		return $sessions_list;
	}

	/**
	 * Destrói todas as outras sessões ativas do usuário exceto a atual
	 *
	 * @param int $user_id ID do usuário.
	 * @return bool
	 */
	public static function destroy_other_sessions( $user_id ) {
		if ( class_exists( 'WP_Session_Tokens' ) ) {
			$sessions = \WP_Session_Tokens::get_instance( $user_id );
			$sessions->destroy_others( wp_get_session_token() );
			Event_Center::record_event( 'sessions_destroyed_others', Event_Center::SEV_MEDIUM, 'Todas as outras sessões ativas foram encerradas.', '', $user_id );
			return true;
		}
		return false;
	}

	/**
	 * Destrói absolutamente todas as sessões ativas de um usuário
	 *
	 * @param int $user_id ID do usuário.
	 * @return bool
	 */
	public static function destroy_all_sessions( $user_id ) {
		if ( class_exists( 'WP_Session_Tokens' ) ) {
			$sessions = \WP_Session_Tokens::get_instance( $user_id );
			$sessions->destroy_all();
			Event_Center::record_event( 'sessions_destroyed_all', Event_Center::SEV_HIGH, 'Todas as sessões de login foram terminadas forçadamente.', '', $user_id );
			return true;
		}
		return false;
	}
}
