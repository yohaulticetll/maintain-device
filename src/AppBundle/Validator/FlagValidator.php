<?php

namespace AppBundle\Validator;

use AppBundle\Exception\FlagNotValidException;

/**
 * Class FlagValidator
 * @package AppBundle\Validator
 */
class FlagValidator implements FlagValidatorInterface
{
    /**
     *
     */
    const FLAG_INITIAL = 'dekompletacja_rozpakowywanie';

    /**
     * @var array
     */
    protected $allowedFlags = [
        'pakowanie' => ['czyszczenie', 'wymiana_obudowy'],
        'czyszczenie' => ['testowanie_sprawny'],
        'wymiana_obudowy' => ['testowanie_sprawny'],
        'testowanie_sprawny' => ['dekompletacja_rozpakowywanie'],
        'pakowanie_uszkodzony' => ['testowanie_uszkodzony'],
        'testowanie_uszkodzony' => ['dekompletacja_rozpakowywanie']
    ];

    /**
     * @param string $flagName
     * @param string $lastFlag
     * @return bool
     * @throws FlagNotValidException
     */
    public function flagIsValid(string $flagName, string $lastFlag): bool
    {
        $flagName = strtolower($flagName);
        $lastFlag = strtolower($lastFlag);

        if (self::FLAG_INITIAL === $flagName && empty($lastFlag)) {
            return true;
        }

        if (array_key_exists($flagName, $this->allowedFlags) && in_array($lastFlag, $this->allowedFlags[$flagName])) {
            return true;
        }

        throw new FlagNotValidException("Attempt to assign not existing or not allowed flag");
    }

}