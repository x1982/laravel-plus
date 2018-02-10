<?php
namespace Landers\LaravelPlus\Supports\Kuaidi100;

class Kuaidi100Service
{
    use \Landers\Substrate2\Traits\MakeInstanceTrait;

    use \Landers\LaravelPlus\Traits\HttpRequestTrait;

    const COURIERS = [
        'zhongtong' => '中通快递',
    ];

    /**
     * 用于查询单号所属的快递公司
     */
    const URL_QUERY_TYPE = 'http://www.kuaidi100.com/autonumber/auto?num=%s';

    /**
     * 用于查询单号的结果
     */
    const URL_QUERY_RESULT = 'http://www.kuaidi100.com/query?type=%s&postid=%s&id=1&valicode=&temp=%s';


    public function __construct()
    {

    }

    public function query(string $courier_no)
    {
        $result = null;

        $list = $this->queryCourier($courier_no);
        if ($list) {
            foreach($list as $item){
                $type = array_get($item, 'comCode');
                $result = $this->queryBy($type, $courier_no);
                if ( $result ) break;
            }
        }

        if ($result) {
            $result = $this->formatResult($result);
        }

        return $result;
    }

    /**
     * 根据快递单号查询快递公司
     * @param string $courier_no
     */
    public function queryCourier(string $courier_no)
    {
        $url = sprintf(self::URL_QUERY_TYPE, $courier_no);
        return json_decode($this->requestGet($url), true);
    }

    /**
     * 根据快递类型和单号查询
     * @param string $type
     * @param string $courier_no
     * @return bool|mixed
     * @throws \Exception
     */
    private function queryBy(string $type, string $courier_no)
    {
        $url = sprintf(self::URL_QUERY_RESULT, $type, $courier_no, time());
        $result = json_decode($this->requestGet($url), true);
        if ( !$result ) {
            throw new \Exception('查询错误');
        }

        if ($result['status'] != 200) return false;

        return $result;
    }

    /**
     * 格式化结果
     * @param $result
     * @return array
     */
    private function formatResult($result)
    {
        $list = $result['data'];
        foreach ($list as &$item){
            $item = [
                'datetime' => $item['time'],
                'content' => $item['context'],
                'location' => $item['location'],
            ];
            unset($item);
        }

        $result = [
            'courier_id' => $result['com'],
            'courier' => array_get(self::COURIERS, $result['com'], $result['com']),
            'courier_no' => $result['nu'],
            'path' => $list
        ];

        return $result;
    }
}