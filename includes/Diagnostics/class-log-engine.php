<?php
/**
 * Motor Estruturado de Logs e Gestão de Auditoria Interna do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Diagnostics
 */

namespace SiteShield\Diagnostics;

use SiteShield\Core\Settings;
use SiteShield\Core\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Log_Engine
 */
class Log_Engine {

	/**
	 * Identificador único da requisição atual (Request ID)
	 *
	 * @var string|null
	 */
	private static $request_id = null;

	/**
	 * Categorias válidas de logs do SiteShield
	 */
	const CATEGORIES = array(
		'system'    => 'Sistema & Inicialização',
		'security'  => 'Segurança Geral',
		'waf'       => 'Firewall de Aplicação (WAF)',
		'malware'   => 'Scanner de Malware',
		'login'     => 'Segurança de Login & 2FA',
		'database'  => 'Banco de Dados',
		'cloud'     => 'SiteShield Cloud & Licença',
		'license'   => 'Licenciamento',
		'analytics' => 'Tráfego & Visitantes',
		'errors'    => 'Erros & Exceções',
	);

	/**
	 * Retorna ou gera o Request ID único da execução atual
	 *
	 * @return string
	 */
	public static function get_request_id() {
		if ( null === self::$request_id ) {
			$date_prefix       = gmdate( 'Ymd' );
			$random_suffix     = strtoupper( substr( md5( uniqid( (string) mt_rand(), true ) ), 0, 6 ) );
			self::$request_id = 'SS-' . $date_prefix . '-' . $random_suffix;
		}
		return self::$request_id;
	}

