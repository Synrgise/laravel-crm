<?php

namespace Webkul\Admin\Listeners;

use Webkul\Client\Repositories\ClientRepository;

class ConvertWonLeadToClient
{
    public function __construct(protected ClientRepository $clientRepository) {}

    /**
     * When a lead is closed as won, create a client from the lead's organization if applicable.
     *
     * @param  \Webkul\Lead\Models\Lead  $lead
     * @return void
     */
    public function handle($lead): void
    {
        $lead->loadMissing('person.organization');

        if (! $lead->person || ! $lead->person->organization_id) {
            return;
        }

        $this->clientRepository->findOrCreateForOrganization($lead->person->organization_id, [
            'user_id' => $lead->user_id,
        ]);
    }
}
