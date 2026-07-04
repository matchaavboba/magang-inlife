FROM richarvey/nginx-php-fpm:3.1.6

# Set working directory
WORKDIR /var/www/html

# Install additional system packages
RUN apk add --no-interactive --no-cache nodejs npm postgresql-client

# Copy application files
COPY . .

# Set permissions
RUN chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port
EXPOSE 80

# Environment variables for richarvey/nginx-php-fpm
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV APP_DEBUG false
ENV RUN_SCRIPTS 1

# The image will automatically run scripts in /var/www/html/conf/http/hooks or execute command
# We can tell it to run our shell script as entrypoint or hook.
# Let's configure the richarvey startup script hook.
RUN mkdir -p /var/www/html/conf/http/
COPY scripts/00-laravel-deploy.sh /var/www/html/conf/http/00-laravel-deploy.sh
RUN chmod +x /var/www/html/conf/http/00-laravel-deploy.sh

# Start command
ENTRYPOINT ["/bin/bash", "-c", "/var/www/html/conf/http/00-laravel-deploy.sh && /start.sh"]
