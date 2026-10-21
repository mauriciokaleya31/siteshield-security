<?php
/**
 * Motor de Autenticação de Dois Fatores (2FA TOTP - RFC 6238) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Login
 */

namespace SiteShield\Login;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Two_Factor
 */
class Two_Factor {

	/**
	 * Meta keys do usuário
	 */
	const SECRET_META_KEY   = 'siteshield_2fa_secret';
	const ENABLED_META_KEY  = 'siteshield_2fa_enabled';
	const BACKUP_META_KEY   = 'siteshield_2fa_backup_codes';

	/**
	 * Inicializa os ganchos do 2FA
	 */
	public static function init() {
		if ( ! Settings::get( 'two_factor_enabled', false ) ) {
			return;
		}

		add_action( 'wp_login', array( __CLASS__, 'handle_post_login_2fa' ), 10, 2 );
		add_action( 'login_form_siteshield_2fa', array( __CLASS__, 'render_2fa_login_prompt' ) );
		add_action( 'login_form_siteshield_verify_2fa', array( __CLASS__, 'verify_2fa_login_submission' ) );
	}

	/**
	 * Gera uma chave secreta Base32 segura de 16 caracteres para TOTP
	 *
	 * @return string
	 */
	public static function generate_secret() {
		$valid_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret      = '';
		$random      = function_exists( 'random_bytes' ) ? random_bytes( 16 ) : wp_generate_password( 16, false, false );
		for ( $i = 0; $i < 16; $i++ ) {
			$secret .= $valid_chars[ ord( $random[ $i ] ) & 31 ];
		}
		return $secret;
	}

	/**
	 * Calcula o código TOTP de 6 dígitos baseado no timestamp atual (RFC 6238)
	 *
	 * @param string   $secret Chave secreta Base32.
	 * @param int|null $time_slice Slice de tempo (30s).
	 * @return string
	 */
	public static function calculate_code( $secret, $time_slice = null ) {
		if ( null === $time_slice ) {
			$time_slice = floor( time() / 30 );
		}

		$secret_key = self::base32_decode( $secret );
		$time       = chr(0) . chr(0) . chr(0) . chr(0) . pack( 'N*', $time_slice );
		$hmac       = hash_hmac( 'sha1', $time, $secret_key, true );
		$offset     = ord( substr( $hmac, -1 ) ) & 0x0F;
		$hash_part  = substr( $hmac, $offset, 4 );
		$unpacked   = unpack( 'N', $hash_part );
		$value      = ( is_array( $unpacked ) && isset( $unpacked[1] ) ) ? ( $unpacked[1] & 0x7FFFFFFF ) : 0;

		return str_pad( (string) ( $value % 1000000 ), 6, '0', STR_PAD_LEFT );
	}

