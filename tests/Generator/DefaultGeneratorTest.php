<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 8:49 下午.
 */

namespace HughCube\Laravel\CaptchaCode\Tests\Generator;

use HughCube\Laravel\CaptchaCode\Generator\DefaultGenerator;
use HughCube\Laravel\CaptchaCode\Generator\Generator;
use HughCube\Laravel\CaptchaCode\Tests\TestCase;

class DefaultGeneratorTest extends TestCase
{
    public function testIsGenerator()
    {
        $generator = $this->getGenerator();
        $this->assertInstanceOf(Generator::class, $generator);
    }

    public function testGet()
    {
        for ($i = 0; $i < 1000; $i++) {
            $generator = $this->getGenerator($i);
            $this->assertSame(strlen($generator->get()), $i);
        }
    }

    public function testNonZeroLeading()
    {
        $generator = new DefaultGenerator(6, '0123456789', true);
        $this->assertTrue($generator->isNonZeroLeading());

        for ($i = 0; $i < 1000; $i++) {
            $code = $generator->get();
            $this->assertSame(6, strlen($code));
            $this->assertNotSame('0', $code[0]);
        }

        // 测试关闭时允许 0 开头
        $disabledGenerator = new DefaultGenerator(6, '0123456789', false);
        $this->assertFalse($disabledGenerator->isNonZeroLeading());
        $hasZero = false;
        for ($i = 0; $i < 200; $i++) {
            if ('0' === $disabledGenerator->get()[0]) {
                $hasZero = true;
                break;
            }
        }
        $this->assertTrue($hasZero);
    }

    protected function getGenerator($length = null): DefaultGenerator
    {
        return new DefaultGenerator($length, null);
    }
}
