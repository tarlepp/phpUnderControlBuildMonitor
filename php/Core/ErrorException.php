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
use phpUnderControlBuildMonitor\Util\Logger;

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
class ErrorException extends \ErrorException implements Interfaces\Exception
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
        Exception::makeJsonError($this);
    }
}
