<?php

namespace Webkul\Admin\Http\Controllers\Client;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'document'   => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
        ]);

        $this->clientRepository->findOrFail($clientId);

        $data = array_merge($request->only([
            'name', 'type', 'start_date', 'end_date', 'notes',
        ]), ['client_id' => $clientId]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store(
                'client_contracts/' . $clientId,
                'public'
            );
            $data['document_path'] = $path;
        }

        $this->clientContractRepository->create($data);

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
            'document'   => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
        ]);

        $data = $request->only(['name', 'type', 'start_date', 'end_date', 'notes']);

        if ($request->hasFile('document')) {
            $contract = $this->clientContractRepository->find($id);
            if ($contract && $contract->document_path) {
                Storage::disk('public')->delete($contract->document_path);
            }
            $path = $request->file('document')->store(
                'client_contracts/' . $clientId,
                'public'
            );
            $data['document_path'] = $path;
        }

        $this->clientContractRepository->update($data, $id);

        session()->flash('success', trans('admin::app.clients.contracts.update-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }

    public function destroy(int $clientId, int $id): RedirectResponse
    {
        $contract = $this->clientContractRepository->find($id);
        if ($contract && $contract->document_path) {
            Storage::disk('public')->delete($contract->document_path);
        }
        $this->clientContractRepository->delete($id);
        session()->flash('success', trans('admin::app.clients.contracts.delete-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }
}
