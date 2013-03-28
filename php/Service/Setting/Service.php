<?php
/**
 * \php\Service\Setting\Service.php
 *
 * @package     Service
 * @subpackage  Setting
 * @category    Controller
 */
namespace phpUnderControlBuildMonitor\Service\Setting;

use phpUnderControlBuildMonitor\Service\Exception;
use phpUnderControlBuildMonitor\Service\Handler;
use phpUnderControlBuildMonitor\Util\Config;
use phpUnderControlBuildMonitor\Util\JSON;

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
        'buildsPerRow'      => 4,
        'buildClass'        => 'span4',
        'refreshInterval'   => 10,
        'projectsToShow'    => array(),
    );

    /**
     * Project cache.
     *
     * @var array
     */
    protected $projectCache = array();

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
        curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 5);

        // Get HTTP status code and actual response from server
        $content = curl_exec($ch);
        $status  = (int)curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        // cURL error occurred
        if ($content === false) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        // HTTP status code is not 200/OK
        if ($status !== 200) {
            $message = sprintf(
                "HTTP request error %d<br />%s",
                $status,
                strip_tags($content)
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
     * Method return build monitor setting dialog data.
     *
     * @return array
     */
    public function handleRequestSettingsDialog()
    {
        $projects = array();

        foreach ($this->getProjects($this->settings['feedUrl']) as $project) {
            $projects[] = array(
                'name'      => $project,
                'checked'   => in_array($project, $this->settings['projectsToShow']),
            );
        }

        return array_merge($this->settings, array('projects' => $projects));
    }

    /**
     * Method saves user specified settings.
     *
     * @throws  Exception
     *
     * @return  array   Stored settings
     */
    public function handleRequestSaveSettings()
    {
        // Fetch form data
        $formData = $this->request->get('data', array());

        // Specify required form data
        $requiredFormData = array(
            'feedUrl',
            'buildsPerRow',
            'refreshInterval',
            'projectsToShow',
        );

        $field = 'generic';

        try {
            $settings = array();

            // Iterate request setting data
            foreach ($requiredFormData as $field) {
                // Required field not set
                if (!isset($formData[$field])) {
                    throw new Exception("This field is required.");
                }

                // Specify field value and used validate -method.
                $value = $formData[$field];
                $method = 'validate' . ucfirst($field);

                // Validate method doesn't exists
                if (!method_exists($this, $method)) {
                    $message = sprintf(
                        "Validate method '%s' not found.",
                        $method
                    );

                    throw new Exception($message);
                }

                // Validate current field data
                $settings[$field] = call_user_func(array($this, $method), $value);
            }

            // Store settings.
            $this->storeSettings($settings);

            return $this->settings;
        } catch (Exception $error) {
            header("HTTP/1.0 400 Bad Request");

            return array(
                'element'   => $field,
                'message'   => $error->getMessage(),
            );
        }
    }

    /**
     * Method fetches specified feed projects and returns them as an array.
     *
     * @return  array   Stored settings
     */
    public function handleRequestGetFeedProjects()
    {
        // Specify current feed url
        $feedUrl = (string)$this->request->get('url', '');

        try {
            // Validate feed url and store its projects into cache
            $this->validateFeedUrl($feedUrl);

            // Initialize output projects
            $projects = array();

            // Iterate projects from specified feed url
            foreach ($this->projectCache as $project) {
                $projects[] = array(
                    'name'      => $project,
                    'checked'   => in_array($project, $this->settings['projectsToShow']),
                );
            }

            return array('projects' => $projects);
        } catch (Exception $error) {
            header("HTTP/1.0 400 Bad Request");

            return array(
                'message'   => $error->getMessage(),
            );
        }
    }

    /**
     * Method removes single project from current view
     *
     * @return  array
     */
    protected function handleRequestRemoveProject()
    {
        // Determine project that user want to remove
        $project = (string)$this->request->get('project');

        // Fetch settings from session
        $settings = $this->request->getSession('settings', array());

        // Determine project key to remove
        $keyToRemove = array_search($project, $settings['projectsToShow']);

        // Project key founded, so remove it and reset projects array
        if ($keyToRemove !== false) {
            unset($settings['projectsToShow'][$keyToRemove]);

            $settings['projectsToShow'] = array_values($settings['projectsToShow']);
        }

        // Store current settings
        $this->storeSettings($settings);

        return $this->settings;
    }

    /**
     * Method validates specified feed url. If url is not valid method will
     * throw an exception.
     *
     * @throws  Exception
     *
     * @param   string  $url    Feed url to check
     *
     * @return  string          Valid feed url
     */
    protected function validateFeedUrl($url)
    {
        // Fetch projects from feed url
        $this->projectCache = $this->getProjects($url);

        return $url;
    }

    /**
     * Method validates build count per row value.
     *
     * @throws  Exception
     *
     * @param   integer $count  Builds per row value
     *
     * @return  integer         Valid count of builds per row
     */
    protected function validateBuildsPerRow($count)
    {
        $count = (int)$count;

        if ($count < 1 || $count > 4) {
            throw new Exception("Invalid build count for row. Value must be between 1-4.");
        }

        return $count;
    }

    /**
     * Method validates refresh interval value.
     *
     * @throws  Exception
     *
     * @param   integer $interval   Refresh interval
     *
     * @return  integer             Valid refresh interval
     */
    protected function validateRefreshInterval($interval)
    {
        $interval = (int)$interval;

        if ($interval < 1 || $interval > 30) {
            throw new Exception("Invalid refresh interval. Value must be between 1-30.");
        }

        return $interval;
    }

    /**
     * Method validates selected projects.
     *
     * @throws  Exception
     *
     * @param   array   $projects   Selected projects
     *
     * @return  array               Validated projects
     */
    protected function validateProjectsToShow(array $projects)
    {
        // No projects selected
        if (empty($projects)) {
            throw new Exception("No projects selected. Please select at least one project to show.");
        }

        // Get difference between selected and actual projects.
        $diff = array_diff($projects, $this->projectCache);

        // User has selected invalid/unknown projects
        if (!empty($diff)) {
            $message = sprintf(
                "Following projects are not founded in specified feed url: '%s'",
                implode("', '", $diff)
            );

            throw new Exception($message);
        }

        return $projects;
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
        $settings = array(); // $this->request->getSession('settings', array());

        // Settings are not yet set, so make default settings
        if (empty($settings)) {
            $feedUrl = $this->settings['feedUrl'];
            $buildsPerRow = $this->settings['buildsPerRow'];
            $refreshInterval = $this->settings['refreshInterval'];

            // Try to read local configuration file
            try {
                $config = Config::readIni('config.ini');

                $feedUrl = isset($config['feedUrl']) ? $config['feedUrl'] : $feedUrl;
                $buildsPerRow = isset($config['buildsPerRow']) ? $config['buildsPerRow'] : $buildsPerRow;
                $refreshInterval = isset($config['refreshInterval']) ? $config['refreshInterval'] : $refreshInterval;
            } catch (\Exception $error) {
                // Silently suppress this error
            }

            $settings = array(
                'feedUrl'           => $feedUrl,
                'buildsPerRow'      => $buildsPerRow,
                'refreshInterval'   => $refreshInterval,
                'projectsToShow'    => $this->getProjects($feedUrl),
            );
        }

        // Sore setting data to session
        $this->storeSettings($settings);
    }

    /**
     * Method stores current setting data to user session.
     *
     * @throws  Exception
     *
     * @param   array $settings
     *
     * @return  void
     */
    private function storeSettings(array $settings)
    {
        // Determine used build class
        switch ((int)$settings['buildsPerRow']) {
            case 1:
                $class = 'span12';
                break;
            case 2:
                $class = 'span6';
                break;
            case 3:
                $class = 'span4';
                break;
            case 4:
                $class = 'span3';
                break;
            default:
                throw new Exception("Invalid number of projects per row.");
                break;
        }

        // Store used build class
        $settings['buildClass'] = $class;

        // Store settings locally
        $this->settings = $settings;

        // Store setting data to session
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
        libxml_use_internal_errors(true);

        // Load feed url and convert content to SimpleXML object
        $xml = simplexml_load_string($this->handleRequestGetFeed($feedUrl, true));

        if (!$xml) {
            $message = "Failed loading XML.";

            foreach (libxml_get_errors() as $index => $error) {
                $message .= "<br /><strong>" . ($index + 1) . ":</strong> " . $error->message;
            }

            throw new Exception($message);
        }

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

        if (empty($output)) {
            throw new Exception("No feed items found.");
        }

        return $output;
    }
}