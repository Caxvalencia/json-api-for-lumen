<?php

namespace RealPage\JsonApi\Validation;

use Neomerx\JsonApi\Exceptions\JsonApiException;

class RequestFailedValidation extends JsonApiException
{
    public function getHttpCode(): int
    {
        return 422;
    }
}
