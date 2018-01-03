<?php

use Radiergummi\DynDns\Configuration;

// enable the autoloader
require 'vendor/autoload.php';

$config = new Configuration();

/* ------------------------------------------------------------------------------------
 * Configuration options
 *
 * Make sure you modify these to your needs.
 * ------------------------------------------------------------------------------------
 */

// IMPORTANT: Application secret. Change this to a random string!
// You can use the following command to generate one:
// openssl rand -base64 32
// $config->secret = '...';

// application name as shown on the CLI
$config->name = 'Cloudflare DynDNS';

// whether to enable logging
$config->logEnabled = true;

// where to write the log file to
$config->logPath = __DIR__ . '/dyndns.log';

// whether to enable debugging
$config->debug = true;

// Slim configuration options. The `displayErrorDetails` option controls display of
// errors. Leave it like this to connect the option to the debug setting.
$config->slimConfiguration = [
    'settings' => [
        'displayErrorDetails' => $config->debug
    ]
];

/* ------------------------------------------------------------------------------------
 * No modification required below this point
 * ------------------------------------------------------------------------------------
 */

// define the base path to the application
define( 'PATH', dirname( __FILE__ ) );

// determine whether this is a console request
$isCLIRequest = php_sapi_name() === 'cli';

// fetch the appropriate routes
$routes = ( $isCLIRequest
    ? include PATH . '/src/routes/console.php'
    : include PATH . '/src/routes/web.php'
);

// load the routes
$config->routes = $routes;

// create the kernel namespace path
$application = 'Radiergummi\\DynDns\\' . ( $isCLIRequest
        ? 'ConsoleKernel'
        : 'WebKernel'
    );

//  Create the app passing the configuration
/** @var \Radiergummi\DynDns\Kernel $app */
$app = new $application( $config );

// try to execute the application
try {
  $app->init();
}
catch ( Throwable $exception ) {
  if ( $config->debug ) {
    echo 'A fatal error occurred in ' . $exception->getFile() . ' on line ' . $exception->getLine();
    echo $exception->getMessage();
  } else {
    echo 'Something has gone terribly wrong!';
  }
}
