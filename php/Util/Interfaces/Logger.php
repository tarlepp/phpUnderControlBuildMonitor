<?php
/**
 * \php\Util\Interfaces\Logger.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Util
 * @category    Interface
 */
namespace phpUnderControlBuildMonitor\Util\Interfaces;

/**
 * Interface for \phpUnderControlBuildMonitor\Util\Logger -class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Util
 * @category    Interface
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
interface Logger
{
    /**
     * Construction of the class.
     *
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception);
}
