<?php
/**
 * \php\Service\Setting\Service.php
 *
 * @package     Service
 * @subpackage  Setting
 * @category    Controller
 */
namespace phpUnderControlBuildMonitor\Service\Setting;

use phpUnderControlBuildMonitor\Core\Exception;
use phpUnderControlBuildMonitor\Service\Handler;

/**
 * Setting service handler class.
 *
 * @package     Service
 * @subpackage  Setting
 * @category    Controller
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Service extends Handler
{
    /**
     * Feed settings array.
     *
     * @var array
     */
    protected $settings = array(
        'feedUrl'           => 'feed.xml',
        'buildsPerRow'      => 3,
        'buildClass'        => 'span4',
        'refreshInterval'   => 10,
        'projectsToShow'    => array(),
    );

    /**
     * Method returns current user settings.
     *
     * @throws  Exception
     *
     * @return  array   Array of fail and success Feeds
     */
    public function handleRequestDefault()
    {
        // Fetch settings from session
        $settings = $this->request->getSession('settings', array());

        // If this is happens some weird things have occurred
        if (empty($settings)) {
            throw new Exception("Settings not founded, weird.");
        }

        return $settings;
    }

    /**
     * Method fetches specified RSS feed content.
     *
     * @throws  Exception
     *
     * @param   string  $feedUrl    Feed url to fetch
     * @param   bool    $return     Return feed data or not.
     *
     * @return  void|string
     */
    public function handleRequestGetFeed($feedUrl = '', $return = false)
    {
        // Initialize cURL
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, \CURLOPT_URL, empty($feedUrl) ? $this->settings['feedUrl'] : $feedUrl);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);

        // Get HTTP status code and actual response from server
        $content = curl_exec($ch);
        $status  = (int)curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        // cURL error occurred
        if ($content === false) {
            header("HTTP/1.0 400 Bad Request");

            throw new Exception('cURL error: ' . curl_error($ch));
        }

        // HTTP status code is not 200/OK
        if ($status !== 200) {
            $message = sprintf(
                "HTTP request error with status code: %d. Response: %s",
                $status,
                $content
            );

            throw new Exception($message);
        }

        if ($return) {
            return $content;
        }

        header('Content-type: application/xml');
        echo $content;

        exit(0);
    }

    /**
     * Main service request initializer. If settings are not yet stored
     * to session method will store default setting data to session.
     *
     * Method checks if settings are
     * not stored yet
     *
     * @return  void
     */
    protected function initializeRequest()
    {
        // Fetch settings
        $settings = array(); //$this->request->getSession('settings', array());

        // Settings are not yet set, so make default settings
        if (empty($settings)) {
            $settings = array(
                'feedUrl'           => $this->baseHref . 'feed.xml',
                'buildsPerRow'      => 3,
                'buildClass'        => 'span4',
                'refreshInterval'   => 10,
                'projectsToShow'    => $this->getProjects($this->baseHref . 'feed.xml'),
            );
        }

        // Store setting data to local storage
        $this->settings = $settings;

        // Sore setting data to session
        $this->storeSettings();
    }

    /**
     * Method stores current setting data to user session.
     *
     * @return  void
     */
    private function storeSettings()
    {
        $this->request->setSession('settings', $this->settings);
    }

    /**
     * Method determines which projects are available in specified
     * phpUnderControl RSS feed.
     *
     * @throws  Exception
     *
     * @param   string  $feedUrl    phpUnderControl RSS feed url
     *
     * @return  array               List of all test projects.
     */
    private function getProjects($feedUrl)
    {
        // Load feed url and convert content to SimpleXML object
        $xml = simplexml_load_string($this->handleRequestGetFeed($feedUrl, true));

        // Required data not found
        if (!isset($xml->channel->item)) {
            throw new Exception("Invalid XML feed url: '" . $feedUrl . "'");
        }

        // Initialize output
        $output = array();

        // Iterate RSS feed items
        foreach ($xml->channel->item as $item) {
            $output[] = mb_substr($item->title, 0, mb_strpos($item->title, ' '));
        }

        return $output;
    }
}