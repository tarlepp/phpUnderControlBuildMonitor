<?php
/**
 * \php\Core\Autoload.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Autoload
 */
namespace phpUnderControlBuildMonitor\Core;

/**
 * This class contains phpUnderControlBuildMonitor autoload class, which handles class
 * autoload functionality.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Autoload
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Autoload implements Interfaces\Autoload
{
    /**
     * Used base path for classes.
     *
     * @var string
     */
    private $path = '';

    /**
     * Constructor of the class.
     */
    public function __construct()
    {
        $this->path = dirname(dirname(__FILE__)) . DS;
    }

    /**
     * Main autoload functionality.
     *
     * @param   string  $class  The name of the class to attempt to load.
     *
     * @return  void
     */
    public function load($class)
    {
        // Namespace call
        if (mb_strpos($class, '\\') !== false) {
            $bits = explode('\\', $class);

            $check = array_shift($bits);

            if ($check !== 'phpUnderControlBuildMonitor') {
                return;
            }

            $class = array_pop($bits);
        }

        if (isset($bits) && is_array($bits) && count($bits) > 0) {
            $classDir  = implode(DS, $bits);
            $classFile = $this->path . $classDir . DS . $class . ".php";

            if (is_readable($classFile)) {
                require_once $classFile;
            }
        }
    }
}
