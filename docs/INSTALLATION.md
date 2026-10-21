# 📦 Guia de Instalação e Configuração — SiteShield Security

## Requisitos do Sistema
- **WordPress:** 5.8 ou superior (testado e compatível até 6.6+)
- **PHP:** 7.4 ou superior (Recomendado PHP 8.0, 8.1, 8.2 ou 8.3)
- **Extensões PHP:** `openssl`, `hash`, `json`, `mbstring`

---

## 🚀 Instalação Manual via Painel WordPress
1. Baixe o arquivo **`siteshield-security.zip`**.
2. Acesse seu painel do WordPress em **Plugins** > **Adicionar Novo Plugin** > **Enviar Plugin**.
3. Selecione o arquivo ZIP e clique em **Instalar Agora**.
4. Clique em **Ativar Plugin**.
5. Você será redirecionado para o **Assistente Onboarding em 6 Passos**:
   - **Passo 1:** Ative as proteções básicas de segurança.
   - **Passo 2:** Configure a URL e chave de login.
   - **Passo 3:** Escolha o modo do Firewall (recomendado: MONITOR).
   - **Passo 4:** Execute a varredura heurística inicial.
   - **Passo 5:** Configure seu e-mail para receber alertas.
   - **Passo 6:** Conclua e visualize seu **Security Score**.

---

## 🛡️ Prevenção Contra Lockout (Recuperação de Emergência)
Caso você perca o acesso ou seja bloqueado acidentalmente:
1. Abra o arquivo `wp-config.php` do seu WordPress na hospedagem.
2. Adicione antes da linha `/* That's all, stop editing! */`:
   ```php
   define( 'SITESHIELD_DISABLE_ALL', true );
   ```
3. Acesse o painel normalmente e ajuste suas configurações de login e IP.
