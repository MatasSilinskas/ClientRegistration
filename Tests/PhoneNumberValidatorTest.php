<?php

use PHPUnit\Framework\TestCase;

require_once './Validators/PhoneNumberValidator.php';

class PhoneNumberValidatorTest extends TestCase
{
    /**
     * @param string $phoneNumber
     * @param bool   $expected
     *
     * @dataProvider phoneNumberProvider
     */
    public function testValidate(bool $expected, string $phoneNumber): void
    {
        $phoneNumberValidator = new PhoneNumberValidator();
        $this->assertEquals($expected, $phoneNumberValidator->validate($phoneNumber));
    }

    public function phoneNumberProvider()
    {
        return [
            [true, '860000000'],
            [false, '86000a000'],
            [false, 'aaaaaaaaa'],
            [false, 'a860000000c'],
            [false, '860000000c'],
            [false, 'a860000000'],
            [false, '86000000'],
            [false, '8600000000'],
            [true, '+37000000000'],
            [false, '+3700a000000'],
            [false, 'aaa+37000000000ccc'],
            [false, '+37000000000ccc'],
            [false, 'aaa+37000000000'],
            [false, '37000000000'],
            [false, '+3700000000'],
            [false, '+370000000000'],
            [false, '+aaaaaaaaaaa'],
        ];
    }
}