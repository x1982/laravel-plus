<?php
namespace Landers\LaravelPlus\Constraints;

use Illuminate\Console\Command as LaravelCommand;

abstract class Command extends LaravelCommand
{
    protected function checks(string $propmt, array $values)
    {
        echo PHP_EOL;
        $results = [];

        foreach ($values as $value) {
            $message = sprintf($propmt, $value);
            if ($this->confirm($message)) {
                $results[] = $value;
            }
        }
        return $results;
    }

    /**
     * Get the value of a command option.
     *
     * @param  string  $key
     * @return string|array
     */
    public function option($key = null, $default = null)
    {
        if ( !$this->input ) {
            return $default;
        } else {
            $ret = parent::option( $key );
            return empty($ret) ? $default : $ret;
        }
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string  $key
     * @param  null|mixed $default
     * @return string|array
     */
    public function argument($key = null, $default = null)
    {
        if ( !$this->input ) {
            return $default;
        } else {
            $ret = parent::argument( $key );
            return empty($ret) ? $default : $ret;
        }
    }

    /**
     * @param \Closure $callback
     * @param string $prompt
     */
    protected function needAsk(\Closure $callback, string $prompt)
    {
        $value = $callback();
        if ( is_null($value) || $value === '') {
            echo PHP_EOL;
            $value = $this->ask($prompt);
        }

        return $value;
    }

    /**
     * @param \Closure $callback
     * @param string $prompt
     * @param array $values
     * @return mixed|string
     */
    protected function needChoice(\Closure $callback, string $prompt, array $values)
    {
        $value = $callback();
        if ( is_null($value) || $value === '') {
            echo PHP_EOL;
            $value = $this->choice($prompt, $values);
        }

        return $value;
    }

    /**
     * @param \Closure $callback
     * @param string $prompt
     * @return bool|mixed
     */
    protected function needConfirm(\Closure $callback, string $prompt)
    {
        $value = $callback();
        if ( is_null($value) || $value === '') {
            $value = $this->confirm($prompt);
        } else {
            $value = (bool)$value;
        }
        return $value;
    }

    protected function needChecks(\Closure $callback, string $propmt, array $values)
    {
        $results = $callback();
        if ( is_null($results) || $results === '') {
            $results = $this->checks($propmt, $values);
        } else {
            $results = explode(',', $results);
        }
        return $results;
    }
}