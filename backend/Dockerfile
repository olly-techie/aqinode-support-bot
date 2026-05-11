FROM php:8.2-apache

# Install system dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libxml2-dev \
    && docker-php-ext-install dom xml

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy backend files
COPY . .

# Set permissions for the knowledge file (if it exists or will be created)
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 (Render will map this to its own port)
EXPOSE 80

# The default command for php:apache is to start apache in the foreground
