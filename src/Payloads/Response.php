<?php

namespace Butschster\Exchanger\Payloads;

use JMS\Serializer\Annotation as JMS;
use Butschster\Exchanger\Contracts\Exchange\Payload as PayloadContract;

class Response implements PayloadContract
{
    /** @JMS\Type("boolean") */
    public bool $success;

    /** @JMS\Type("Butschster\Exchanger\Payloads\Response\Headers") */
    public ?Response\Headers $headers = null;

    /** @JMS\Type("Butschster\Exchanger\Payloads\Payload") */
    public $payload = null;

    /** @JMS\Type("array<Butschster\Exchanger\Payloads\Error>") */
    public array $errors;

    /**
     * Check if error with given code exist
     * @param int $code
     * @return bool
     */
    public function hasError(int $code): bool
    {
        if (! is_array($this->errors)) {
            return false;
        }

        /** @var Error $error */
        foreach ($this->errors as $error) {
            if ($error->code == $code) {
                return true;
            }
        }

        return false;
    }
}
