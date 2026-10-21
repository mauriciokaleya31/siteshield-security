# 🛡️ SiteShield Security & Anti-Clone — Enterprise Suite for WordPress (Edição Demo Gratuita — 1.ª Versão)

[![Version](https://img.shields.io/badge/version-2.4.0%20(1.%C2%AA%20Vers%C3%A3o%20Demo)-blue.svg)](https://github.com/mauriciokaleya31/siteshield-security/releases)
[![Edition](https://img.shields.io/badge/edi%C3%A7%C3%A3o-100%25%20Gratuita%20%26%20Desbloqueada-10B981.svg)](https://github.com/mauriciokaleya31/siteshield-security)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B%20%7C%206.6%2B-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B%20%7C%208.x-777bb4.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-GPLv2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![Author](https://img.shields.io/badge/criador-Maur%C3%ADcio%20Kaleya-orange.svg)](https://github.com/mauriciokaleya31)

O **SiteShield Security & Anti-Clone (Enterprise Suite)** é um plugin completo e corporativo de cibersegurança, blindagem (*hardening*), auditoria e proteção anti-clonagem para **WordPress**, idealizado e desenvolvido por **Maurício Kaleya** ([@mauriciokaleya31](https://github.com/mauriciokaleya31)).

> ### 🎉 Nota Importante — Modelo Demo (Primeira Versão 100% Gratuita)
> Esta é a **Primeira Versão Pública (Modelo de Demonstração / 1.ª Versão)** disponibilizada gratuitamente para a comunidade!
> - **100% dos recursos desbloqueados de fábrica:** Diferente de versões com bloqueio de licença, nesta edição **todas as funcionalidades Enterprise & PRO estão totalmente liberadas de forma gratuita**, sem necessidade de chave de licença paga.
> - Inclui **Firewall WAF com 4 Níveis**, **2FA TOTP**, **Scanner Heurístico Profundo & Quarentena**, **Monitor de Integridade SHA-256**, **Anti-Clone**, **Relatórios Executivos em PDF** e o **Selo Oficial SiteShield Verified** prontos para usar!

Projetado com os princípios de **Defesa em Profundidade (*Defense in Depth*)**, **Privilégio Mínimo (*Least Privilege*)** e **Alta Performance (*Performance First*)**, o SiteShield protege sites WordPress, lojas WooCommerce e portais empresariais contra ataques modernos sem comprometer a velocidade do site.

---

## 🎯 Para Que Serve o SiteShield Security?

O SiteShield atua como um centro unificado de operações de segurança (*Security Operations Center - SOC*) dentro do seu WordPress. Ele resolve os principais problemas de segurança da plataforma:

- **Bloqueia ataques em tempo real** antes que cheguem ao banco de dados ou ao núcleo do WordPress (SQL Injection, XSS, LFI/RFI, Path Traversal e bots maliciosos).
- **Impede invasões de conta e ataques de força bruta** ocultando a página de login padrão (`wp-login.php`), exigindo Autenticação de Dois Fatores (2FA TOTP) e limitando tentativas por IP.
- **Detecta e isola malwares e backdoors** em temas, plugins e pastas de upload através de um motor heurístico com quarentena segura e reversível.
- **Monitora a integridade dos arquivos (SHA-256)** para alertar imediatamente caso qualquer arquivo crítico do WordPress seja modificado sem autorização.
- **Protege a propriedade intelectual e o conteúdo (Anti-Clone)** contra cópia de texto, inspeção de código, *scraping* automatizado, *hotlinking* de imagens e clonagem via `iframe`.
- **Diagnostica erros e saúde do servidor** analisando logs do PHP/WordPress com base de conhecimento integrada e gerando relatórios executivos em **PDF, CSV e JSON**.

---

## ✨ Funcionalidades e Módulos Principais

### 1. 🔥 Firewall de Aplicação Web (WAF Inteligente)
- **Modos de Operação:** `OFF` (Desativado), `MONITOR` (Registra ameaças sem bloquear — ideal para período de aprendizado) e `BLOCK` (Bloqueio ativo imediato com HTTP 403).
- **4 Níveis de Sensibilidade:** Proteção ajustável desde blogs simples até e-commerces críticos.
- **Inspeção Profunda de Payload:** Filtro em tempo real contra *SQL Injection (SQLi)*, *Cross-Site Scripting (XSS)*, *Local/Remote File Inclusion (LFI/RFI)* e *Path Traversal*.
- **Gestor de IPs e Rate Limiting:** Lista de bloqueio (*Blacklist*) e permissão (*Whitelist*) manual ou automática, além de proteção contra *scanners* (`sqlmap`, `nikto`, `dirbuster`, `wpscan`).

### 2. 🔐 Segurança de Login, 2FA & Sessões Ativas
- **Login Cloaking (Ocultação de Login):** Altera e protege a URL de acesso (`wp-login.php`), bloqueando bots automatizados.
- **Autenticação de Dois Fatores (2FA TOTP - RFC 6238):** Compatível com *Google Authenticator*, *Authy*, *Microsoft Authenticator* e *1Password*.
- **Códigos de Recuperação Criptografados:** Geração de códigos de uso único armazenados com hash `SHA-256`.
- **Proteção contra Força Bruta:** Bloqueio progressivo de IPs após tentativas falhas de autenticação.
- **Gestão de Sessões Administrativas:** Visualize quem está logado em tempo real e encerre sessões suspeitas remotamente com 1 clique.

### 3. 🦠 Scanner Heurístico de Malware & Quarentena Segura
- **Varredura Rápida e Completa:** Inspeção estática e contextual em `wp-admin`, `wp-includes`, `wp-content/plugins`, `wp-content/themes` e `wp-content/uploads`.
- **Detecção de Web Shells e Ofuscação:** Identifica assinaturas de `c99`, `r57`, `b374k`, execução remota (`eval`, `base64_decode`, `shell_exec`, `passthru`, `system`) e arquivos PHP ocultos na pasta de uploads.
- **Quarentena Segura com Backup:** Nunca apaga arquivos de forma destrutiva sem confirmação — isola o arquivo suspeito com backup `.bak` e permite restaurar ou excluir definitivamente com 1 clique.

### 4. 🧬 Monitor de Integridade de Arquivos (SHA-256)
- Cria uma linha de base (*baseline snapshot*) criptográfica `SHA-256` dos arquivos do sistema.
- Detecta e audita qualquer arquivo **modificado**, **adicionado** ou **removido** no servidor.

### 5. 🛡️ WordPress Hardening (Blindagem do Sistema)
- Desativação do `XML-RPC` e proteção contra ataques de amplificação *pingback*.
- Bloqueio de enumeração de usuários (`/?author=1` e endpoints públicos da REST API).
- Desativação do editor de arquivos interno do painel (`DISALLOW_FILE_EDIT`).
- Ocultação da versão do WordPress e remoção de metadados sensíveis do cabeçalho HTML.

### 6. 🚫 Proteção de Conteúdo, Anti-Clone & Anti-Scraping
- Bloqueio opcional de clique direito (context menu), seleção de texto, arrasto de imagens e atalhos de inspeção (`F12`, `Ctrl+U`, `Ctrl+Shift+I`, `Ctrl+S`).
- **Anti-Frame / Frame-Busting:** Impede que seu site seja embutido em `iframes` de terceiros (proteção contra *Clickjacking* e clonagem visual).
- **Anti-Hotlink:** Protege imagens e mídias contra uso indevido de banda por outros domínios.
- **Anti-Bot Engine:** Filtra *scrapers* preservando robôs legítimos de busca (Googlebot, Bingbot, etc.).

### 7. 🌐 Cabeçalhos HTTP de Segurança (Security Headers & CSP)
- Configuração nativa de `Strict-Transport-Security (HSTS)`, `Content-Security-Policy (CSP)`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` e `Permissions-Policy`.

### 8. 📊 Diagnóstico Avançado, Visitantes, Relatórios & Selo Verificado
- **Security Score (0 a 100%):** Pontuação dinâmica da postura de segurança do site com recomendações práticas.
- **System Health & Debug Log Parser:** Leitor inteligente do `debug.log` com base de conhecimento que explica a causa de erros PHP/WordPress e como corrigi-los.
- **Autoteste de Proteções:** Simulador interno para testar se as camadas do WAF e cabeçalhos estão operacionais.
- **Estatísticas Reais de Visitantes:** Monitoramento de tráfego humano vs. bots e origem das requisições.
- **Relatórios Executivos:** Exportação de auditorias em **PDF**, **CSV** e **JSON**.
- **Selo SiteShield Verified:** Selo visual configurável (shortcode/flutuante) para exibir a certificação de segurança no rodapé ou páginas do site.
- **Suporte Multilíngue Nativo:** Português (`pt_PT` / `pt_BR`), Inglês (`en_US`), Espanhol (`es_ES`) e Francês (`fr_FR`).

---

## ⚙️ Requisitos do Sistema

| Requisito | Mínimo | Recomendado |
| :--- | :--- | :--- |
| **WordPress** | `5.8` ou superior | `6.4+` / `6.6+` |
| **PHP** | `7.4` ou superior | `8.0`, `8.1`, `8.2` ou `8.3` |
| **Banco de Dados** | MySQL `5.7+` ou MariaDB `10.3+` | MySQL `8.0+` ou MariaDB `10.6+` |
| **Extensões PHP** | `openssl`, `hash`, `json`, `mbstring` | + `curl`, `zip` |

---

## 🚀 Instruções de Instalação

Você pode instalar o **SiteShield Security** de 3 formas diferentes:

### Método 1: Instalação via Painel do WordPress (Arquivo `.ZIP`) — *Recomendado*
1. Acesse a aba **[Releases](https://github.com/mauriciokaleya31/siteshield-security/releases)** deste repositório (ou clique em **Code > Download ZIP**).
2. Baixe o pacote instalável `siteshield-security.zip`.
3. No painel administrativo do seu WordPress, acesse **Plugins > Adicionar Novo Plugin > Enviar Plugin**.
4. Selecione o arquivo `siteshield-security.zip` e clique em **Instalar Agora**.
5. Após a instalação, clique em **Ativar Plugin**.

### Método 2: Instalação via Git / Linha de Comando (Servidor / SSH)
Se você tem acesso SSH ao seu servidor WordPress, clone diretamente na pasta `wp-content/plugins/`:

```bash
cd /caminho/do/seu/wordpress/wp-content/plugins/
git clone https://github.com/mauriciokaleya31/siteshield-security.git
```

Em seguida, ative pelo painel do WordPress ou usando o **WP-CLI**:

```bash
wp plugin activate siteshield-security
```

### Método 3: Instalação via FTP / SFTP (cPanel / FileZilla)
1. Extraia o conteúdo do repositório em uma pasta chamada `siteshield-security`.
2. Envie a pasta `siteshield-security` para o diretório `/wp-content/plugins/` da sua hospedagem.
3. Acesse o painel do WordPress em **Plugins > Plugins Instalados** e clique em **Ativar** em *SiteShield Security & Anti-Clone — Enterprise Suite*.

---

## 🧭 Configuração Inicial (Assistente Onboarding em 6 Passos)

Assim que o plugin é ativado pela primeira vez, você será guiado pelo **Assistente Onboarding** (`SiteShield > Assistente Onboarding`):

1. **Passo 1 — Proteções Essenciais:** Ative o Hardening básico do WordPress e a proteção contra enumeração de usuários.
2. **Passo 2 — Segurança de Login:** Defina o limite de tentativas contra força bruta e, se desejar, configure a URL personalizada de login.
3. **Passo 3 — Modo do Firewall (WAF):** Escolha entre o modo `MONITOR` (recomendado nos primeiros dias para auditar o tráfego sem bloqueios acidentais) ou `BLOCK` (proteção ativa imediata).
4. **Passo 4 — Varredura Inicial:** Gere o snapshot `SHA-256` de integridade de arquivos e execute o primeiro scan heurístico.
5. **Passo 5 — Alertas e Notificações:** Defina o e-mail administrativo (e Webhook opcional) para receber alertas críticos de segurança.
6. **Passo 6 — Conclusão & Security Score:** Visualize a pontuação de segurança do seu site e acesse o **Security Center**.

---

## 🆘 Modo de Emergência (Prevenção Contra Lockout)

O SiteShield foi construído com mecanismos anti-bloqueio acidental (*Anti-Lockout*). Caso você altere a URL de login, perca seu dispositivo 2FA ou bloqueie seu próprio IP acidentalmente, você pode recuperar o acesso imediatamente de duas maneiras:

### Opção A: Via `wp-config.php` (FTP / Gerenciador de Arquivos da Hospedagem)
Abra o arquivo `wp-config.php` na raiz do seu WordPress e adicione a seguinte linha antes de `/* That's all, stop editing! */`:

```php
define( 'SITESHIELD_DISABLE_ALL', true );
```

Isso pausará temporariamente todos os bloqueios do firewall e de login para que você entre no painel, ajuste suas configurações e depois remova a linha.

### Opção B: Via WP-CLI (Terminal SSH)
```bash
wp siteshield emergency enable
```
Para reativar as proteções normalmente:
```bash
wp siteshield emergency disable
```

---

## 💻 Comandos WP-CLI e REST API

### Comandos WP-CLI Disponíveis
```bash
# Exibe pontuação de segurança (Security Score) e status dos módulos
wp siteshield status

# Executa varredura heurística rápida de malware
wp siteshield scan

# Executa varredura profunda em todos os diretórios do WordPress
wp siteshield scan --full

# Verifica a integridade SHA-256 dos arquivos
wp siteshield integrity

# Gerencia o Modo de Emergência
wp siteshield emergency enable
wp siteshield emergency disable
```

### Endpoints da REST API (Autenticados)
Todos os endpoints exigem permissão de administrador (`manage_options`) via Cookie Nonce ou *Application Passwords*:
- `GET /wp-json/siteshield/v1/status` — Retorna Security Score, diagnóstico e status operacional.
- `POST /wp-json/siteshield/v1/scan` — Inicia uma varredura de malware (`type=quick` ou `type=full`).
- `GET /wp-json/siteshield/v1/ips` — Lista IPs bloqueados e na lista de permissões.

---

## 📂 Estrutura de Diretórios do Projeto

```text
siteshield-security/
├── siteshield.php               # Arquivo principal de bootstrap do plugin
├── uninstall.php                # Rotina limpa de desinstalação
├── README.md                    # Documentação principal do repositório
├── readme.txt                   # Manifesto padrão do diretório WordPress
├── admin/                       # Controlador administrativo, Setup Wizard e 22 Views
│   ├── class-admin-controller.php
│   ├── class-setup-wizard.php
│   └── views/
├── assets/                      # Folhas de estilo (CSS), scripts (JS) e ícones
├── docs/                        # Documentação técnica complementar
├── includes/                    # Arquitetura modular PSR-4 (SiteShield\*)
│   ├── Alerts/                  # Central de alertas
│   ├── AntiBot/                 # Motor anti-bot e filtro de scrapers
│   ├── AntiClone/               # Proteção de conteúdo e anti-hotlink
│   ├── API/                     # Endpoints REST API
│   ├── Audit/                   # Auditoria de eventos e logs
│   ├── Badge/                   # Selo SiteShield Verified
│   ├── CLI/                     # Comandos WP-CLI
│   ├── Cloud/                   # Cliente de sincronização e inteligência Cloud
│   ├── Core/                    # Database, Settings, Security Score, Emergency Mode
│   ├── Diagnostics/             # Parser de debug.log e base de conhecimento
│   ├── Events/                  # Central de eventos de segurança
│   ├── Export/                  # Exportador de dados (CSV/JSON)
│   ├── Firewall/                # WAF Engine, IP Manager, Rate Limiter, Exceções
│   ├── Hardening/               # Blindagem nativa do WordPress
│   ├── Headers/                 # Cabeçalhos HTTP e CSP
│   ├── Health/                  # System Health Center
│   ├── I18n/                    # Internacionalização (PT, EN, ES, FR)
│   ├── Integrity/               # Monitor de integridade SHA-256
│   ├── Login/                   # Login Security, 2FA TOTP e Sessões Ativas
│   ├── Notifications/           # Disparo de alertas por e-mail e webhook
│   ├── Performance/             # Monitor de performance do plugin
│   ├── Recovery/                # Backup e restauração de configurações
│   ├── Reports/                 # Gerador de relatórios executivos PDF
│   ├── Scanner/                 # Scanner heurístico, Quarentena e Agendador Cron
│   ├── Testing/                 # Autoteste de proteções
│   ├── Visitors/                # Rastreador e estatísticas de visitantes
│   └── Vulnerabilities/         # Monitor de vulnerabilidades de Core, Plugins e Temas
├── languages/                   # Arquivos de tradução (.po, .mo, .pot)
└── tests/                       # Suíte de testes automatizados e regressão
```

---

## 👨‍💻 Autor e Créditos

Desenvolvido e mantido por:

- **Criador:** **Maurício Kaleya**
- **GitHub:** [@mauriciokaleya31](https://github.com/mauriciokaleya31)
- **E-mail:** [kaleyapt@gmail.com](mailto:kaleyapt@gmail.com)

---

## 📄 Licença

Este projeto está licenciado sob a licença **GPLv2 ou posterior** (*GNU General Public License v2.0 or later*) — veja o cabeçalho do plugin ou acesse [gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html) para mais detalhes.
