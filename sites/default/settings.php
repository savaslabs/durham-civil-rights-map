<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}

$settings['install_profile'] = 'standard';

if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  // Dev.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'dev') {
    $domain = 'dev-durham-civil-rights-map.pantheonsite.io';
    $settings['trusted_host_patterns'] = array(
      'dev-durham-civil-rights-map\.pantheonsite\.io$',
    );
  }
  // Test.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'test') {
    $domain = 'test-durham-civil-rights-map.pantheonsite.io';
    $settings['trusted_host_patterns'] = array(
      'test-durham-civil-rights-map\.pantheonsite\.io$',
    );
  }
  // Live.
  if ($_ENV['PANTHEON_ENVIRONMENT'] === 'live') {
    $domain = 'www.durhamatletico.com';
    $settings['trusted_host_patterns'] = array(
      'durhamcivilrightsmap\.org$',
    );
  }
  else {
    $domain = $_SERVER['HTTP_HOST'];
  }
}