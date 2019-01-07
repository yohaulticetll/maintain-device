<?php

namespace AppBundle\Validator;

use AppBundle\Exception\FlagNotValidException;

interface FlagValidatorInterface
{
    /**
     * @throws FlagNotValidException
     *
     * @param string $newFlag
     * @param string $lastFlag
     * @return bool
     */
    public function flagIsValid(string $newFlag, string $lastFlag): bool;
}