<?php
/**
 * Base de Conhecimento e Motor de Explicação Inteligente de Erros do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Diagnostics
 */

namespace SiteShield\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Error_Knowledge_Base
 */
class Error_Knowledge_Base {

	/**
	 * Analisa um erro e gera uma explicação contextualizada e recomendações de resolução
	 *
	 * @param string $type Tipo de erro (PHP Fatal error, Warning, TypeError, etc.).
	 * @param string $message Mensagem técnica original do erro.
	 * @param string $file Arquivo onde ocorreu.
	 * @param int    $line Linha do arquivo.
	 * @param string $origin Origem identificada (SiteShield, WordPress, Plugin, Tema).
	 * @return array
	 */
	public static function explain( $type, $message, $file = '', $line = 0, $origin = 'WordPress' ) {
		$msg_lower  = strtolower( $message );
		$type_upper = strtoupper( $type );

		// 1. Classe não encontrada (Class not found)
		if ( false !== strpos( $msg_lower, 'class' ) && ( false !== strpos( $msg_lower, 'not found' ) || false !== strpos( $msg_lower, 'cannot declare class' ) ) ) {
			preg_match( '/class\s+["\']?([^"\'\s]+)["\']?\s+not found/i', $message, $m );
			$target_class = ! empty( $m[1] ) ? $m[1] : 'Classe Não Identificada';

			return array(
				'category'         => 'Dependência & Carregamento',
				'title'            => __( 'Classe PHP Não Encontrada', 'siteshield' ),
				'explanation'      => sprintf( __( 'O WordPress tentou executar a classe "%s", mas ela não foi localizada na memória no momento da chamada.', 'siteshield' ), $target_class ),
				'probable_causes'  => array(
					__( 'Falha na incluso (require/include) do arquivo que define a classe.', 'siteshield' ),
					__( 'Erro de sintaxe ou interrupção no arquivo que antecede o carregamento desta classe.', 'siteshield' ),
					__( 'Incompatibilidade de namespace ou carregamento fora da ordem de ganchos (hooks) do WordPress.', 'siteshield' ),
					__( 'Ficheiro ausente ou corrompido durante o envio/descompactação do plugin.', 'siteshield' ),
				),
				'impact'           => __( 'A funcionalidade ou a página administrativa associada a este módulo não abrirá até que a classe seja carregada.', 'siteshield' ),
				'recommendation'   => __( 'Atualize o pacote do plugin para garantir que todos os módulos sejam carregados de forma isolada e protegida.', 'siteshield' ),
			);
		}

		// 2. Erro de Tipo / Objeto acessado como array (TypeError: Cannot use object of type stdClass as array)
		if ( false !== strpos( $msg_lower, 'cannot use object of type stdclass as array' ) || false !== strpos( $msg_lower, 'cannot use object of type' ) ) {
			return array(
				'category'         => 'Incompatibilidade de Tipos PHP 8.x',
				'title'            => __( 'Acesso Inválido a Objeto como Array', 'siteshield' ),
				'explanation'      => __( 'O código tentou ler uma propriedade de um objeto utilizando colchetes ($obj["chave"]) em vez de seta ($obj->chave). No PHP 8.0+, essa operação resulta em erro fatal.', 'siteshield' ),
				'probable_causes'  => array(
					__( 'Transiente ou resposta da API do WordPress retornou um objeto stdClass em vez de array associativo.', 'siteshield' ),
					__( 'Código desenvolvido para PHP 7.x que não foi adaptado para a tipagem estrita do PHP 8.x.', 'siteshield' ),
				),
				'impact'           => __( 'A rotina que consulta atualizações ou dados do sistema falha ação processar os dados recebidos.', 'siteshield' ),
				'recommendation'   => __( 'Utilizar verificações de tipo com is_object() e is_array() antes de acessar chaves dinâmicas.', 'siteshield' ),
			);
		}

		// 3. Chamada a função indefinida (Call to undefined function)
		if ( false !== strpos( $msg_lower, 'call to undefined function' ) ) {
			preg_match( '/call to undefined function\s+([a-zA-Z0-9_\\\\]+)\(\)/i', $message, $m );
			$func_name = ! empty( $m[1] ) ? $m[1] : 'função';

			return array(
				'category'         => 'Inicialização & Dependência',
				'title'            => __( 'Chamada a Função Indefinida', 'siteshield' ),
				'explanation'      => sprintf( __( 'A função "%s()" foi chamada antes de ter sido declarada ou em um gancho prematuro do ciclo de vida do WordPress.', 'siteshield' ), $func_name ),
				'probable_causes'  => array(
					__( 'Chamada a funções plugáveis (como wp_get_current_user ou get_current_user_id) antes do carregamento de pluggable.php.', 'siteshield' ),
					__( 'Módulo PHP ou extensão ausente no servidor (ex: cURL, OpenSSL, DNS).', 'siteshield' ),
					__( 'Função de outro plugin que não está ativo no momento.', 'siteshield' ),
				),
				'impact'           => __( 'Interrupção imediata da execução do script no ponto em que a função foi requisitada.', 'siteshield' ),
				'recommendation'   => __( 'Envolver a chamada com function_exists() ou adiar a execução para o gancho plugins_loaded / init.', 'siteshield' ),
			);
		}

		// 4. Erro de Sintaxe (Parse Error / Syntax Error)
		if ( false !== strpos( $type_upper, 'PARSE' ) || false !== strpos( $msg_lower, 'syntax error' ) ) {
			return array(
				'category'         => 'Sintaxe PHP',
				'title'            => __( 'Erro de Sintaxe no Código PHP', 'siteshield' ),
				'explanation'      => __( 'O interpretador PHP encontrou um caractere inesperado (como vírgula órfã, ponto e vírgula ausente ou aspas abertas) que impede a compilação do arquivo.', 'siteshield' ),
				'probable_causes'  => array(
					__( 'Vírgula sobrando no final de declarações em versões anteriores do PHP.', 'siteshield' ),
					__( 'Arquivo corrompido durante a transferência via FTP ou extração do ZIP.', 'siteshield' ),
					__( 'Uso de sintaxe moderna não suportada pela versão do PHP em execução.', 'siteshield' ),
				),
				'impact'           => __( 'O arquivo com erro de sintaxe não pode ser interpretado e bloqueia todo o carregamento do plugin associado.', 'siteshield' ),
				'recommendation'   => __( 'Substituir os arquivos do plugin pela versão auditada e reinstalar via painel.', 'siteshield' ),
			);
		}

		// 5. Limite de Memória Excedido (Allowed memory size exhausted)
		if ( false !== strpos( $msg_lower, 'allowed memory size' ) || false !== strpos( $msg_lower, 'memory exhausted' ) ) {
			return array(
				'category'         => 'Recursos do Servidor',
				'title'            => __( 'Limite de Memória PHP Excedido', 'siteshield' ),
				'explanation'      => __( 'O WordPress tentou alocar mais memória do que o limite permitido pela configuração (WP_MEMORY_LIMIT / memory_limit do PHP).', 'siteshield' ),
				'probable_causes'  => array(
					__( 'Limite de memória do PHP muito baixo (ex: 64MB ou 128MB).', 'siteshield' ),
					__( 'Leitura de arquivos muito grandes em uma única operação.', 'siteshield' ),
					__( 'Laço de repetição infinito em plugin ou tema de terceiros.', 'siteshield' ),
				),
				'impact'           => __( 'Páginas pesadas ou operações de varredura podem travar no meio da execução.', 'siteshield' ),
				'recommendation'   => __( 'Aumente o limite adicionando define("WP_MEMORY_LIMIT", "256M"); no wp-config.php.', 'siteshield' ),
			);
		}

		// 6. Tempo Limite Excedido (Maximum execution time exceeded)
		if ( false !== strpos( $msg_lower, 'maximum execution time' ) || false !== strpos( $msg_lower, 'timed out' ) ) {
			return array(
				'category'         => 'Tempo de Execução',
				'title'            => __( 'Tempo Limite de Execução Excedido (Timeout)', 'siteshield' ),
				'explanation'      => __( 'Um script PHP demorou mais tempo para ser concluído do que o tempo limite configurado no max_execution_time do servidor.', 'siteshield' ),
				'probable_causes'  => array(
					__( 'Varredura completa de muitos arquivos executada de forma síncrona.', 'siteshield' ),
					__( 'Conexão externa lenta ou bloqueada por firewall de rede do servidor.', 'siteshield' ),
					__( 'Consultas pesadas ação banco de dados com muitos registros.', 'siteshield' ),
				),
				'impact'           => __( 'A requisição é encerrada abruptamente sem finalizar o processamento.', 'siteshield' ),
				'recommendation'   => __( 'Dividir operações longas em lotes menores ou utilizar chamadas em segundo plano via WP-Cron.', 'siteshield' ),
			);
		}

		// 7. Erro de Permissão de Ficheiros (Permission denied / failed to open stream)
		if ( false !== strpos( $msg_lower, 'permission denied' ) || false !== strpos( $msg_lower, 'failed to open stream' ) ) {
			return array(
				'category'         => 'Permissões do Sistema de Arquivos',
				'title'            => __( 'Falha de Permissão em Arquivo ou Pasta', 'siteshield' ),
				'explanation'      => __( 'O servidor web tentou ler, criar ou modificar um arquivo ou pasta, mas o sistema operacional negou o acesso.', 'siteshield' ),
				'probable_causes'  => array(
					__( 'Pasta de uploads ou diretório do plugin com permissões muito restritas (diferentes de 755/644).', 'siteshield' ),
					__( 'Proprietário do arquivo (user/group) diferente do usuário sob o qual o servidor web (Nginx/Apache/PHP-FPM) opera.', 'siteshield' ),
				),
				'impact'           => __( 'Impossibilidade de gerar relatórios, quarentena de arquivos ou salvar configurações.', 'siteshield' ),
				'recommendation'   => __( 'Ajuste as permissões das pastas para 755 e dos arquivos para 644 via painel de hospedagem ou SSH.', 'siteshield' ),
			);
		}

		// 8. Erro no Banco de Dados (WordPress database error)
		if ( false !== strpos( $msg_lower, 'wordpress database error' ) || false !== strpos( $msg_lower, 'table' ) && false !== strpos( $msg_lower, 'doesn\'t exist' ) ) {
			return array(
				'category'         => 'Banco de Dados MySQL / MariaDB',
				'title'            => __( 'Erro na Tabela ou Consulta do Banco de Dados', 'siteshield' ),
				'explanation'      => __( 'O WordPress não conseguiu completar uma consulta SQL, ou uma tabela customizada não foi criada corretamente.', 'siteshield' ),
				'probable_causes'  => array(
					__( 'Tabela do plugin ausente após migração ou atualização sem passar pelo gancho de ativação.', 'siteshield' ),
					__( 'Banco de dados corrompido ou sem espaço em disco no servidor.', 'siteshield' ),
					__( 'Incompatibilidade no prefixo das tabelas do WordPress.', 'siteshield' ),
				),
				'impact'           => __( 'Registros de segurança, visitantes ou eventos podem não ser persistidos.', 'siteshield' ),
				'recommendation'   => __( 'Acesse as ferramentas do SiteShield e clique em "Reinstalar Tabelas" para recriar o esquema com dbDelta.', 'siteshield' ),
			);
		}

		// 9. Explicação Genérica Baseada em Fatos
		return array(
			'category'         => 'Geral',
			'title'            => sprintf( __( 'Aviso ou Evento de Sistema (%s)', 'siteshield' ), esc_html( $type ) ),
			'explanation'      => sprintf( __( 'O interpretador PHP registrou uma ocorrência no arquivo "%s" na linha %d.', 'siteshield' ), basename( $file ), $line ),
			'probable_causes'  => array(
				__( 'Execução de rotina em ambiente com modo de depuração ativado.', 'siteshield' ),
				__( 'Aviso de compatibilidade ou depreciação de biblioteca.', 'siteshield' ),
			),
			'impact'           => __( 'Dependendo da gravidade, pode ser apenas um alerta informativo ou exigir ajauste nas configurações.', 'siteshield' ),
			'recommendation'   => __( 'Consulte os detalhes técnicos clicando em "Ver detalhes técnicos" para inspecionar a pilha de execução.', 'siteshield' ),
		);
	}
}
