<?php
/**
 * \php\Util\Logger.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Util
 * @category    Log
 */
namespace phpUnderControlBuildMonitor\Util;

use phpUnderControlBuildMonitor\Core\Request;

/**
 * This class contains generic logger.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Util
 * @category    Log
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Logger implements Interfaces\Logger
{
    /**
     * Exception to write log.
     *
     * @var \Exception
     */
    private $exception;

    /**
     * Construction of the class.
     *
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;

        self::write($this->makeMessage());
    }

    /**
     * Actual log write method.
     *
     * @param   string  $message    Log message
     * @param   int     $type       Log type
     *
     * @return  void
     */
    public static function write($message, $type = LOG_ERR)
    {
        // Get request object
        $request = Request::getInstance();

        // Specify extra log information
        $information = array(
            'IP: ' . $request->getClientIp(),
            'URL: ' . $request->getCurrentUrl(),
            'Agent: ' . $request->getServer('HTTP_USER_AGENT'),
        );

        openlog("phpUnderControlBuildMonitor", LOG_PID | LOG_PERROR, LOG_LOCAL0);

        syslog($type, $message . " " . implode(' ', $information));

        closelog();
    }

    /**
     * Method makes JSON string from current exception for log writing.
     *
     * @return string
     */
    private function makeMessage()
    {
        $data = array(
            'message'   => $this->exception->getMessage(),
            'code'      => $this->exception->getCode(),
            'file'      => $this->exception->getFile(),
            'line'      => $this->exception->getLine(),
        );

        return JSON::encode($data);
    }
}
