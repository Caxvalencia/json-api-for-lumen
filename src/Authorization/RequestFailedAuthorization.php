<?php

namespace RealPage\JsonApi\Authorization;

use Neomerx\JsonApi\Exceptions\JsonApiException;

class RequestFailedAuthorization extends JsonApiException
{
    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return 403;
    }
}
