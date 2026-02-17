<?php

namespace Webkul\Client\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;

class ClientContractRepository extends Repository
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function model(): string
    {
        return \Webkul\Client\Models\ClientContract::class;
    }
}
