<?php
namespace App\Exceptions;

class NoAwsCredentialsException extends \Exception
{
    protected $code = 401;
}
