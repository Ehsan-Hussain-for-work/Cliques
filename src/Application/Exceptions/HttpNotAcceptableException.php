<?php

namespace App\Application\Exceptions;

use Slim\Exception\HttpException;
use Psr\Http\Message\ServerRequestInterface;

class HttpNotAcceptableException extends HttpException
{
    protected $code = 406;
    protected $message = 'Not Acceptable.';
}