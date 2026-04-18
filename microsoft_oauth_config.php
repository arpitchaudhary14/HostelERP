<?php
require_once __DIR__ . '/env.php';
define('MS_CLIENT_ID',     $_ENV['MS_CLIENT_ID'] ?? '');
define('MS_CLIENT_SECRET', $_ENV['MS_CLIENT_SECRET'] ?? '');
define('MS_REDIRECT_URI',  $_ENV['MS_REDIRECT_URI'] ?? '');
define('MS_TENANT',        'common');
define('MS_AUTH_URL',  'https://login.microsoftonline.com/' . MS_TENANT . '/oauth2/v2.0/authorize');
define('MS_TOKEN_URL', 'https://login.microsoftonline.com/' . MS_TENANT . '/oauth2/v2.0/token');
define('MS_USER_URL',  'https://graph.microsoft.com/v1.0/me');
?>