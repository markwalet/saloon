<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
interface Request extends Authenticatable, CanThrowRequestExceptions, HasConfig, HasHeaders, HasQueryParams, HasDelay, HasMiddlewarePipeline, HasMockClient, HasRetry
{
    /**
     * Get the HTTP method
     */
    public function getMethod(): Method;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string;

    /**
     * Handle the boot lifecycle hook
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Handle the PSR request before it is sent
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface;

    /**
     * Cast the response to a DTO.
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Contracts\Response>|null
     */
    public function resolveResponseClass(): ?string;
}
