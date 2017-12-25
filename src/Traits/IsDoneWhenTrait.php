<?php
namespace Landers\LaravelAms\Traits;

use Illuminate\Support\Facades\Cache;

Trait IsDoneWhenTrait {

    private function isDoneByUniqueKey(array $args, $mix_time) {
        if (!$args) {
            throw new \Exception('至少有一个参数！');
        }

        $a = debug_backtrace(0)[2];
        $args[] = $a['class'] . '::' . $a['function'];

        $args[] = $mix_time;

        $unique = md5(serialize($args));

        if (Cache::get($unique)) {
            return true;
        } else {
            Cache::forever($unique, true);
            return false;
        }
    }

    /**
     * 今天是否已完成
     * @return bool
     * @throws \Exception
     */
    protected function isDoneToday()
    {
        $args = func_get_args();
        $mix_time = date('Y-m-d');
        return $this->isDoneByUniqueKey($args, $mix_time);
    }

    /**
     * 当前小时是否已完成
     * @return bool
     * @throws \Exception
     */
    protected function isDoneThisHour()
    {
        $args = func_get_args();
        $mix_time = date('Y-m-d H');
        return $this->isDoneByUniqueKey($args, $mix_time);
    }

    /**
     * 本月是否已完成
     * @return bool
     * @throws \Exception
     */
    protected function isDoneThisMonth()
    {
        $args = func_get_args();
        $mix_time = date('Y-m');
        return $this->isDoneByUniqueKey($args, $mix_time);
    }
}
