#!/usr/bin/env bash
set -euo pipefail

# Requires: GitHub CLI (gh) authenticated with repo scope
# Usage: scripts/create_github_issues.sh [owner/repo]

if ! command -v gh >/dev/null 2>&1; then
  echo "ERROR: gh (GitHub CLI) not found. Install: https://cli.github.com/" >&2
  exit 1
fi

REPO_NWO="${1:-}"
if [[ -z "$REPO_NWO" ]]; then
  # Try to detect from current directory's origin remote
  origin_url=$(git -C "$(dirname "$0")/.." remote get-url origin 2>/dev/null || true)
  if [[ "$origin_url" =~ github.com[:/](.+)/(.+)\.git ]]; then
    REPO_NWO="${BASH_REMATCH[1]}/${BASH_REMATCH[2]}"
  else
    # Fallback to gh repo view
    REPO_NWO=$(gh repo view --json nameWithOwner -q .nameWithOwner 2>/dev/null || true)
  fi
fi

if [[ -z "$REPO_NWO" ]]; then
  echo "ERROR: Could not determine owner/repo. Pass as arg: scripts/create_github_issues.sh owner/repo" >&2
  exit 1
fi

echo "Using repository: $REPO_NWO"

create_label() {
  local name="$1"; shift
  local color="$1"; shift
  local description="$*"
  gh label create "$name" \
    --color "$color" \
    --description "$description" \
    --repo "$REPO_NWO" \
    --force >/dev/null || true
}

echo "Ensuring labels..."
create_label "area:backend" 0e8a16 "Trabalho de backend/Laravel"
create_label "area:infra"   5319e7 "Infra/DevOps/filas/webhooks"
create_label "area:ia"      fbca04 "Serviço IA / embeddings / tools"
create_label "area:frontend" 1d76db "UI/UX/Views"
create_label "priority:P1"  d73a4a "Alta prioridade"

create_milestone() {
  local title="$1"; shift
  local description="$*"
  gh api -X POST \
    "/repos/$REPO_NWO/milestones" \
    -f title="$title" \
    -f description="$description" \
    -q .number
}

create_issue() {
  local title="$1"; shift
  local body="$1"; shift
  local milestone="$1"; shift
  local labels="$1"; shift
  gh issue create \
    --repo "$REPO_NWO" \
    --title "$title" \
    --body "$body" \
    --milestone "$milestone" \
    --label "$labels" >/dev/null
}

echo "Creating milestones..."
MS1=$(create_milestone "v1.1 – WhatsApp saída + filas" "Envio de mensagens (texto/template/mídia), filas, templates, status e erros.")
MS2=$(create_milestone "v1.2 – Serviço de IA" "FastAPI: /chat/route, /embed, /search + vetores e tools.")
MS3=$(create_milestone "v1.3 – Automations & horários" "Regras de horário, SLAs, follow-ups e tarefas.")
MS4=$(create_milestone "v1.4 – Analytics" "Métricas e dashboards.")
MS5=$(create_milestone "v1.5 – Governança & LGPD" "Auditoria/PII/retentiva.")

echo "Creating issues for v1.1..."
create_issue \
  "Cloud API: enviar texto e templates" \
  "Implementar cliente de envio (texto/template), validar HSM, parametrizar por tenant." \
  "$MS1" \
  "area:backend,area:infra,priority:P1"

create_issue \
  "Persistência e gestão de templates WhatsApp" \
  "CRUD de templates por tenant, sync opcional com Cloud API." \
  "$MS1" \
  "area:backend,area:frontend"

create_issue \
  "Filas: jobs de envio + webhooks de status" \
  "Jobs com retries, idempotência por wa_message_id, atualização de status." \
  "$MS1" \
  "area:infra,area:backend,priority:P1"

create_issue \
  "Observabilidade e erros no conector WhatsApp" \
  "Logs estruturados, Sentry (opcional), métricas básicas por tenant." \
  "$MS1" \
  "area:infra"

echo "Creating issues for v1.2..."
create_issue \
  "FastAPI: /chat/route básico" \
  "Implementar endpoint que retorna mensagem e ações (mock inicialmente)." \
  "$MS2" \
  "area:ia,area:backend,priority:P1"

create_issue \
  "Embeddings + vetor (pgvector/Qdrant)" \
  "Ingestão de PDFs/URLs/FAQs, busca semântica com top-k e filtros por tenant." \
  "$MS2" \
  "area:ia"

create_issue \
  "Ferramentas (function calling)" \
  "create_lead, update_stage, book_meeting, send_payment_link com contratos estáveis." \
  "$MS2" \
  "area:ia,area:backend"

echo "Creating issues for v1.3..."
create_issue \
  "Regras de horário por tenant" \
  "Modelo + middleware de roteamento para SUPORTE pós-00:00." \
  "$MS3" \
  "area:backend"

create_issue \
  "Automations: triggers e ações" \
  "Sem resposta 2h ⇒ follow-up; mudança de estágio ⇒ ação configurável." \
  "$MS3" \
  "area:backend,area:infra"

echo "Creating issues for v1.4..."
create_issue \
  "Métricas e dashboards" \
  "1ª resposta, taxa de conversão por estágio/agente, motivos de perda." \
  "$MS4" \
  "area:backend,area:frontend"

echo "Creating issues for v1.5..."
create_issue \
  "LGPD: auditoria, PII e retenção" \
  "Auditoria detalhada, mascaramento de PII, políticas de retenção por tenant." \
  "$MS5" \
  "area:backend"

echo "All done. Check the repo: https://github.com/$REPO_NWO/issues"

