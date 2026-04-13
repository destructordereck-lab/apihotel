FROM php:8.2-cli
COPY . /app
WORKDIR /app
EXPOSE 10000
ENV PORT 10000
RUN chmod +x /app/start.sh
CMD ["/app/start.sh"]
