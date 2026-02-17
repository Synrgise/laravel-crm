<?php

namespace Webkul\Client\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Client\Contracts\ClientContract as ClientContractInterface;

class ClientContract extends Model implements ClientContractInterface
{
    protected $table = 'client_contracts';

    protected $fillable = [
        'client_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'document_path',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function client()
    {
        return $this->belongsTo(ClientProxy::modelClass());
    }
}
