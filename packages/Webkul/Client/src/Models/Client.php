<?php

namespace Webkul\Client\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Client\Contracts\Client as ClientContract;
use Webkul\Contact\Models\OrganizationProxy;

class Client extends Model implements ClientContract
{
    protected $table = 'clients';

    protected $fillable = [
        'organization_id',
        'client_since',
        'billing_email',
        'billing_address',
        'payment_terms',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'client_since'    => 'date',
        'billing_address' => 'array',
    ];

    /**
     * Get the organization that owns the client.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(OrganizationProxy::modelClass());
    }

    /**
     * Get the contracts and agreements.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ClientContractProxy::modelClass());
    }

    /**
     * Get the SLAs.
     */
    public function slas(): HasMany
    {
        return $this->hasMany(ClientSlaProxy::modelClass());
    }
}