	/**
	 * Retorna o diretório seguro de armazenamento dos logs do SiteShield
	 *
	 * @return string
	 */
	public static function get_log_dir() {
		$upload_dir = wp_upload_dir();
		$log_dir    = $upload_dir['basedir'] . '/siteshield/logs';

		if ( ! is_dir( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
			// Bloqueia qualquer acesso web direto ações arquivos de log
			@file_put_contents( $log_dir . '/.htaccess', "Order Deny,Allow\nDeny from all\n<FilesMatch \".*\">\nOrder Deny,Allow\nDeny from all\n</FilesMatch>\n" );
			@file_put_contents( $log_dir . '/index.php', "<?php\n// Silêncio é ouro.\nexit;\n" );
		}

		return $log_dir;
	}

	/**
	 * Grava uma entrada estruturada no log da categoria especificada
	 *
	 * @param string      $category Categoria do log (system, security, waf, malware, login, database, cloud, license, analytics, errors).
	 * @param string      $event Nome do evento ou ação.
	 * @param string      $message Mensagem explicativa.
	 * @param string      $severity Gravidade (INFO, WARNING, ERROR, CRITICAL).
	 * @param array|mixed $context Dados adicionais de contexto.
	 * @return bool
	 */
	public static function log( $category, $event, $message, $severity = 'INFO', $context = array() ) {
		try {
			$allowed_categories = array_keys( self::CATEGORIES );
			if ( ! in_array( $category, $allowed_categories, true ) ) {
				$category = 'system';
			}

			$log_dir   = self::get_log_dir();
			$file_path = $log_dir . '/' . $category . '.log';

			$user_id    = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
			$client_ip  = Compatibility::get_client_ip();
			$request_id = self::get_request_id();

			// Mascara dados sensíveis antes da persistência
			$safe_message = self::mask_sensitive_data( $message );
			$safe_context = self::mask_sensitive_data( $context );

			$entry = array(
				'timestamp'  => gmdate( 'Y-m-d H:i:s' ),
				'request_id' => $request_id,
				'severity'   => strtoupper( $severity ),
				'event'      => sanitize_text_field( $event ),
				'message'    => $safe_message,
				'ip'         => $client_ip,
				'user_id'    => $user_id,
				'context'    => $safe_context,
			);

			$line = wp_json_encode( $entry ) . "\n";

			return (bool) @file_put_contents( $file_path, $line, FILE_APPEND | LOCK_EX );
		} catch ( \Throwable $e ) {
			return false;
		}
	}

	/**
	 * Lê os registros de log de uma categoria ou de todas com paginação e filtros
	 *
	 * @param string $category Categoria específica ou 'all'.
	 * @param int    $limit Quantidade de linhas.
	 * @param string $severity_filter Filtro de severidade (ALL, CRITICAL, ERROR, WARNING, INFO).
	 * @param string $search Termo de pesquisa.
	 * @return array
	 */
	public static function get_logs( $category = 'all', $limit = 100, $severity_filter = 'ALL', $search = '' ) {
		$log_dir = self::get_log_dir();
		$files   = array();

		if ( 'all' === $category ) {
			foreach ( array_keys( self::CATEGORIES ) as $cat ) {
				$p = $log_dir . '/' . $cat . '.log';
				if ( file_exists( $p ) ) {
					$files[ $cat ] = $p;
				}
			}
		} else {
			$p = $log_dir . '/' . $category . '.log';
			if ( file_exists( $p ) ) {
				$files[ $category ] = $p;
			}
		}

		$entries = array();

		foreach ( $files as $cat => $file_path ) {
			$lines = self::read_file_tail_lines( $file_path, 200 );
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if ( empty( $line ) ) {
					continue;
				}
				$data = json_decode( $line, true );
				if ( ! is_array( $data ) ) {
					continue;
				}

				$data['category']       = $cat;
				$data['category_label'] = isset( self::CATEGORIES[ $cat ] ) ? self::CATEGORIES[ $cat ] : ucfirst( $cat );

				// Filtro de severidade
				if ( 'ALL' !== $severity_filter && strtoupper( $data['severity'] ) !== strtoupper( $severity_filter ) ) {
					continue;
				}

				// Filtro de busca textual
				if ( ! empty( $search ) ) {
					$search_lc = strtolower( $search );
					$haystack  = strtolower( ( isset( $data['message'] ) ? $data['message'] : '' ) . ' ' . ( isset( $data['event'] ) ? $data['event'] : '' ) . ' ' . ( isset( $data['request_id'] ) ? $data['request_id'] : '' ) );
					if ( false === strpos( $haystack, $search_lc ) ) {
						continue;
					}
				}

				$entries[] = $data;
			}
		}

		// Ordena os registros do mais recente para o mais antigo
		usort( $entries, function( $a, $b ) {
			return strcmp( isset( $b['timestamp'] ) ? $b['timestamp'] : '', isset( $a['timestamp'] ) ? $a['timestamp'] : '' );
		} );

		return array_slice( $entries, 0, $limit );
	}

	/**
	 * Lê eficientemente as últimas N linhas de um arquivo sem carregar o arquivo todo na memória
	 *
	 * @param string $filepath Caminho do arquivo.
	 * @param int    $lines_count Quantidade de linhas.
	 * @return array
	 */
	public static function read_file_tail_lines( $filepath, $lines_count = 200 ) {
		if ( ! file_exists( $filepath ) || ! is_readable( $filepath ) ) {
			return array();
		}

		$handle = @fopen( $filepath, 'r' );
		if ( ! $handle ) {
			return array();
		}

		$line_buffer = array();
		$chunk_size  = 4096;
		$pos         = -1;
		$leftover    = '';

		fseek( $handle, 0, SEEK_END );
		$file_size = ftell( $handle );

		if ( 0 === $file_size ) {
			fclose( $handle );
			return array();
		}

		$bytes_read = 0;

		while ( count( $line_buffer ) < $lines_count && $bytes_read < $file_size ) {
			$read_size = min( $chunk_size, $file_size - $bytes_read );
			$bytes_read += $read_size;
			fseek( $handle, $file_size - $bytes_read );
			$chunk = fread( $handle, $read_size );

			$chunk_lines = explode( "\n", $chunk . $leftover );
			$leftover    = array_shift( $chunk_lines );

			for ( $i = count( $chunk_lines ) - 1; $i >= 0; $i-- ) {
				$trimmed = trim( $chunk_lines[ $i ] );
				if ( ! empty( $trimmed ) ) {
					$line_buffer[] = $trimmed;
					if ( count( $line_buffer ) >= $lines_count ) {
						break 2;
					}
				}
			}
		}

		if ( ! empty( $leftover ) && count( $line_buffer ) < $lines_count ) {
			$line_buffer[] = trim( $leftover );
		}

		fclose( $handle );

		return $line_buffer;
	}

