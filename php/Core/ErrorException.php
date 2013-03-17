<?php
/**
 * \php\Core\ErrorException.php
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Exception
 */
namespace phpUnderControlBuildMonitor\Core;

use phpUnderControlBuildMonitor\Util\JSON;

/**
 * ErrorException -class
 *
 * Generic error exception class for phpUnderControlBuildMonitor -software.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Exception
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class ErrorException extends \ErrorException
{
    /**
     * Write error log or not.
     *
     * @var bool
     */
    protected $writeLog = true;

    /**
     * Construction of main error exception class.
     *
     * @link    http://php.net/manual/en/errorexception.construct.php
     *
     * @param   string      $message    [optional] The Exception message to throw.
     * @param   int         $code       [optional] The Exception code.
     * @param   int         $severity   [optional] The severity level of the exception.
     * @param   string      $filename   [optional] The filename where the exception is thrown.
     * @param   int         $lineNumber [optional] The line number where the exception is thrown.
     * @param   \Exception  $previous   [optional] The previous exception used for the exception chaining.
     *
     * @return  \phpUnderControlBuildMonitor\Core\ErrorException
     */
    public function __construct(
        $message = "",
        $code = 0,
        $severity = 1,
        $filename = __FILE__,
        $lineNumber = __LINE__,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $severity, $filename, $lineNumber, $previous);

        if ($this->writeLog) {
            // TODO: implement log write
        }
    }

    /**
     * Common method to convert current exception to "standard" JSON
     * error which is easily be usable in javascript.
     *
     * @return  void
     */
    public function makeJsonResponse()
    {
        $data = array(
            'message' => $this->getMessage(),
            'code'    => $this->getCode(),
            'file'    => str_replace(System::$basePath, DIRECTORY_SEPARATOR, $this->getFile()),
            'line'    => $this->getLine(),
            'trace'   => str_replace(System::$basePath, DIRECTORY_SEPARATOR, $this->getTraceAsString()),
        );

        header("HTTP/1.0 400 Bad Request");

        JSON::makeHeaders();

        echo JSON::encode(array('error' => $data));

        exit(0);
    }
}
