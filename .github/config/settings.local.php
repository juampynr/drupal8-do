<?php

/**
 * Local settings for building an artifact at GitHub Actions.
 */

$databases['default']['default'] = [
  'database' => getenv('DB_NAME'),
  'username' => getenv('DB_USER'),
  'password' => getenv('DB_PASSWORD'),
  'prefix' => '',
  'host' => getenv('DB_HOST'),
  'port' => getenv('DB_HOST'),
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => getenv('DB_DRIVER'),
];
