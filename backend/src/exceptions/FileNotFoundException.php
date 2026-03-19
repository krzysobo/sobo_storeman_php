<?php
namespace App\Exceptions;

class FileNotFoundException extends \Exception
{
    protected $code = 404;
}
