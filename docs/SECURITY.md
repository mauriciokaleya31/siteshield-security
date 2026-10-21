# 🛡️ Diretrizes de Segurança — SiteShield Security

## Princípios Arquiteturais

1. **Defense in Depth**: Múltiplas camadas de proteção trabalhando em conjunto (WAF + Login Security + Heurística + Headers + Anti-Bot).
2. **Least Privilege**: Todas as ações administrativas exigem capacidade `manage_options` e verificação de Nonces (`check_ajax_referer`).
3. **Safe Defaults**: O Firewall WAF inicia em modo `MONITOR` para permitir aprendizado e evitar bloqueios indevidos (falsos positivos).
4. **No Destructive Automation**: O scanner de malware nunca apaga arquivos automaticamente por padrão; ele isola em quarentena com backup `.bak` e exige confirmação do administrador.
5. **No Plaintext Secrets**: Códigos de recuperação do 2FA são criptografados com SHA-256 e o segredo TOTP é gerado com entropia criptográfica (`random_bytes`).
6. **Data Minimization & Auto-Cleanup**: Retenção de logs configurável (7 a 365 dias) com rotina de limpeza periódica de registros antigos.
