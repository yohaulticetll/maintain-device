<?php

namespace Tests\AppBundle\Validator;

use AppBundle\Exception\FlagNotValidException;
use AppBundle\Validator\FlagValidator;
use PHPUnit\Framework\TestCase;

/**
 * Class FlagValidatorTest
 * @package Tests\AppBundle\Validator
 *
 * @covers \AppBundle\Validator\FlagValidator
 */
class FlagValidatorTest extends TestCase
{
    /**
     * @covers \AppBundle\Validator\FlagValidator::flagIsValid
     *
     * @throws FlagNotValidException
     */
    public function testFlagIsValid()
    {
        $validator = new FlagValidator();
        $this->assertTrue($validator->flagIsValid(FlagValidator::FLAG_INITIAL, ''));

        $this->expectException(FlagNotValidException::class);

        $validator->flagIsValid(chr(mt_rand(0,127)),chr(mt_rand(0,127)));
    }
}