<?php

use Landers\LaravelAms\Constraints\Tests\BaseTestCase;
use Landers\LaravelPlus\Supports\Kuaidi100\Kuaidi100Service;
use Tests\TestCase;

class Kuaidi100Test extends TestCase
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
        $no = '611021231958194';
        $no = '472503282097';
        dp(Kuaidi100Service::make()->query($no));
    }
}
