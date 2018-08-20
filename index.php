<?php


date_default_timezone_set('America/New_York');

$config = require 'config.php';

require dirname(__FILE__).'/vendor/autoload.php';

use App\Core\BulkPricing;
use App\Core\Router;
use App\Core\Request;
use App\Core\Helpers;

$bulkpricing = new BulkPricing();

$bulkpricing->callSessionStart();

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

//$bulkpricing->checkLoggedIn("BULKPRICING", "bulkpricing");

/**
 * if we are authenticated, we go to the router
 */
//die($config['appdir']);
Router::load($config['appdir'] . '/routes.php')->direct(Request::uri(), Request::method());
//die(Request::uri(), Request::method());
