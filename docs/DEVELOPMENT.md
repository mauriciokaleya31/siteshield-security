# 💻 Guia de Desenvolvimento, API e WP-CLI — SiteShield

## Estrutura Modular
```text
includes/
├── Core/            # Database, Settings, Security Score, Emergency Mode
├── Firewall/        # WAF Engine, Rate Limiter, IP Manager
├── Login/           # Login Cloaking, 2FA TOTP (RFC 6238), Sessions
├── Scanner/         # Malware Heuristics, Quarantine, Cron Scheduler
├── Integrity/       # SHA-256 File Baseline & Change Tracking
├── AntiBot/         # Anti-Scraping & Good Bot Allowlist
├── AntiClone/       # Content Deterrents & Anti-Hotlink
├── Headers/         # HSTS, CSP, X-Frame, CORS & Permissions
├── Vulnerabilities/ # Core, Plugins, Themes & PHP Audit
├── Audit/           # Structured Activity Logger
├── Notifications/   # Email & Webhook Dispatcher
├── Recovery/        # JSON Settings Backup & Restore
├── API/             # Authenticated REST API
└── CLI/             # WP-CLI Commands
```

---

## 📡 REST API Interna
Todos os endpoints exigem autenticação de administrador com cookie de sessão ou Application Passwords:

- **`GET /wp-json/siteshield/v1/status`**: Retorna Security Score, diagnóstico e status dos módulos.
- **`POST /wp-json/siteshield/v1/scan`**: Dispara varredura heurística (`type=quick` ou `type=full`).
- **`GET /wp-json/siteshield/v1/ips`**: Retorna listagem de IPs bloqueados e permitidos.

---

## ⌨️ Comandos WP-CLI
```bash
# Exibe pontuação de segurança e status do sistema
wp siteshield status

# Executa varredura rápida de malware
wp siteshield scan

# Executa varredura profunda em todo o WordPress
wp siteshield scan --full

# Verifica integridade de arquivos
wp siteshield integrity

# Ativa ou desativa o Modo de Emergência
wp siteshield emergency enable
wp siteshield emergency disable
```
