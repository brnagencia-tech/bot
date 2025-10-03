# BRN Pixel — Backend (MVP)

Este repositório está sendo evoluído de um CRM legado para o backend do **BRN Pixel**, plataforma multi-tenant de tracking com pixel próprio, Advanced Matching e integrações CAPI. A codebase ainda mantém algumas telas legadas (Kanban/Leads), porém o foco atual é a nova API e o modelo de dados multi-tenant descritos abaixo.

## Funcionalidades (estado atual)
- API de autenticação multi-tenant (`/api/auth/*`) com criação de tenant própria.
- Modelo de dados alinhado ao BRN Pixel (roles, tenants, pixels, eventos, consents, webhooks, etc.).
- Telas legadas (Kanban/Leads) permanecem apenas como referência temporária.
- Reset de senha e páginas públicas originais ainda disponíveis, mas serão substituídas.
- Dashboard web (Tailwind) com visão de métricas básicas, eventos recentes e logs.
- Consent manager inicial (registro via pixel SDK, listagem e revogação via API).
- Webhooks por tenant com fila simples (pending/delivered) e rotação de segredo.

## Estrutura do Projeto
- `/public` — Front controller e arquivos públicos
- `/app/Controllers` — Lógica dos controladores
- `/app/Models` — Modelos de dados
- `/app/Views` — Templates de visualização
- `/app/Core` — Núcleo (Router, View, Auth, CSRF, DB)
- `/app/Services` — Serviços auxiliares (ex: EmailService)
- `/app/Controllers/Api` — Controladores HTTP da nova API

## Instalação e Deploy (exemplo DigitalOcean)
1. Clone o repositório na sua VM (ex: Ubuntu)
2. Instale PHP 8.1+, MySQL/MariaDB, e extensões PDO
3. Copie `.env.example` para `.env` e ajuste as variáveis (DB, APP_URL, MAIL_*)
4. Importe o schema SQL (inclui seeds básicos para BRN Pixel):
   ```sh
   mysql -u seu_usuario -p crm_db < database.sql
   ```
   - O script cria todas as tabelas multi-tenant, chaves estrangeiras e registros iniciais (roles `master|admin|user`, tenant demo e usuário `master@brnpixel.test`).
   - Em execuções repetidas o script utiliza `ON DUPLICATE KEY` para atualizar dados sem duplicar registros.
   - Caso precise apenas aplicar alterações incrementais, migre estes trechos para migrations dedicadas.
   - Tokens de pixel devem ser persistidos usando `SHA-256` (`token_enc = hash('sha256', token_raw)`), alinhados ao que o endpoint de ingestão espera.
5. Configure o servidor web (Nginx/Apache) para apontar o root para `/public`
   - Exemplo Nginx:
	 ```nginx
	 server {
		 server_name seu_dominio.com;
		 root /caminho/para/projeto/public;
		 index index.php;
		 location / {
			 try_files $uri $uri/ /index.php?$query_string;
		 }
		 location ~ \.php$ {
			 fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
			 fastcgi_index index.php;
			 include fastcgi_params;
			 fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		 }
	 }
	 ```
6. Permissões: garanta que o usuário do webserver possa gravar em `reset_links.log` (para reset de senha em dev)
7. Acesse `APP_URL` no navegador

## Segurança
- Senhas com `password_hash`
- CSRF em todos os formulários
- Rate-limit no login e reset
- SQL seguro (PDO)
- XSS: todas as saídas escapadas
- Cookies de sessão: SameSite/Lax, httponly

## Variáveis .env
Veja `.env.example` para detalhes de conexão e email.

## Reset de Senha
- Em ambiente de desenvolvimento, o link de reset é salvo em `reset_links.log` na raiz do projeto.
- Em produção, configure um serviço de email SMTP real.

## Configuração do Banco de Dados
Veja o arquivo `.env.example` para detalhes de conexão.

### Dica: migrando para migrations passo a passo
Se desejar controlar versões incrementais (ex.: usar Laravel 11), converta as definições do `database.sql` em migrations individuais seguindo esta ordem:
1. Criar as tabelas base (`roles`, `tenants`, `users`, `user_tenant`).
2. Adicionar integrações e governança (`ad_accounts`, `pixels`, `pixel_tokens`, `event_schemas`).
3. Implementar ingestão/eventos (`events`), consentimento (`consents`) e destinos (`webhooks`, `deliveries`).
4. Finalizar com auxiliares (`oauth_states`, `audit_logs`) e seeds (roles, tenant demo, usuário master).
Cada migration pode receber seeds via classes Seeder ou comandos artisan personalizados para manter o ambiente sincronizado.

### Testes
- Testes rápidos (sem dependência de banco) disponíveis em `tests/run.php`:
  ```sh
  php tests/run.php
  ```
  Valida normalização de Advanced Matching, propósitos de consentimento e helpers de ingestão.

### Tratamento de dados legados
As antigas tabelas (`users`, `stages`, `leads`, `password_resets`, etc.) não fazem parte do novo modelo BRN Pixel. Antes de rodar o script em produção:
- Exporte ou faça backup das tabelas atuais.
- Planeje uma rotina de transformação (ex.: mapear usuários existentes para a nova tabela `users` com `user_tenant`).
- Ajuste serviços que dependiam do esquema anterior (controllers, views PHP) para os novos domínios multi-tenant.
- Em ambiente de desenvolvimento, recomenda-se dropar o banco antigo e aplicar o novo schema do zero.

- `POST /api/auth/register`
  - Corpo JSON: `{ "email": "user@exemplo", "password": "...", "name": "opcional", "tenant_name": "Minha Empresa" }` ou informe `tenant_id` para entrar em tenant existente.
  - Resposta 201: `{"token":"...","user":{...},"tenant":{...}}`
  - Possíveis erros (JSON): `email_already_registered`, `tenant_name_in_use`, `tenant_name_required`.
