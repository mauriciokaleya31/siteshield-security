=== SiteShield Security & Anti-Clone — Enterprise Suite ===
Contributors: mauriciokaleya31
Author: Maurício Kaleya
Author URI: https://github.com/mauriciokaleya31
Plugin URI: https://github.com/mauriciokaleya31/siteshield-security
Tags: security, firewall, waf, 2fa, malware-scanner, anti-clone, hardening, wordpress-security
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 2.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Suíte corporativa de segurança para WordPress criada por Maurício Kaleya: WAF Inteligente, 2FA TOTP, Scanner de Malware com Quarentena, Integridade SHA-256, Hardening e Anti-Clone.

== Description ==

O **SiteShield Security & Anti-Clone — Enterprise Suite** é uma solução completa de cibersegurança e proteção de conteúdo para WordPress, desenvolvida por **Maurício Kaleya** (@mauriciokaleya31).

= Principais Recursos =
* **Firewall WAF Inteligente**: Modos OFF, MONITOR e BLOCK com 4 níveis de sensibilidade contra SQLi, XSS, LFI/RFI e bots maliciosos.
* **Segurança de Login & 2FA TOTP**: Ocultação da URL de login, Autenticação de Dois Fatores (RFC 6238), códigos de recuperação SHA-256 e proteção contra força bruta.
* **Scanner Heurístico de Malware & Quarentena**: Varredura em temas, plugins e uploads com isolamento seguro e restauração em 1 clique.
* **Integridade de Arquivos (SHA-256)**: Monitoramento de alterações em arquivos críticos do WordPress.
* **WordPress Hardening**: Bloqueio de XML-RPC, enumeração de usuários e edição de arquivos.
* **Proteção Anti-Clone & Anti-Scraping**: Proteção contra cópia de conteúdo, clique direito, atalhos DevTools, iframes não autorizados e hotlinking.
* **Cabeçalhos HTTP & CSP**: HSTS, Content-Security-Policy, X-Frame-Options, X-Content-Type-Options e Referrer-Policy.
* **Diagnóstico, Visitantes & Relatórios PDF/CSV/JSON**: Leitor inteligente de debug.log, Security Score de 0 a 100%, estatísticas reais de visitantes e Selo SiteShield Verified.

== Installation ==

1. Faça o upload da pasta `siteshield-security` para o diretório `/wp-content/plugins/` ou envie o arquivo `siteshield-security.zip` em **Plugins > Adicionar Novo > Enviar Plugin**.
2. Ative o plugin no menu **Plugins** do WordPress.
3. Siga o **Assistente Onboarding em 6 Passos** para configurar o Firewall, Login, Scanner e Alertas.

== Changelog ==

= 2.4.0 =
* Versão Enterprise Suite completa por Maurício Kaleya (@mauriciokaleya31).
* WAF de 4 níveis, 2FA TOTP, Quarentena Segura, Integridade SHA-256, Anti-Clone, Relatórios PDF/CSV/JSON e suporte multilíngue (PT, EN, ES, FR).