	/**
	 * Limpa os arquivos de log do SiteShield
	 *
	 * @param string $category Categoria específica ou 'all'.
	 * @return bool
	 */
	public static function clear_logs( $category = 'all' ) {
		$log_dir = self::get_log_dir();

		if ( 'all' === $category ) {
			foreach ( array_keys( self::CATEGORIES ) as $cat ) {
				$p = $log_dir . '/' . $cat . '.log';
				if ( file_exists( $p ) ) {
					@unlink( $p );
				}
			}
			return true;
		}

		$p = $log_dir . '/' . $category . '.log';
		if ( file_exists( $p ) ) {
			return @unlink( $p );
		}

		return true;
	}

	/**
	 * Aplica política de retenção de logs conforme configurado
	 *
	 * @return int Total de bytes limpos.
	 */
	public static function enforce_retention() {
		$days    = intval( Settings::get( 'log_retention_days', 30 ) );
		$cutoff  = time() - ( $days * 86400 );
		$log_dir = self::get_log_dir();
		$cleared = 0;

		foreach ( array_keys( self::CATEGORIES ) as $cat ) {
			$file = $log_dir . '/' . $cat . '.log';
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$lines      = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
			$kept_lines = array();

			foreach ( $lines as $line ) {
				$data = json_decode( $line, true );
				if ( is_array( $data ) && isset( $data['timestamp'] ) ) {
					$time = strtotime( $data['timestamp'] );
					if ( $time >= $cutoff ) {
						$kept_lines[] = $line;
					} else {
						$cleared++;
					}
				} else {
					$kept_lines[] = $line;
				}
			}

			@file_put_contents( $file, implode( "\n", $kept_lines ) . ( ! empty( $kept_lines ) ? "\n" : '' ), LOCK_EX );
		}

		return $cleared;
	}

	/**
	 * Mascara informações confidenciais para garantir conformidade e privacidade
	 *
	 * @param mixed $data Conteúdo textual ou array a ser anonimizado.
	 * @return mixed
	 */
	public static function mask_sensitive_data( $data ) {
		if ( is_array( $data ) ) {
			$sanitized = array();
			foreach ( $data as $k => $v ) {
				$key_lc = strtolower( (string) $k );
				if ( in_array( $key_lc, array( 'password', 'pass', 'pwd', 'secret', 'token', 'authorization', 'api_key', 'license_key', 'credit_card', 'cc_num' ), true ) ) {
					$sanitized[ $k ] = '••••••••';
				} else {
					$sanitized[ $k ] = self::mask_sensitive_data( $v );
				}
			}
			return $sanitized;
		}

		if ( ! is_string( $data ) ) {
			return $data;
		}

		// Mascara padrões conhecidos de chaves de API e senhas
		$patterns = array(
			'/((?:sk_live|sk_test|key|token|bearer|secret)[a-zA-Z0-9_\-]{6})([a-zA-Z0-9_\-]{8,})/i' => '$1••••••••',
			'/([a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+)/i'                                    => '$1',
			'/(SS-[A-Z0-9]{4}-)[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}/i'                                => '$1••••-••••-••••',
		);

		return preg_replace( array_keys( $patterns ), array_values( $patterns ), $data );
	}
}
