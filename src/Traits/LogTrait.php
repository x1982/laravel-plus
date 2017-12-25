<?php
namespace Landers\LaravelPlus\Traits;

use Illuminate\Support\Facades\Log;
use Landers\Substrate2\Utils\CliColorize;

trait LogTrait
{

    /**
     * @param $message
     * @return string
     */
    private function logConvertMessage($color, $message)
    {
        if ( is_object($message) || is_array($message)) {
            $message = json_encode((array)$message);
        }
        return call_user_func_array([CliColorize::class, $color], [$message]);
    }

    private function logBuildOuput( )
    {
        $args = func_get_args();
        foreach ($args as &$arg){
            if ($arg) {
                if (is_object($arg) || is_array($arg)) {
                    $arg = json_encode((array)$arg);
                }
            } else {
                $arg = '';
            }
            unset($arg);
        }

        return implode(' ', $args);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logEmergency( $message, $context = array())
    {
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logAlert($message, $context = array())
    {
        $message = $this->logConvertMessage('yellow', $message);
        $message = $this->logBuildOuput($message, $context);
        Log::warning($message);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logCritical($message, $context = array())
    {
        $message = $this->logConvertMessage('red', $message);
        $message = $this->logBuildOuput($message, $context);
        Log::warning($message);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logError($message, $context = array())
    {
        $message = $this->logConvertMessage('error', $message);
        $message = $this->logBuildOuput($message, $context);
        Log::error($message);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logWarn($message, $context = array())
    {
        $message = $this->logConvertMessage('warn', $message);
        $message = $this->logBuildOuput($message, $context);
        Log::warning($message);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logNotice($message, $context = array())
    {
        $message = $this->logConvertMessage('', $message);
        $message = $this->logBuildOuput($message, $context);
        Log::warning($message);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logInfo($message, $context = array())
    {
        $message = $this->logConvertMessage('info', $message);
        $message = $this->logBuildOuput($message, $context);
        Log::info($message);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function logDebug($message, $context = array())
    {

    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array|string  $context
     *
     * @return void
     */
    public function log($message, $level = 'notice', $context = array())
    {
        $message = $this->logConvertMessage($level, $message);
        $message = $this->logBuildOuput($message, $context);
        Log::log($level, $message);
    }
}