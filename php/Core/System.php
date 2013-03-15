<?php
/**
 * \php\Core\Init.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Init
 * @category    Initializer
 */
namespace phpUnderControlBuildMonitor\Core;

use phpUnderControlBuildMonitor\Util\UUID;

/**
 * This class process phpUnderControlBuildMonitor system initialize functions.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Init
 * @category    Initializer
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class System implements Interfaces\System
{
    /**
     * Singleton class variable.
     *
     * @access  protected
     * @var     \phpUnderControlBuildMonitor\Core\System
     */
    protected static $instance = null;

    /**#@+
     * Base site variables to use. These are not valid IF actual content
     * is empty.
     *
     * @var string
     */
    public static $baseHref = '';
    public static $csrfToken = '';
    /**#@-*/

    /**
     * Current request object.
     *
     * @var \phpUnderControlBuildMonitor\Core\Request
     */
    protected $request;

    /**
     * Used system components and properties.
     *
     * @var     array
     */
    private $components = array(
        'session'       => array(
            'property'  => false,
            'method'    => 'initSession'
        ),
        'request'       => array(
            'property'  => false,
            'method'    => 'initRequest'
        ),
        'variables'     => array(
            'property'  => false,
            'method'    => 'initVariables'
        ),
    );

    /**
     * Construction of the class.
     */
    protected function __construct()
    {
        $this->initializeComponents();
    }

    /**
     * Method initialize Session -class to use.
     *
     * @access  public
     * @static
     *
     * @return  \phpUnderControlBuildMonitor\Core\System
     */
    public static function initialize()
    {
        if (is_null(System::$instance)) {
            System::$instance = new System;
        }

        return System::$instance;
    }

    /**
     * Method initializes all phpUnderControlBuildMonitor specified components.
     */
    protected function initializeComponents()
    {
        array_walk($this->components, array($this, 'initializeComponent'));
    }

    /**
     * Method initializes single phpUnderControlBuildMonitor system init component
     * to use. Note that all system components has fixed definitions.
     *
     * @throws  Exception
     *
     * @param   array   $data
     * @param   string  $name
     */
    protected function initializeComponent(array $data, $name)
    {
        // Determine used class + method
        $class = $this->getClassName($data, $name);
        $method = $this->getMethodName($data, $name);

        if (isset($data['property']) && $data['property'] === true) {
            if (is_null($class)) {
                $message = sprintf(
                    "Invalid configuration for system component '%s', no class specified.",
                    $name
                );

                throw new Exception($message);
            }

            $this->{$name} = new $class();
        } else {
            if (!is_null($class)) {
                new $class();
            } elseif (!is_null($method)) {
                call_user_func(array($this, $method));
            } else {
                $message = sprintf(
                    "Invalid configuration for system component '%s', no class or method specified.",
                    $name
                );

                throw new Exception($message);
            }
        }
    }

    /**
     * Method returns component class name.
     *
     * @throws  Exception
     *
     * @param   array   $data   Component data
     * @param   string  $name   Name of the component
     *
     * @return  null|string
     */
    protected function getClassName(array $data, $name)
    {
        if (!isset($data['class'])) {
            return null;
        } else {
            // Specify name of the used component class.
            $class = "\\phpUnderControlBuildMonitor\\Core\\System\\". $data['class'];

            // Class doesn't exist => fatal error
            if (!class_exists($class)) {
                $message = sprintf(
                    "Specified system component '%s' class '%s' not found",
                    $name,
                    $class
                );

                throw new Exception($message);
            }
        }

        return $class;
    }

    /**
     * Method returns component method name. No that this method must be in
     * this class scope.
     *
     * @throws Exception
     *
     * @param   array   $data   Component data
     * @param   string  $name   Name of the component
     *
     * @return  string|null
     */
    protected function getMethodName(array $data, $name)
    {
        $method = (isset($data['method'])) ? $data['method'] : null;

        if (is_null($method)) {
            return null;
        }

        if (!method_exists($this, $method)) {
            $message = sprintf(
                "Invalid configuration for system component '%s', method '%s' doesn't exists.",
                $name,
                $method
            );

            throw new Exception($message);
        }

        return $method;
    }

    /**
     * Method initializes phpUnderControlBuildMonitor sessions.
     *
     * @return  void
     */
    protected function initSession()
    {
        session_start();
    }

    /**
     * Method initializes Request object to use
     *
     * @return  void
     */
    protected function initRequest()
    {
        $this->request = Request::getInstance();
    }

    /**
     * Method initializes common phpUnderControlBuildMonitor variables to use.
     *
     * @return  void
     */
    protected function initVariables()
    {
        // Store site base href url
        self::$baseHref = $this->request->getBaseHref(false, true);

        // Try to fetch token from session
        $token = $this->request->getSession('token', null);

        // Token not found from session
        if (is_null($token)) {
            // Generate new random token
            $token = sha1(UUID::v4());

            // Store generated token to session
            $this->request->setSession('token', $token);
        }

        // Store CSRF token
        self::$csrfToken = $token;
    }
}
