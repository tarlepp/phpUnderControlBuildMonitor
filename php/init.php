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

// HomeAI autoload classes
require_once dirname(__FILE__) . DS . 'Core' . DS . 'Interfaces' . DS . 'Autoload.php';
require_once dirname(__FILE__) . DS . 'Core' . DS . 'Autoload.php';

// Register autoload functionality
spl_autoload_register(array(new \phpUnderControlBuildMonitor\Core\Autoload(), 'load'));

// Initialize system
System::initialize();
