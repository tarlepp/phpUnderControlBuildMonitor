<?php
/**
 * \php\Service\Interfaces\Handler.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Service
 * @category    Interface
 */
namespace phpUnderControlBuildMonitor\Service\Interfaces;

use phpUnderControlBuildMonitor\Core\Request;
use phpUnderControlBuildMonitor\Service\Exception;

/**
 * Interface for \phpUnderControlBuildMonitor\Service\Handler -class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Service
 * @category    Interface
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
interface Handler
{
    /**
     * Construction of the class.
     *
     * @param   Request $request    Request object
     * @param   string  $action     Service action to run
     */
    public function __construct(Request $request, $action);

    /**
     * Method calls requested service class handleRequest method and outputs its content
     * as a JSON object.
     *
     * @throws  Exception
     *
     * @return  void
     */
    public function handleRequest();

    /**
     * Method handles current service request.
     *
     * @return  string|array|object|bool
     */
    public function handleRequestDefault();
}
