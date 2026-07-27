<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status = 400,
    ) {
        parent::__construct($message);
    }

    public static function unauthorized(string $message = 'Authentication is required.'): self
    {
        return new self('UNAUTHORIZED', $message, 401);
    }

    public static function forbidden(string $message = 'This action is forbidden.'): self
    {
        return new self('FORBIDDEN', $message, 403);
    }

    public static function notFound(string $message = 'The requested resource was not found.'): self
    {
        return new self('NOT_FOUND', $message, 404);
    }

    public static function conflict(string $code, string $message): self
    {
        return new self($code, $message, 409);
    }
}
