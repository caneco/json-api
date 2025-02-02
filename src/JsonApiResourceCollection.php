<?php

declare(strict_types=1);

namespace TiMacDonald\JsonApi;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class JsonApiResourceCollection extends AnonymousResourceCollection
{
    /**
     * @param Request $request
     */
    public function with($request): array
    {
        return [
            'included' => $this->collection
                ->map(fn (JsonApiResource $resource): Collection => $resource->included($request))
                ->flatten()
                ->reject(fn (?JsonApiResource $resource): bool => $resource === null)
                ->uniqueStrict(fn (JsonApiResource $resource): string => $resource->toUniqueResourceIdentifier($request)),
        ];
    }

    /**
     * @param Request $request
     */
    public function toResponse($request)
    {
        return parent::toResponse($request)->header('Content-type', 'application/vnd.api+json');
    }

    /**
     * @internal
     */
    public function withIncludePrefix(string $prefix): self
    {
        $this->collection->each(fn (JsonApiResource $resource): JsonApiResource => $resource->withIncludePrefix($prefix));

        return $this;
    }

    /**
     * @internal
     */
    public function included(Request $request): Collection
    {
        return $this->collection->map(fn (JsonApiResource $resource): Collection => $resource->included($request));
    }

    /**
     * @internal
     */
    public function toResourceIdentifier(Request $request): array
    {
        return $this->collection->map(fn (JsonApiResource $resource): array => $resource->toResourceIdentifier($request))->all();
    }
}
