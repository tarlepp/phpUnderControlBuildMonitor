<?php
/**
 * \php\Core\Exception.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Exception
 */
namespace phpUnderControlBuildMonitor\Core;

use phpUnderControlBuildMonitor\Util\JSON;
use phpUnderControlBuildMonitor\Util\Logger;

/**
 * Exception -class
 *
 * Generic exception class for phpUnderControlBuildMonitor -software. All Exception classes
 * must extend this class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Exception
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Exception extends \Exception implements Interfaces\Exception
{
    /**
     * Write error log or not.
     *
     * @var bool
     */
    protected $writeLog = true;

    /**
     * Construction of main exception class.
     *
     * @param   string      $message    Exception message
     * @param   integer     $code       Error code
     * @param   \Exception  $previous   Previous exception
     *
     * @return  \phpUnderControlBuildMonitor\Core\Exception
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if ($this->writeLog) {
            new Logger($this);
        }
    }

    /**
     * Method makes JSON exception.
     *
     * @return  void
     */
    public function makeJsonResponse()
    {
        self::makeJsonError($this);
    }

    /**
     * Common method to convert current exception to "standard" JSON
     * error which is easily be usable in javascript.
     *
     * @param   \Exception  $error  Exception where to make error
     *
     * @return  void
     */
    public static function makeJsonError(\Exception $error)
    {
        $data = array(
            'message'   => $error->getMessage(),
            'code'      => $error->getCode(),
            'file'      => str_replace(System::$basePath, DIRECTORY_SEPARATOR, $error->getFile()),
            'line'      => $error->getLine(),
            'trace'     => str_replace(System::$basePath, DIRECTORY_SEPARATOR, $error->getTraceAsString()),
        );

        header("HTTP/1.0 400 Bad Request");

        JSON::makeHeaders();

        echo JSON::encode(array('error' => $data));

        exit(0);
    }
}
