FROM wordpress:php8.1-fpm

# ... previous content unchanged ...

COPY ./wordpress-config/custom.ini /usr/local/etc/php/conf.d/custom.ini

# === Optional: Reset Aiven MySQL at build-time (TEST ONLY) ===
ARG AIVEN_TOKEN
ARG AIVEN_PROJECT
ARG AIVEN_SERVICE
ARG AIVEN_DB
ARG AIVEN_RESET_DB=0
RUN if [ "$AIVEN_RESET_DB" = "1" ]; then \
      API="https://api.aiven.io/v1/project/${AIVEN_PROJECT}/service/${AIVEN_SERVICE}" && \
      echo "Deleting DB ${AIVEN_DB}…" && \
      (curl -fsS -X DELETE -H "Authorization: Bearer ${AIVEN_TOKEN}" -H "Accept: application/json" "$API/db/${AIVEN_DB}" || true) && \
      sleep 2 && \
      echo "Creating DB ${AIVEN_DB}…" && \
      curl -fsS -X POST -H "Authorization: Bearer ${AIVEN_TOKEN}" -H "Content-Type: application/json" -d "{\"database\":\"${AIVEN_DB}\"}" "$API/db" ; \
    else \
      echo "AIVEN_RESET_DB!=1 → skip DB reset at build" ; \
    fi
# === End optional reset ===

# ... any other COPY or RUN steps ...

# Removed the lines that copy and chmod reset_aiven_mysql.sh

CMD ["/usr/local/bin/init-wordpress.sh"]