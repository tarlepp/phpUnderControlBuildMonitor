<?php
/**
 * \php\Service\Handler.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Service
 * @category    Controller
 */
namespace phpUnderControlBuildMonitor\Service;

use phpUnderControlBuildMonitor\Core\Request;
use phpUnderControlBuildMonitor\Core\System;
use phpUnderControlBuildMonitor\Util\JSON;

/**
 * Base service handler class. All service classes _must_ extend this base class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Service
 * @category    Controller
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
abstract class Handler implements Interfaces\Handler
{
    /**
     * Request object.
     *
     * @var Request
     */
    protected $request;

    /**
     * Service action to handle.
     *
     * @var string
     */
    protected $action;

    /**
     * Site base href.
     *
     * @var string
     */
    protected $baseHref;

    /**
     * Site base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * Construction of the class.
     *
     * @param   Request $request    Request object
     * @param   string  $action     Service action to run
     */
    public function __construct(Request $request, $action)
    {
        // Store used variables.
        $this->request = $request;
        $this->action = $action;

        // Store site base href and path
        $this->baseHref = System::$baseHref;
        $this->basePath = System::$basePath;
    }

    /**
     * Method calls requested service class handleRequest method and outputs its content
     * as a JSON object.
     *
     * @throws  Exception
     *
     * @return  void
     */
    public function handleRequest()
    {
        // Specify service action to call
        $method = 'handleRequest' . $this->action;

        // Method doesn't exist in current scope
        if (!method_exists($this, $method)) {
            throw new Exception("Request service method for '" . $this->action . "' action not found.");
        }

        // Specify init methods to check
        $init = array(
            'initializeRequest',
            'initializeRequest'. $this->action,
        );

        // Iterate initialize methods and call them if founded
        foreach ($init as $initMethod) {
            if (method_exists($this, $initMethod)) {
                call_user_func(array($this, $initMethod));
            }
        }

        JSON::makeHeaders();

        echo JSON::encode(call_user_func(array($this, $method)));

        exit(0);
    }
}