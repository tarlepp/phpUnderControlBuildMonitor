<?php
/**
 * Main php functionality for all service requests
 *
 * @author  Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
use \phpUnderControlBuildMonitor\Core\Router;
use \phpUnderControlBuildMonitor\Core\System;
use \phpUnderControlBuildMonitor\Core\Request;
use \phpUnderControlBuildMonitor\Util\JSON;

// We want to show all errors.
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

try {
    // Require system init file
    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "init.php";

    // Get current request object
    $request = Request::getInstance();

    if (!$request->isAjax()) {
        header('Location: ' . System::$baseHref);
        exit(0);
    }

    // Handle current request via Router
    Router::handleRequest($request);
} catch (\Exception $error) {
    /**
     * Exception that was thrown in phpUnderControlBuildMonitor system
     *
     * @var $error \phpUnderControlBuildMonitor\Core\Exception
     */
    if (method_exists($error, 'makeJsonResponse')) {
        $error->makeJsonResponse();
    } else { // Base exception this is a failure by default
        $message = str_replace(System::$basePath, DIRECTORY_SEPARATOR, $error->getMessage());

        $data = array(
            'message'   => 'Invalid exception thrown. ' . $message,
            'code'      => $error->getCode(),
            'file'      => str_replace(System::$basePath, DIRECTORY_SEPARATOR, $error->getFile()),
            'line'      => $error->getLine(),
            'trace'     => str_replace(System::$basePath, DIRECTORY_SEPARATOR, $error->getTraceAsString()),
        );

        header("HTTP/1.0 400 Bad Request");

        JSON::makeHeaders();

        echo JSON::encode(array('error' => $data));

        exit(0);
    }
}
