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
    /**#@+
     * Image type constants, these are basically same as directories
     * in /html/images/ -folder.
     *
     * @type    constant
     * @var     string
     */
    const TYPE_FAILS = 'fails';
    const TYPE_SUCCESS = 'success';
    /**#@-*/

    /**
     * Image arrays.
     *
     * @var array
     */
    protected $images = array(
        self::TYPE_FAILS    => array(),
        self::TYPE_SUCCESS  => array(),
    );

    /**
     * Method handles current service request. Basically method returns an image
     * url for 'success' or 'fails' image type.
     *
     * @return  string|array|object|bool
     */
    public function handleRequestDefault()
    {
        // Determine image type
        $type = (bool)$this->request->get('failed', true) ? self::TYPE_FAILS : self::TYPE_SUCCESS;

        // Return random image from pool
        return count($this->images[$type]) > 0 ? $this->images[$type][array_rand($this->images[$type], 1)] : false;
    }

    /**
     * Main service request initializer.
     *
     * @return  void
     */
    protected function initializeRequest()
    {
        // Specify base path of status images
        $path = $this->basePath . 'html' . DIRECTORY_SEPARATOR . 'images';

        // Iterate image array
        foreach ($this->images as $type => $images) {
            // Specify current image path
            $imagePath = $path . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR;

            // Iterate image files and add them to main data array
            foreach (glob($imagePath . '*{.jpg,.png}', GLOB_BRACE) as $imageFile) {
                $this->images[$type][] = $imageFile;
            }

            // Convert image files to URIs
            array_walk($this->images[$type], array($this, 'convertImagePathToUrl'));
        }
    }

    /**
     * Method converts base path part of image to corresponding url.
     *
     * @param   string  $image  Image file
     *
     * @return  void
     */
    private function convertImagePathToUrl(&$image)
    {
        $image = str_replace($this->basePath . 'html' . DIRECTORY_SEPARATOR, $this->baseHref, $image);
    }
}