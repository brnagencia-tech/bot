# Roadmap MVP → v1.x

## v1.1 – WhatsApp Saída + Filas
- Envio de mensagens (texto, template, mídia) via Cloud API.
- Persistência de templates e vinculação por tenant.
- Jobs em fila (Redis/Database) para envio e webhooks de status.
- Tratamento de erros e retries + logs estruturados.
- Variáveis de ambiente e validação.

## v1.2 – Serviço de IA (FastAPI)
- Endpoints /chat/route, /embed, /search.
- Vetores com pgvector ou Qdrant; ingestão de PDFs/URLs/FAQs.
- Orquestração (function calling) para ferramentas: create_lead, update_stage, book_meeting, send_payment_link.
- Integração Laravel ↔ IA com timeouts e retries.

## v1.3 – Automations & Horário Comercial
- Regras de horário por tenant (SUPORTE pós-00:00).
- Triggers (sem resposta 2h, mudança de estágio) + ações (follow-up, tarefa).
- SLA + tarefas agendadas.

## v1.4 – Analytics
- Métricas: 1ª resposta, conversões por estágio/agente.
- Motivos de perda e marcadores (intenção/sentimento).
- Dashboards simples em Blade.

## v1.5 – Governança & LGPD
- Auditoria ampliada, mascaramento de PII.
- Retenção configurável por tenant.
- Consentimento e políticas.

## v1.6 – Billing (SaaS)
- Assinaturas/planos, limites por tenant, trilhas de upgrade.

