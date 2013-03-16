<?php
/**
 * \php\init.php
 *
 * This file contains all necessary actions for phpUnderControlBuildMonitor core initialization.
 * Basically file contains different init function calls so that system will work as designed.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Core
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
use phpUnderControlBuildMonitor\Core\System;

// Used base files to use
$files = array(
    array(
        dirname(__FILE__),
        'Core',
        'Interfaces',
        'Autoload.php'
    ),
    array(
        dirname(__FILE__),
        'Core',
        'Autoload.php'
    ),
);

// Require defined base files
foreach ($files as $file) {
    require_once implode(DIRECTORY_SEPARATOR, $file);
}

// Register autoload functionality
spl_autoload_register(array(new \phpUnderControlBuildMonitor\Core\Autoload(), 'load'));

// Initialize system
System::initialize();
