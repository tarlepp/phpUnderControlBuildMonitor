<?php
/**
 * \php\Service\Image\Service.php
 *
 * @package     Service
 * @subpackage  Image
 * @category    Controller
 */
namespace phpUnderControlBuildMonitor\Service\Image;

use phpUnderControlBuildMonitor\Service\Exception;
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
     * Method returns an array of fail and success images. Output is a multidimensional
     * array which contains request count of images. Actual image values are direct urls
     * to them.
     *
     * @return  array   Array of fail and success images
     */
    public function handleRequestDefault()
    {
        // Initialize used image arrays
        $imagesFails = $imagesSuccess = array();

        // Iterate images
        foreach ($this->images as $type => $images) {
            // Determine request count of images
            $imageCount = (int)$this->request->get('cnt' . ucfirst($type), 0);
            $imageArray = 'images' . ucfirst($type);

            $count = count($images);

            // No need for this type of images
            if ($imageCount === 0) {
                ${$imageArray} = array();

                continue;
            }

            // We don't have required amount of images
            while ($imageCount > count($images)) {
                // Only one image, so do not make unnecessary random for images
                if ($count === 1) {
                    $images[] = current($images);
                } else { // Otherwise select random key from array
                    $images[] = $images[array_rand($images)];
                }
            }

            $keys = array_rand($images, $imageCount);

            if (!is_array($keys)) {
                $keys = array($keys);
            }

            // Get request count of random images from current images array
            ${$imageArray} = array_values(array_intersect_key($images, array_flip($keys)));
        }

        $data = array(
            self::TYPE_FAILS    => $imagesFails,
            self::TYPE_SUCCESS  => $imagesSuccess,
        );

        return $data;
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

            // Fetch images
            $images = glob($imagePath . '*{.jpg,.png}', GLOB_BRACE);

            if (count($images) > 0) {
                // Iterate image files and add them to main data array
                foreach ($images as $imageFile) {
                    $this->images[$type][] = $imageFile;
                }
            } else {
                $this->images[$type][] = false;
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