- `POST /api/auth/login`
  - Corpo JSON: `{ "email": "...", "password": "..." }`
  - Resposta 200: `{"token":"...","user":{...},"tenants":[{"tenant_id":1,"role_override":"admin"}]}`
  - Erros: `invalid_credentials`, `user_without_tenant`.
- `POST /api/auth/logout`
  - Requer header `Authorization: Bearer <token>` emitido no login/register.
  - Resposta 204 sem corpo. Erro 401 caso token inválido.

> Para demais rotas autenticadas (ex.: `/api/pixels`), envie também o header `X-BRN-Tenant: <tenant_id>` junto do `Authorization`. Papéis `admin` ou `master` são obrigatórios para criar/alterar pixels e tokens.

- `GET /api/pixels`
  - Requer headers `Authorization: Bearer <token>` e `X-BRN-Tenant: <tenant_id>`.
  - Lista pixels do tenant atual.
- `POST /api/pixels`
  - Headers: `Authorization`, `X-BRN-Tenant` (usuário com papel admin/master).
  - Corpo: `{ "name": "Site", "pixel_id": "BRN-PIX-123", "description": "Landing principal" }`.
  - Resposta 201 com dados do pixel.
  - A chave `config` aceita JSON para destinos; ex.:
    ```json
    {
      "meta_capi": {
        "enabled": true,
        "pixel_id": "123456789",
        "access_token": "EAAB...",
        "test_event_code": "TEST123"
      }
    }
    ```
- `GET /api/pixels/{id}`
  - Retorna detalhes do pixel + tokens (sem expor hashes).
- `PATCH /api/pixels/{id}`
  - Permite atualizar `name`, `description`, `is_active`, `config`.
- `DELETE /api/pixels/{id}`
  - Marca o pixel como inativo.
- `POST /api/pixels/{id}/tokens`
  - Gera token novo (hash SHA-256 salvo, valor raw retornado apenas uma vez). Resposta 201 `{ "token_id": 1, "token": "<raw>" }`.
- `DELETE /api/pixels/{id}/tokens/{tokenId}`
  - Revoga token existente.
- `GET /api/webhooks`
  - Lista webhooks do tenant (admin/master).
- `POST /api/webhooks`
  - Cria webhook; envia segredo raw uma única vez (`secret`). Campos: `url`, `headers`, `is_active`.
- `PATCH /api/webhooks/{id}` / `DELETE /api/webhooks/{id}` / `POST /api/webhooks/{id}/rotate`
  - Atualiza, remove ou rotaciona segredo (retorna novo `secret`).
- `POST /api/webhooks/run`
  - Apenas master: processa lote de entregas pendentes (stub até fila ser implementada).
- `GET /api/events`
  - Requer `Authorization` + `X-BRN-Tenant`.
  - Suporta filtros via query: `status`, `event_name`, `pixel_id`, `pixel_public_id`, `from`, `to`, `search`, `page`, `per_page` (máx. 100).
  - Retorna lista paginada com payload/context decodificados e informações do pixel.
- `GET /api/events/{id}`
  - Detalhes de um evento específico (inclui payload/context/dest_status).
- `GET /api/events/metrics`
  - Mesmos filtros de `/api/events`.
  - Resposta `{ "total": 123, "by_status": {"delivered":100, ...}, "last_event_time": "2024-05-22 18:00:00", "events_last_24h": 42 }`.
- `POST /api/consents`
  - Uso pelo SDK de consentimento. Headers: `Content-Type: application/json`, `X-BRN-Pixel-Token`.
  - Corpo: `{ "pixel_id": "BRN-PIX-123", "anonymous_id": "anon-1", "policy_version": "2024-05", "purposes": {"ads": true, "analytics": false}, "granted": true }`.
  - Registra (ou revoga se `granted=false`) o consentimento para o tenant do pixel.
- `GET /api/consents`
  - Requer `Authorization` + `X-BRN-Tenant` com papel admin/master.
  - Filtros: `user_anon_id`, `policy_version`, `active`, `page`, `per_page`.
- `POST /api/consents/{id}/revoke`
  - Marca consentimento como revogado (opcionalmente incluir `meta` com motivo).
- `POST /api/ingest`
  - Headers: `Content-Type: application/json`, `X-BRN-Pixel-Token: <token do pixel (SHA-256)>`.
  - Corpo mínimo:
    ```json
    {
      "pixel_id": "BRN-PIX-123",
      "event_name": "lead",
      "event_id": "evt-123",
      "event_time": "2024-05-21T18:23:00Z",
      "payload": {"value": 100, "currency": "BRL"},
      "advanced_matching": {"email": "user@example.com"},
      "context": {"page_url": "https://site.com/"}
    }
    ```
  - Resposta 202 (novo evento): `{"event_id":1,"event_idempotency":"evt-123","deduplicated":false}`.
  - Resposta 200 com `deduplicated=true` quando o `event_id` já existir.
  - Erros comuns: `pixel_not_found_or_inactive` (404), `invalid_pixel_token` (401), `schema_validation_failed` com `missing_fields` (422).
  - Eventos recém-criados são marcados como `processing`, enviados para a Meta Conversions API (POST `/events`) e geram entregas para webhooks ativos. Configure fila/worker para reprocessar entregas (`POST /api/webhooks/run`) e trate respostas de erro da Meta.

> Observação: os tokens atuais reutilizam a coluna `remember_token` e são armazenados via hash SHA-256. Antes de produção, migre para uma tabela dedicada (ex.: `personal_access_tokens`) com expiração/escopo.

## Licença
MIT
