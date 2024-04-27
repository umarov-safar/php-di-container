<?php

namespace DInjection\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class CouldNotResolveClassException extends RuntimeException implements NotFoundExceptionInterface
{

}