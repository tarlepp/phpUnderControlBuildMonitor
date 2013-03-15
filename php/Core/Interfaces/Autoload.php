<?php
/**
 * \php\Core\Interfaces\Autoload.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Interface
 */
namespace phpUnderControlBuildMonitor\Core\Interfaces;

/**
 * Interface for \phpUnderControlBuildMonitor\Core\Autoload -class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Interface
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
interface Autoload
{
    public function __construct();
    public function load($class);
}
