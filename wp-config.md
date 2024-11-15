```
<?php

define('FORCE_SSL_ADMIN', true); if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
    $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $_SERVER['HTTPS'] = 'on';
}

define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);
define('ADMIN_COOKIE_PATH', '/');
define('COOKIEPATH', '');
define('SITECOOKIEPATH', '');


define( 'FS_METHOD', 'direct' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );


define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', true );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', 'www.xoppio.com' );
// if host is not DOMAIN_CURRENT_SITE then forward to www.hostname
if ( $_SERVER['HTTP_HOST'] != DOMAIN_CURRENT_SITE && !str_contains($_SERVER['HTTP_HOST'], 'www.')) {
    header( 'Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
    exit;
}

define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );
```