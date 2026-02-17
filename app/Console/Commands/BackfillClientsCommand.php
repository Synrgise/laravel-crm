<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Webkul\Client\Repositories\ClientRepository;

class BackfillClientsCommand extends Command
{
    protected $signature = 'clients:backfill
                            {--dry-run : List organizations that would get a client without creating them}';

    protected $description = 'Create client records for organizations that have won leads but no client yet';

    public function handle(ClientRepository $clientRepository): int
    {
        $prefix = DB::getTablePrefix();
        $dryRun = $this->option('dry-run');

        $organizationIdsWithWonLeads = DB::table('leads')
            ->join('persons', 'leads.person_id', '=', 'persons.id')
            ->join('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->where('lead_pipeline_stages.code', 'won')
            ->whereNotNull('persons.organization_id')
            ->distinct()
            ->pluck('persons.organization_id');

        if ($organizationIdsWithWonLeads->isEmpty()) {
            $this->info('No organizations with won leads found.');
            return self::SUCCESS;
        }

        $existingClientOrgIds = DB::table('clients')->pluck('organization_id')->flip();
        $toCreate = $organizationIdsWithWonLeads->filter(fn ($id) => ! $existingClientOrgIds->has($id));

        if ($toCreate->isEmpty()) {
            $this->info('All organizations with won leads already have a client.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('Would create clients for ' . $toCreate->count() . ' organization(s):');
            foreach ($toCreate as $orgId) {
                $org = DB::table('organizations')->where('id', $orgId)->first();
                $this->line('  - ' . ($org->name ?? "ID {$orgId}"));
            }
            return self::SUCCESS;
        }

        $created = 0;
        foreach ($toCreate as $organizationId) {
            $latestWonLead = DB::table('leads')
                ->join('persons', 'leads.person_id', '=', 'persons.id')
                ->join('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
                ->where('persons.organization_id', $organizationId)
                ->where('lead_pipeline_stages.code', 'won')
                ->orderByDesc('leads.closed_at')
                ->orderByDesc('leads.created_at')
                ->select('leads.user_id', 'leads.closed_at')
                ->first();

            $clientRepository->findOrCreateForOrganization($organizationId, [
                'user_id'     => $latestWonLead->user_id ?? null,
                'client_since' => $latestWonLead->closed_at ? date('Y-m-d', strtotime($latestWonLead->closed_at)) : now()->toDateString(),
            ]);
            $created++;
        }

        $this->info("Created {$created} client(s) for organizations with won leads.");
        return self::SUCCESS;
    }
}
