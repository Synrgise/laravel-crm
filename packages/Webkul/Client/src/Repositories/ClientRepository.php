<?php

namespace Webkul\Client\Repositories;

use Illuminate\Container\Container;
use Webkul\Client\Contracts\Client;
use Webkul\Core\Eloquent\Repository;

class ClientRepository extends Repository
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function model(): string
    {
        return Client::class;
    }

    /**
     * Find or create a client for the given organization (e.g. when a deal is won).
     */
    public function findOrCreateForOrganization(int $organizationId, array $attributes = []): \Webkul\Client\Contracts\Client
    {
        $client = $this->findOneWhere(['organization_id' => $organizationId]);

        if ($client) {
            return $client;
        }

        return $this->create(array_merge([
            'organization_id' => $organizationId,
            'client_since'    => now()->toDateString(),
        ], $attributes));
    }
}
