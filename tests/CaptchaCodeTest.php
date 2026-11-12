<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 11:45 下午.
 */

namespace HughCube\Laravel\CaptchaCode\Tests;

use HughCube\Laravel\CaptchaCode\CaptchaCode;
use HughCube\Laravel\CaptchaCode\Manager;

class CaptchaCodeTest extends TestCase
{
    public function testIsFacade()
    {
        $this->assertInstanceOf(Manager::class, CaptchaCode::getFacadeRoot());
    }

    public function testGetOrRand()
    {
        $key = md5(serialize([__METHOD__, mt_rand()]));
        $code = CaptchaCode::getOrRand($key);

        $this->assertNotNull($code);
        $this->assertSame($code, CaptchaCode::getOrRand($key));
    }

    public function testRegisterServiceProvider()
    {
        $reflection = new \ReflectionClass(CaptchaCode::class);
        $method = $reflection->getMethod('registerServiceProvider');
        $method->setAccessible(true);
        $method->invoke(null, $this->app);

        $this->assertTrue(true);
    }
}
