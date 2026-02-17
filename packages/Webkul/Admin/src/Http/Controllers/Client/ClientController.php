<?php

namespace Webkul\Admin\Http\Controllers\Client;

use Illuminate\View\View;
use Webkul\Admin\DataGrids\Client\ClientDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Client\Repositories\ClientRepository;
use Webkul\Lead\Models\LeadProxy;

class ClientController extends Controller
{
    public function __construct(protected ClientRepository $clientRepository) {}

    public function index(): View|\Illuminate\Http\JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(ClientDataGrid::class)->process();
        }

        return view('admin::clients.index');
    }

    public function view(int $id): View
    {
        $client = $this->clientRepository->findOrFail($id);
        $client->load(['organization', 'contracts', 'slas']);

        $leadsCount = LeadProxy::modelClass()::query()
            ->whereHas('person', fn ($q) => $q->where('organization_id', $client->organization_id))
            ->count();

        return view('admin::clients.view', compact('client', 'leadsCount'));
    }

    public function edit(int $id): View
    {
        $client = $this->clientRepository->findOrFail($id);
        $client->load(['organization', 'contracts', 'slas']);

        return view('admin::clients.edit', compact('client'));
    }

    public function update(int $id): \Illuminate\Http\RedirectResponse
    {
        $client = $this->clientRepository->update(request()->only([
            'billing_email',
            'billing_address',
            'payment_terms',
            'notes',
        ]), $id);

        session()->flash('success', trans('admin::app.clients.edit.update-success'));

        return redirect()->route('admin.clients.view', $client->id);
    }

    public function leads(int $id): View
    {
        $client = $this->clientRepository->findOrFail($id);
        $client->load('organization');

        $leads = LeadProxy::modelClass()::query()
            ->whereHas('person', fn ($q) => $q->where('organization_id', $client->organization_id))
            ->with(['person', 'stage', 'pipeline'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin::clients.leads', compact('client', 'leads'));
    }
}