	/**
	 * Valida o código de 6 dígitos inserido pelo usuário (com tolerância de ±1 slice de 30s)
	 *
	 * @param string $secret Chave Base32.
	 * @param string $code Código de 6 dígitos.
	 * @return bool
	 */
	public static function verify_code( $secret, $code ) {
		$code = trim( $code );
		if ( strlen( $code ) !== 6 || ! ctype_digit( $code ) ) {
			return false;
		}

		$current_slice = floor( time() / 30 );
		// Verifica slice anterior (-30s), atual e próximo (+30s) para compensar desvios de relógio
		for ( $i = -1; $i <= 1; $i++ ) {
			$calculated = self::calculate_code( $secret, $current_slice + $i );
			if ( hash_equals( $calculated, $code ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gera e salva 8 códigos de recuperação de uso único (hasheados com SHA-256)
	 *
	 * @param int $user_id ID do usuário.
	 * @return array Códigos em texto puro para exibição uma única vez ação usuário.
	 */
	public static function generate_backup_codes( $user_id ) {
		$plain_codes  = array();
		$hashed_codes = array();

		for ( $i = 0; $i < 8; $i++ ) {
			$code           = strtoupper( bin2hex( random_bytes( 4 ) ) );
			$plain_codes[]  = $code;
			$hashed_codes[] = hash( 'sha256', $code );
		}

		update_user_meta( $user_id, self::BACKUP_META_KEY, $hashed_codes );
		return $plain_codes;
	}

	/**
	 * Valida e consome um código de recuperação de emergência
	 *
	 * @param int    $user_id ID do usuário.
	 * @param string $code Código de backup.
	 * @return bool
	 */
	public static function verify_and_consume_backup_code( $user_id, $code ) {
		$hashed_codes = get_user_meta( $user_id, self::BACKUP_META_KEY, true );
		if ( empty( $hashed_codes ) || ! is_array( $hashed_codes ) ) {
			return false;
		}

		$input_hash = hash( 'sha256', trim( strtoupper( $code ) ) );
		$key        = array_search( $input_hash, $hashed_codes, true );

		if ( false !== $key ) {
			unset( $hashed_codes[ $key ] );
			update_user_meta( $user_id, self::BACKUP_META_KEY, array_values( $hashed_codes ) );
			return true;
		}

		return false;
	}

	/**
	 * Intercepta o login bem-sucedido para exigir 2FA se o usuário tiver ativado
	 */
	public static function handle_post_login_2fa( $user_login, $user ) {
		if ( ! is_a( $user, 'WP_User' ) ) {
			return;
		}

		$is_2fa_active = get_user_meta( $user->ID, self::ENABLED_META_KEY, true );
		if ( ! $is_2fa_active ) {
			return;
		}

		// Desloga temporariamente a sessão até que o código 2FA seja validado
		wp_destroy_current_session();
		wp_clear_auth_cookie();

		$token = wp_create_nonce( 'siteshield_2fa_pending_' . $user->ID );
		set_transient( 'siteshield_2fa_pending_' . $token, $user->ID, 300 ); // 5 minutos

		wp_safe_redirect( wp_login_url() . '?action=siteshield_2fa&token=' . $token );
		exit;
	}

	/**
	 * Renderiza a tela de solicitação de código 2FA
	 */
	public static function render_2fa_login_prompt() {
		$token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
		$user_id = get_transient( 'siteshield_2fa_pending_' . $token );

		if ( ! $user_id ) {
			wp_safe_redirect( wp_login_url() );
			exit;
		}

		login_header( __( 'Verificação em Duas Etapas (2FA)', 'siteshield' ) );
		?>
		<form name="siteshield_2fa_form" id="siteshield_2fa_form" action="<?php echo esc_url( wp_login_url() . '?action=siteshield_verify_2fa' ); ?>" method="post">
			<p style="margin-bottom: 20px; font-size: 14px; line-height: 1.5; color: #475569;">
				🛡️ <strong>SiteShield 2FA:</strong> Abra seu aplicativo autenticador (Google Authenticator, Authy) e digite o código de 6 dígitos ou um código de recuperação.
			</p>
			<p>
				<label for="siteshield_2fa_code"><?php esc_html_e( 'Código de Verificação:', 'siteshield' ); ?></label>
				<input type="text" name="siteshield_2fa_code" id="siteshield_2fa_code" class="input" value="" size="20" placeholder="Ex: 123456" autocomplete="one-time-code" autofocus required />
			</p>
			<input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>" />
			<p class="submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e( 'Verificar e Entrar', 'siteshield' ); ?>" style="width: 100%; background: #0066FF; border-color: #0066FF;" />
			</p>
		</form>
		<?php
		login_footer();
		exit;
	}

	/**
	 * Valida a submissão do código 2FA no login
	 */
	public static function verify_2fa_login_submission() {
		$token   = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
		$code    = isset( $_POST['siteshield_2fa_code'] ) ? sanitize_text_field( wp_unslash( $_POST['siteshield_2fa_code'] ) ) : '';
		$user_id = get_transient( 'siteshield_2fa_pending_' . $token );

		if ( ! $user_id ) {
			wp_safe_redirect( wp_login_url() );
			exit;
		}

		$secret = get_user_meta( $user_id, self::SECRET_META_KEY, true );

		// Tenta validar como código TOTP de 6 dígitos ou como código de backup
		$is_valid_totp   = self::verify_code( $secret, $code );
		$is_valid_backup = ! $is_valid_totp && self::verify_and_consume_backup_code( $user_id, $code );

		if ( $is_valid_totp || $is_valid_backup ) {
			delete_transient( 'siteshield_2fa_pending_' . $token );
			wp_set_auth_cookie( $user_id, true );
			wp_safe_redirect( admin_url() );
			exit;
		}

		wp_safe_redirect( wp_login_url() . '?action=siteshield_2fa&token=' . $token . '&error=invalid_code' );
		exit;
	}

	/**
	 * Decodifica Base32 para binário
	 */
	private static function base32_decode( $b32 ) {
		$b32       = strtoupper( $b32 );
		$chars     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$buffer    = 0;
		$length    = 0;
		$binary    = '';

		for ( $i = 0; $i < strlen( $b32 ); $i++ ) {
			$pos = strpos( $chars, $b32[ $i ] );
			if ( false === $pos ) {
				continue;
			}
			$buffer = ( $buffer << 5 ) | $pos;
			$length += 5;
			if ( $length >= 8 ) {
				$length -= 8;
				$binary .= chr( ( $buffer >> $length ) & 0xFF );
			}
		}

		return $binary;
	}
}
