<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Data\PipeOrder;

/**
 * @internal
 */
interface MiddlewarePipeline
{
    /**
     * Add a middleware before the request is sent
     *
     * @param callable(\Saloon\Contracts\PendingRequest): (\Saloon\Contracts\PendingRequest|\Saloon\Contracts\FakeResponse|void) $callable
     * @return $this
     */
    public function onRequest(callable $callable, ?string $name = null, ?PipeOrder $order = null): static;

    /**
     * Add a middleware after the request is sent
     *
     * @param callable(\Saloon\Contracts\Response): (\Saloon\Contracts\Response|void) $callable
     * @return $this
     */
    public function onResponse(callable $callable, ?string $name = null, ?PipeOrder $order = null): static;

    /**
     * Process the request pipeline.
     */
    public function executeRequestPipeline(PendingRequest $pendingRequest): PendingRequest;

    /**
     * Process the response pipeline.
     */
    public function executeResponsePipeline(Response $response): Response;

    /**
     * Merge in another middleware pipeline.
     *
     * @return $this
     */
    public function merge(self $middlewarePipeline): static;

    /**
     * Get the request pipeline
     */
    public function getRequestPipeline(): Pipeline;

    /**
     * Get the response pipeline
     */
    public function getResponsePipeline(): Pipeline;
}
