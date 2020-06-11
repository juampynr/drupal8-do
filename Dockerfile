FROM juampynr/drupal8ci:latest
COPY . /var/www/html/
RUN robo project:build
