# --- 1) Wait for DB (if provided) ---
if [ -n "${DB_HOST_ONLY}" ] && [ -n "${DB_PORT_ONLY}" ]; then
  echo "Waiting for database at ${DB_HOST_ONLY}:${DB_PORT_ONLY} ..."
  # Wait loop code here...
fi

# --- Drop ALL tables on boot if requested (only before first init) ---
if [ "${RESET_DB_ON_BOOT:-0}" = "1" ] && [ ! -f "${DOCROOT}/wp-config.php" ]; then
  if [ -n "${WORDPRESS_DB_NAME}" ] && [ -n "${WORDPRESS_DB_USER}" ] && [ -n "${WORDPRESS_DB_PASSWORD}" ] && [ -n "${DB_HOST_ONLY}" ] && [ -n "${DB_PORT_ONLY}" ]; then
    echo "RESET_DB_ON_BOOT=1 and no wp-config.php → Dropping ALL TABLES in ${WORDPRESS_DB_NAME}@${DB_HOST_ONLY}:${DB_PORT_ONLY} ..."
    MYSQL_OPTS=( -h "${DB_HOST_ONLY}" -P "${DB_PORT_ONLY}" -u "${WORDPRESS_DB_USER}" --protocol=TCP --batch --skip-column-names )
    # SSL options if enabled
    case "$(echo "${WORDPRESS_DB_SSL}" | tr '[:upper:]' '[:lower:]')" in
      1|true|required)
        CA_PATH="${WORDPRESS_DB_SSL_CA_PATH:-/etc/ssl/certs/mysql-ca.pem}"
        MYSQL_OPTS+=( --ssl-mode=REQUIRED --ssl-ca="${CA_PATH}" )
        ;;
    esac
    export MYSQL_PWD="${WORDPRESS_DB_PASSWORD}"
    mysql "${MYSQL_OPTS[@]}" -e "SET SESSION sql_notes=0; SET FOREIGN_KEY_CHECKS=0; SELECT CONCAT('DROP TABLE IF EXISTS ', GROUP_CONCAT(CONCAT('\`', table_name, '\`') SEPARATOR ','), ';') FROM information_schema.tables WHERE table_schema='${WORDPRESS_DB_NAME}'; SET FOREIGN_KEY_CHECKS=1;" \
      | mysql "${MYSQL_OPTS[@]}" "${WORDPRESS_DB_NAME}" \
      && echo "All tables dropped in ${WORDPRESS_DB_NAME}." \
      || echo "WARNING: Failed to drop tables (database may be empty or unreachable)."
    unset MYSQL_PWD
  else
    echo "RESET_DB_ON_BOOT=1 set, but DB env vars are incomplete; skipping drop."
  fi
else
  if [ "${RESET_DB_ON_BOOT:-0}" = "1" ] && [ -f "${DOCROOT}/wp-config.php" ]; then
    echo "RESET_DB_ON_BOOT=1 but wp-config.php exists → redeploy detected, skipping drop."
  fi
fi