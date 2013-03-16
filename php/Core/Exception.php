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

/**
 * Exception -class
 *
 * Generic exception class for HomeAI -software. All Exception classes
 * must extend this class.
 *
 * @package     phpUnderControlBuildMonitor
 * @subpackage  Core
 * @category    Exception
 *
 * @author      Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class Exception extends \Exception
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
            'message'   => $this->getMessage(),
            'code'      => $this->getCode(),
            'file'      => $this->getFile(),
            'line'      => $this->getLine(),
        );

        header("HTTP/1.0 400 Bad Request");

        JSON::makeHeaders();

        echo JSON::encode(array('error' => $data));

        exit(0);
    }
}
