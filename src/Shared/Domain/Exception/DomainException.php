<?php

namespace App\Shared\Domain\Exception;

class DomainException extends \DomainException
{
    use TranslateExceptionTrait;
}
