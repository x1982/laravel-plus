<?php

namespace Landers\LaravelPlus\Constraints;

use Illuminate\Console\Command as LaravelCommand;

abstract class Command extends LaravelCommand
{
    /**
     * 是否空
     * @param $value
     * @return bool
     */
    private function isNull($value)
    {
        return is_null($value) || $value === '' || $value === false;
    }

    /**
     * 模拟多选
     * @param string $propmt
     * @param array $values
     * @return array
     */
    protected function checks(string $propmt, array $values)
    {
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
     * @param  string $key
     * @return string|array
     */
    public function option($key = null, $default = null)
    {
        if (!$this->input) {
            return $default;
        } else {
            return parent::option($key);
        }
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string $key
     * @param  null|mixed $default
     * @return string|array
     */
    public function argument($key = null, $default = null)
    {
        if (!$this->input) {
            return $default;
        } else {
            return parent::argument($key);
        }
    }

    /**
     * @param \Closure $callback
     * @param string $prompt
     */
    protected function needAsk(\Closure $callback, string $prompt, $default = null)
    {
        $value = $callback();
        if ($this->isNull($value)) {
            echo PHP_EOL;
            $value = $this->ask($prompt, $default);
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
        if ($this->isNull($value)) {
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
        if ($this->isNull($value)) {
            echo PHP_EOL;
            $value = $this->confirm($prompt);
        } else {
            $value = (bool)$value;
        }
        return $value;
    }

    protected function needChecks(\Closure $callback, string $propmt, array $values)
    {
        $results = $callback();
        if ($this->isNull($results)) {
            echo PHP_EOL;
            $results = $this->checks($propmt, $values);
        } else {
            $results = explode(',', $results);
        }
        return $results;
    }
}