FROM php:8.2-cli

# Copiar todo el código al contenedor
COPY . /app
WORKDIR /app

# Exponer el puerto (documental)
EXPOSE 10000

# Usar la variable de entorno PORT que Render provee
ENV PORT 10000

# Comando de arranque: usar sh -c para leer $PORT en tiempo de ejecución
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT} -t /app index.php"]
