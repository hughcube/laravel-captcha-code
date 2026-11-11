<?php
/**
 * Created by PhpStorm.
 * User: hugh.li
 * Date: 2021/4/20
 * Time: 5:19 下午.
 */

namespace HughCube\Laravel\CaptchaCode\Generator;

class DefaultGenerator implements Generator
{
    /**
     * ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.
     *
     * @var string
     */
    protected $string = '0123456789';

    protected $length = 6;

    protected $maxIndex;

    /**
     * @var bool
     */
    protected $nonZeroLeading = false;

    /**
     * @var string
     */
    protected $leadingString;

    protected $maxLeadingIndex;

    public function __construct($length, $string = null, $nonZeroLeading = false)
    {
        $this->string = null === $string ? $this->string : $string;
        $this->length = null === $length ? $this->length : $length;
        $this->nonZeroLeading = (bool) $nonZeroLeading;

        $this->maxIndex = 0 >= strlen($this->string) ? 0 : (strlen($this->string) - 1);

        $leadingString = str_replace('0', '', $this->string);
        $this->leadingString = empty($leadingString) ? $this->string : $leadingString;
        $this->maxLeadingIndex = 0 >= strlen($this->leadingString) ? 0 : (strlen($this->leadingString) - 1);
    }

    /**
     * @return bool
     */
    public function isNonZeroLeading(): bool
    {
        return $this->nonZeroLeading;
    }

    /**
     * @param bool $nonZeroLeading
     *
     * @return $this
     */
    public function withNonZeroLeading(bool $nonZeroLeading = true): DefaultGenerator
    {
        $this->nonZeroLeading = $nonZeroLeading;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(): string
    {
        $code = '';
        for ($i = 1; $i <= $this->length; $i++) {
            if (1 === $i && $this->length > 1 && $this->nonZeroLeading) {
                $code .= $this->leadingString[mt_rand(0, $this->maxLeadingIndex)];
            } else {
                $code .= $this->string[mt_rand(0, $this->maxIndex)];
            }
        }

        return $code;
    }
}
