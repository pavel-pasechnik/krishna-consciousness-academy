#!/usr/bin/env bash
set -euo pipefail

: "${AIVEN_TOKEN:?AIVEN_TOKEN is required}"
: "${AIVEN_PROJECT:?AIVEN_PROJECT is required}"
: "${AIVEN_SERVICE:?AIVEN_SERVICE is required}"
: "${AIVEN_DB:?AIVEN_DB is required}"

# Предохранитель: выполнится только если явно включён reset
if [[ "${AIVEN_RESET_DB:-}" != "1" ]]; then
  echo "AIVEN_RESET_DB!=1 → пропускаю сброс базы"
  exit 0
fi

API="https://api.aiven.io/v1/project/${AIVEN_PROJECT}/service/${AIVEN_SERVICE}"

echo "→ Удаляю БД ${AIVEN_DB} в сервисе ${AIVEN_SERVICE} (проект ${AIVEN_PROJECT})"
# Пытаемся удалить; 404 игнорируем (базы могло не быть)
set +e
curl -fsS -X DELETE \
  -H "Authorization: Bearer ${AIVEN_TOKEN}" \
  -H "Accept: application/json" \
  "${API}/db/${AIVEN_DB}"
status=$?
set -e
if [[ $status -ne 0 ]]; then
  echo "Внимание: DELETE вернул код $status (вероятно, БД не существовала) — продолжаю"
fi

# Небольшая пауза, чтобы сервис применил изменения
sleep 2

echo "→ Создаю БД ${AIVEN_DB} заново"
curl -fsS -X POST \
  -H "Authorization: Bearer ${AIVEN_TOKEN}" \
  -H "Content-Type: application/json" \
  -d "{\"database\":\"${AIVEN_DB}\"}" \
  "${API}/db"

echo "✓ Готово: база ${AIVEN_DB} пересоздана"