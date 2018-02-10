<?php

use Landers\LaravelAms\Constraints\Tests\BaseTestCase;
use Landers\LaravelPlus\Supports\Kuaidi100\Kuaidi100Service;

class Kuaidi100Test extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
    }


    /**
     * @test
     */
    public function testQueryType()
    {
        $no = '472503282097';
        $result = Kuaidi100Service::make()->query($no);
        $this->assertArrayHasKey('courier', $result);
        $this->assertArrayHasKey('path', $result);

        $no = '611021231958194';
        $result = Kuaidi100Service::make()->query($no);
        $this->assertFalse($result);
    }
}
