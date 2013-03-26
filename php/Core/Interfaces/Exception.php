<?php
/**
 * \php\Core\Interfaces\Exception.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Interface
 */
namespace phpUnderControlBuildMonitor\Core\Interfaces;

/**
 * Interface for following classes classes:
 *  - \phpUnderControlBuildMonitor\Core\Exception
 *  - \phpUnderControlBuildMonitor\Core\ErrorException
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Interface
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
interface Exception
{
    public function makeJsonResponse();
}
