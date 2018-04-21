<?php

namespace RealPage\JsonApi\Requests;

use Neomerx\JsonApi\Contracts\Document\ErrorInterface;

class MalformedRequest implements ErrorInterface
{
    /**
     * Get a unique identifier for this particular occurrence of the problem.
     *
     * @return int|string|null
     */
    public function getId()
    {
        return null;
    }

    /**
     * Get links that may lead to further details about this particular occurrence of the problem.
     *
     * @return null|array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    public function getLinks(): ?array
    {
        return null;
    }

    /**
     * Get the HTTP status code applicable to this problem, expressed as a string value.
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return 400; // bad request
    }

    /**
     * Get an application-specific error code, expressed as a string value.
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return null;
    }

    /**
     * Get a short, human-readable summary of the problem.
     *
     * It should not change from occurrence to occurrence of the problem, except for purposes of localization.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return 'Request json malformed';
    }

    /**
     * Get a human-readable explanation specific to this occurrence of the problem.
     *
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return 'The request json is malformed and could not be parsed.';
    }

    /**
     * An object containing references to the source of the error, optionally including any of the following members:
     *    "pointer"   - A JSON Pointer [RFC6901] to the associated entity in the request document
     *                  [e.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute].
     *    "parameter" - An optional string indicating which query parameter caused the error.
     *
     * @return array|null
     */
    public function getSource(): ?array
    {
        return null;
    }

    /**
     * Get error meta information.
     *
     * @return array|null
     */
    public function getMeta()
    {
        return null;
    }
}
