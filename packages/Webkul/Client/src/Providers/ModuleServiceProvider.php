<?php

namespace Webkul\Client\Providers;

use Webkul\Core\Providers\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Client\Models\Client::class,
        \Webkul\Client\Models\ClientContract::class,
        \Webkul\Client\Models\ClientSla::class,
    ];
}
