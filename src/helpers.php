<?php
/**
 * 从类名中提取命名空间
 */
function extract_namespace($class_name)
{
    $arr = explode('\\', $class_name);
    if ( count($arr) > 1) {
        unset($arr[count($arr) - 1]);
        return implode('\\', $arr);
    } else {
        throw new \Exception('提取命名空间错误');
    }
}

/**
 * 从类名中提取模块名称
 */
function extract_module_name($class_name)
{
    $arr = explode('\\', $class_name);
    if ( count($arr) > 1) {
        return $arr[count($arr) - 2];
    } else {
        throw new \Exception('提取模块标识错误');
    }
}

if (!function_exists('instantiate_api_result')) {
    function instantiate_api_result($args = NULL) {
        $class = \Landers\Substrate2\Classes\ApiResult::class;
        return $args ? $class::makeBy($args) : $class::make();
    }
}
if (!function_exists('build_api_by_result')) {
    function build_api_by_result($result) {
        return response()->json($result->toArray(), $result->status_code);
    }
}
if (!function_exists('build_api_result')) {
    function build_api_result() {
        $class = \Landers\Substrate2\Classes\ApiResult::class;
        $result = NULL;
        if (func_num_args() == 1 ) {
            $result = func_get_arg(0);
            if ( !($result instanceof $class) ) {
                $result = NULL;
            }
        }
        $result or $result = instantiate_api_result(func_get_args());
        return build_api_by_result($result);
    }
}
if (!function_exists('build_api_invalid')) {
    function build_api_invalid() {
        $result = instantiate_api_result()->invalid();
        return build_api_by_result($result);
    }
}
if (!function_exists('build_api_message')) {
    function build_api_message($message) {
        $result = instantiate_api_result()->message($message);
        return build_api_by_result($result);
    }
}
if (!function_exists('build_api_data')) {
    function build_api_data($data, $message = null) {
        $result = instantiate_api_result()->data($data, $message);
        return build_api_by_result($result);
    }
}
if (!function_exists('build_api_bool')) {
    function build_api_bool($bool, $message = NULL) {
        $result = instantiate_api_result()->bool($bool, $message);
        return build_api_by_result($result);
    }
}
if (!function_exists('build_api_busy')) {
    function build_api_busy() {
        $result = instantiate_api_result()->busy();
        $result->status_code = 500;
        return build_api_by_result($result);
    }
}
if (!function_exists('build_api_errors')) {
    function build_api_errors($errors) {
        $result = instantiate_api_result()->errors($errors);
        $result->status_code = 500;
        return build_api_by_result($result);
    }
}
if (!function_exists('output_api_result')) {
    function output_api_result(){
        $result = build_api_by_result(func_get_args());
        $result->output();
    }
}

if (!function_exists('build_jsonp_by_result')) {
    function build_jsonp_by_result($callback, $result) {
        $status_code = $result->status_code;
        $result = json_encode($result->toArray(), JSON_UNESCAPED_UNICODE);
        $result = "$callback('$result');";
        return response($result, $status_code);
    }
}
if (!function_exists('build_jsonp_result')) {
    function build_jsonp_result() {
        $callback = func_get_arg(0);
        $args = func_get_args();
        unset($args[0]); $args = array_values($args);
        $result = instantiate_api_result($args);
        return build_jsonp_by_result($callback, $result);
    }
}
if (!function_exists('build_json_invalid')) {
    function build_json_invalid($callback) {
        $result = instantiate_api_result()->invalid();
        return build_jsonp_by_result($callback, $result);
    }
}
if (!function_exists('build_json_message')) {
    function build_json_message($callback, $message) {
        $result = instantiate_api_result()->message($message);
        return build_jsonp_by_result($callback, $result);
    }
}
if (!function_exists('build_jsonp_bool')) {
    function build_jsonp_bool($callback, $bool, $message = NULL) {
        $result = instantiate_api_result()->bool($bool, $message);
        return build_jsonp_by_result($callback, $result);
    }
}
if (!function_exists('build_jsonp_busy')) {
    function build_jsonp_busy($callback) {
        $result = instantiate_api_result()->busy();
        $result->status_code = 500;
        return build_jsonp_by_result($callback, $result);
    }
}
if (!function_exists('build_jsonp_errors')) {
    function build_jsonp_errors($callback, $errors) {
        $result = instantiate_api_result()->errors($errors);
        $result->status_code = 500;
        return build_jsonp_by_result($callback, $result);
    }
}
if (!function_exists('output_jsonp_result')) {
    function output_jsonp_result(){
        $class = \Landers\Substrate2\Classes\ApiResult::class;
        $callback = func_get_arg(0);
        $args = func_get_args();
        unset($args[0]); $args = array_values($args);
        $result = build_api_by_result(func_get_args());
        $response = build_jsonp_by_result($callback, $result);
        $response->send();
        exit();
    }
}

/**
 * 压入任务到队列
 * @param  [type] $job        [description]
 * @param  [type] $queue_name [description]
 * @return [type]             [description]
 */
if (!function_exists('queuePush')) {
    function queuePush($job, $queue_name) {
        $job->onQueue($queue_name);
        return app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($job);
    }
}

/**
 * 追踪到日志
 */
if (!function_exists('trace')) {
    function trace()
    {
        $args = func_get_args();
        $title = array_get($args, '0');
        $content = (array)array_get($args, '1');
        \Log::info($title, $content);
    }
}

/**
 * @param string $key
 * @param null $default
 * @return \Illuminate\Config\Repository|mixed
 */
function read_config( string $key = '', $default = null )
{
    if ( app()->has('config') ) {
        return config( $key, $default );
    } else {
        $file = base_path('config') . '/' . $key . '.php';
        return include($file);
    }
}

/**
 * 监听Sql
 */
function listen_sql(\Closure $callback)
{
    app('db')->listen(function ($event) use ($callback) {
        $sql = $event->sql;
        $time = $event->time;
        $bindings = $event->bindings;
        if (strpos($sql, "?") !== false) {
            array_unshift($bindings, str_replace(["%", "?"], ["%%", "'%s'"], $sql));
            $sql = call_user_func_array("sprintf", $bindings);
        }
        $callback($sql, $time);
    });
}