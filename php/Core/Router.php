<?php
/**
 * \php\Core\Router.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Router
 */
namespace phpUnderControlBuildMonitor\Core;

use phpUnderControlBuildMonitor\Service\Handler;
use phpUnderControlBuildMonitor\Util\Exception;

/**
 * General router class. Basically all AJAX request are routed via this class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Router
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Router implements Interfaces\Router
{
    /**
     * This method handles _all_ AJAX request for phpUnderControlBuildMonitor service controller.
     * Basically method determines what service user want to process. Actual response is made in
     * specified service class.
     *
     * Note that all requests are made via AJAX other request are not routed to this class.
     *
     * @access  public
     * @static
     *
     * @throws  Exception
     *
     * @param   \phpUnderControlBuildMonitor\Core\Request $request
     *
     * @return  void
     */
    public static function handleRequest(Request &$request)
    {
        // Determine current service and token
        $service = $request->get('service', null);
        $action = $request->get('action', null);
        $csrfToken = $request->get('token', null);

        // Check that request contains valid CSRF token
        if (is_null($csrfToken) || strcmp($csrfToken, System::$csrfToken) !== 0) {
            throw new Exception("Invalid CSRF token.");
        }

        // No service class defined
        if (is_null($service)) {
            throw new Exception("Required service class not defined.");
        }

        // Specify used service class for current request
        $class = "\\phpUnderControlBuildMonitor\\Service\\" . $service . "\\Service";

        // Check that asked controller exists
        if (!class_exists($class)) {
            throw new Exception("Asked service class '" . $service . "' not found.");
        }

        // Specify action to call
        $action = is_null($action) ? 'Default' : $action;

        /**
         * Create service handler object and handle defined request.
         *
         * @var $handler    Handler  This is for the smart IDEs
         */
        $handler = new $class($request, $action);

        // Invalid service class
        if (!$handler instanceof Handler) {
            throw new Exception("Invalid service class.");
        }

        // Handle current request
        $handler->handleRequest();

        unset($moduleController);
    }
}
