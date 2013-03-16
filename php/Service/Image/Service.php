<?php
/**
 * \php\Service\Image\Service.php
 *
 * @package     Service
 * @subpackage  Image
 * @category    Controller
 */
namespace phpUnderControlBuildMonitor\Service\Image;

use phpUnderControlBuildMonitor\Service\Handler;

/**
 * Image service handler class.
 *
 * @package     Service
 * @subpackage  Image
 * @category    Controller
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Service extends Handler
{
    /**
     * Method handles current service request.
     *
     * @return  string|array|object|bool
     */
    public function handleRequestDefault()
    {
        // TODO: implement real functionality

        return "images/success/chuck-norris-approved.png";
    }
}