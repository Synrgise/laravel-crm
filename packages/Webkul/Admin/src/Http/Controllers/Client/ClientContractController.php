<?php

namespace Webkul\Admin\Http\Controllers\Client;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Client\Repositories\ClientContractRepository;
use Webkul\Client\Repositories\ClientRepository;

class ClientContractController extends Controller
{
    public function __construct(
        protected ClientRepository $clientRepository,
        protected ClientContractRepository $clientContractRepository
    ) {}

    public function store(Request $request, int $clientId): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:contract,agreement',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'notes'      => 'nullable|string',
        ]);

        $this->clientRepository->findOrFail($clientId);
        $this->clientContractRepository->create(array_merge($request->only([
            'name', 'type', 'start_date', 'end_date', 'notes',
        ]), ['client_id' => $clientId]));

        session()->flash('success', trans('admin::app.clients.contracts.create-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }

    public function update(Request $request, int $clientId, int $id): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'type'       => 'required|in:contract,agreement',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'notes'      => 'nullable|string',
        ]);

        $this->clientContractRepository->update($request->only([
            'name', 'type', 'start_date', 'end_date', 'notes',
        ]), $id);

        session()->flash('success', trans('admin::app.clients.contracts.update-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }

    public function destroy(int $clientId, int $id): RedirectResponse
    {
        $this->clientContractRepository->delete($id);
        session()->flash('success', trans('admin::app.clients.contracts.delete-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }
}
