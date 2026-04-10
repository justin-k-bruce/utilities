<?php

declare(strict_types=1);

namespace Utilities;

use Utilities\ErrorCode;

/**
 * Class EmailReaderError This is an error class for EmailReader
 * @package Utilities
 */
class EmailReaderError
{
    /**
     * @var ErrorCode $code Error code enum
     */
    public ErrorCode $code;

    /**
     * @var string $message Error message
     */
    public string $message;

    /**
     * @var array|null $imapErrors Contains Imap errors
     */
    public ?array $imapErrors = null;

    /**
     * EmailReaderError constructor.
     * @param ErrorCode $code Error code enum
     * @param array|null $imapErrors Contains Imap errors
     */
    public function __construct(ErrorCode $code, ?array $imapErrors = null)
    {
        $this->code = $code;
        $this->message = $code->message();
        $this->imapErrors = $imapErrors;
    }

    /**
     * Get Error returns an object with the error code and message
     * @return object
     */
    public function getError(): object
    {
        return (object)[
            "errorCode" => $this->code->value,
            "errorMessage" => $this->message,
            "imapErrors" => $this->imapErrors,
        ];
    }
}
