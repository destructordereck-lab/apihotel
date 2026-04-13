FROM php:8.2-cli

# Copiar todo el código al contenedor
COPY . /app
WORKDIR /app

# Exponer el puerto
EXPOSE 10000

# Comando de arranque
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/app", "index.php"]

