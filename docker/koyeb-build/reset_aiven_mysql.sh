FROM wordpress:latest

# Copy the init script and make it executable
COPY init-wordpress.sh /usr/local/bin/init-wordpress.sh
RUN chmod +x /usr/local/bin/init-wordpress.sh

# Copy the reset script and make it executable
COPY reset_aiven_mysql.sh /usr/local/bin/reset_aiven_mysql.sh
RUN chmod +x /usr/local/bin/reset_aiven_mysql.sh

# curl is already installed earlier, so no need to install again

CMD ["bash", "-c", "/usr/local/bin/reset_aiven_mysql.sh && /usr/local/bin/init-wordpress.sh"]