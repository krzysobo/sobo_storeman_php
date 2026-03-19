<?php
namespace App\Exceptions;

class InvalidDataException extends \Exception
{
    protected $code = 400;
}
