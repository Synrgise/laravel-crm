<?php

namespace Webkul\Client\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Client\Contracts\ClientSla as ClientSlaInterface;

class ClientSla extends Model implements ClientSlaInterface
{
    protected $table = 'client_slas';

    protected $fillable = [
        'client_id',
        'name',
        'response_hours',
        'resolution_hours',
        'terms',
        'document_path',
    ];

    public function client()
    {
        return $this->belongsTo(ClientProxy::modelClass());
    }
}
