#!/bin/sh
echo "STARTING APP"
echo "ENV PORT = ${PORT}"
echo "ENV DB_HOST = ${DB_HOST}"
echo "ENV DB_USER = ${DB_USER}"
echo "ENV DB_NAME = ${DB_NAME}"
echo "Running: php -S 0.0.0.0:${PORT} -t /app index.php"
exec php -S 0.0.0.0:${PORT} -t /app index.php
