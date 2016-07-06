<?php
/**
 * Main bootstrap file.
 */

// Paden defineren.
define('DIR_ROOT', dirname(__DIR__));
define('DIR_APP', __DIR__);
define('DIR_APP_INC', DIR_APP . '/inc');
define('DIR_VENDOR', DIR_APP . '/vendor');
define('DIR_APP_TEMPLATES', DIR_APP . '/templates');
define('DIR_APP_MODEL', DIR_APP . '/model');
define('DIR_APP_IMG', DIR_APP . '/files/images');


// Starten van template engine.
require_once(DIR_VENDOR . '/lib/Twig/Autoloader.php');
Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem(DIR_APP_TEMPLATES);
$twig = new Twig_Environment($loader, array(
    //'cache' => DIR_APP . '/cache/templates',
));

// Bestanden die vereist zijn.
require_once(DIR_APP_INC . '/app.php');

// Debug mode?
if (app_config('debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

require_once(DIR_APP_MODEL . '/winkels.php');
require_once(DIR_APP_MODEL . '/users.php');
require_once(DIR_APP_MODEL . '/products.php');
require_once(DIR_APP_MODEL . '/bestellingen.php');
require_once(DIR_APP_MODEL . '/database.php');
require_once(DIR_ROOT . '/inc/PHPMailer/PHPMailerAutoload.php');
