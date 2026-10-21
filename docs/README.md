# 🛡️ SiteShield Security — Enterprise Security Suite for WordPress

**Criador:** Maurício Kaleya ([@mauriciokaleya31](https://github.com/mauriciokaleya31))  
**Versão:** 2.4.0  

O **SiteShield Security** é uma suíte corporativa modular de segurança para WordPress projetada com princípios de **Defesa em Profundidade (Defense in Depth)**, **Privilégio Mínimo (Least Privilege)** e **Performance First**.

---

## 🚀 Módulos e Recursos

1. **Firewall de Aplicação (WAF)**:
   - Modos `OFF`, `MONITOR` (análise sem bloqueio) e `BLOCK` (bloqueio imediato 403).
   - Detecção em tempo real de SQL Injection (SQLi), Cross-Site Scripting (XSS), Path Traversal e LFI/RFI.
   - Bloqueio de User-Agents maliciosos e ferramentas de ataque (sqlmap, nikto, dirbuster, etc.).

2. **Segurança de Login & 2FA**:
   - Ocultação da tela de login (`wp-login.php`) com URL de cloaking.
   - Autenticação de Dois Fatores (2FA TOTP - RFC 6238) compatível com Google Authenticator, Authy e 1Password.
   - Códigos de recuperação de uso único criptografados com SHA-256.
   - Proteção contra ataques de força bruta com rate limiting e bloqueio temporário de IPs.
   - Bloqueio de enumeração de nomes de usuários (`?author=X` e REST API).

3. **Scanner Heurístico de Malware & Quarentena**:
   - Análise estática contextual em plugins, temas, uploads, wp-includes e wp-admin.
   - Detecção de Web Shells (c99, r57, b374k), comandos do sistema (`shell_exec`, `system`) e código ofuscado.
   - Isolamento seguro de arquivos em pasta de quarentena com backup `.bak` e restauração com 1 clique.

4. **File Integrity Monitor (SHA-256)**:
   - Criação de snapshot da linha de base de hashes dos arquivos críticos.
   - Detecção e auditoria de arquivos modificados, criados ou deletados.

5. **Proteção Anti-Clone & Anti-Scraping**:
   - Bloqueio de botão direito, atalhos de desenvolvedor (`F12`, `Ctrl+U`, `Ctrl+Shift+I`), seleção de texto e arrasto de fotos.
   - Proteção Anti-Frame (Frame-Busting) e Anti-Hotlink para imagens com lista branca de domínios.

6. **Cabeçalhos HTTP de Segurança**:
   - HSTS com preload, Content-Security-Policy (CSP), X-Frame-Options, X-Content-Type-Options e Referrer-Policy.

7. **Security Center & Diagnóstico**:
   - Security Score inteligente de 0 a 100% com justificativas detalhadas.
   - 6 cartões métricos e registro cronológico de auditoria.
   - Assistente Onboarding em 6 passos para configuração guiada.
   - Modo de Emergência com 1 clique e proteção contra lockout.

8. **Extensibilidade**:
   - REST API autenticada (`/wp-json/siteshield/v1/...`).
   - Comandos WP-CLI (`wp siteshield status`, `scan`, `firewall`, `emergency`).
