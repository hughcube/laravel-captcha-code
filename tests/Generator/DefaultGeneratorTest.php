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

        // 测试 withNonZeroLeading
        $this->assertSame($disabledGenerator, $disabledGenerator->withNonZeroLeading(true));
        $this->assertTrue($disabledGenerator->isNonZeroLeading());
        $this->assertSame($disabledGenerator, $disabledGenerator->withNonZeroLeading(false));
        $this->assertFalse($disabledGenerator->isNonZeroLeading());
    }

    public function testNonZeroLeadingDistribution()
    {
        $generator = new DefaultGenerator(6, '0123456789', true);
        $firstDigitZeroCount = 0;
        $otherDigitsZeroCount = 0;

        for ($i = 0; $i < 3000; $i++) {
            $code = $generator->get();
            $this->assertSame(6, strlen($code));
            if ('0' === $code[0]) {
                $firstDigitZeroCount++;
            }
            for ($j = 1; $j < 6; $j++) {
                if ('0' === $code[$j]) {
                    $otherDigitsZeroCount++;
                }
            }
        }

        $this->assertSame(0, $firstDigitZeroCount, '首位出现 0 次数为 0');
        $this->assertGreaterThan(0, $otherDigitsZeroCount, '后续位应正常包含字符 0');
    }

    public function testNonZeroLeadingWithCustomString()
    {
        // 包含 0 与字母的自定义字符集
        $gen = new DefaultGenerator(4, '0ABC', true);
        for ($i = 0; $i < 200; $i++) {
            $code = $gen->get();
            $this->assertNotSame('0', $code[0]);
            $this->assertContains($code[0], ['A', 'B', 'C']);
        }

        // 不含 0 的字符集
        $genNoZero = new DefaultGenerator(4, '12345', true);
        for ($i = 0; $i < 100; $i++) {
            $code = $genNoZero->get();
            $this->assertNotSame('0', $code[0]);
        }

        // 极端字符集全为 0
        $genAllZero = new DefaultGenerator(4, '0000', true);
        $code = $genAllZero->get();
        $this->assertSame('0000', $code);
    }

    public function testNonZeroLeadingLengthBoundaries()
    {
        // length = 0
        $gen0 = new DefaultGenerator(0, '0123456789', true);
        $this->assertSame('', $gen0->get());

        // length = 1
        $gen1 = new DefaultGenerator(1, '0123456789', true);
        $code1 = $gen1->get();
        $this->assertSame(1, strlen($code1));

        // length = 2
        $gen2 = new DefaultGenerator(2, '0123456789', true);
        for ($i = 0; $i < 500; $i++) {
            $code2 = $gen2->get();
            $this->assertSame(2, strlen($code2));
            $this->assertNotSame('0', $code2[0]);
        }
    }

    public function testNonZeroLeadingBoolCasting()
    {
        $gen1 = new DefaultGenerator(4, '0123456789', 1);
        $this->assertTrue($gen1->isNonZeroLeading());

        $gen0 = new DefaultGenerator(4, '0123456789', 0);
        $this->assertFalse($gen0->isNonZeroLeading());

        $genStr = new DefaultGenerator(4, '0123456789', '');
        $this->assertFalse($genStr->isNonZeroLeading());
    }

    protected function getGenerator($length = null): DefaultGenerator
    {
        return new DefaultGenerator($length, null);
    }
}
