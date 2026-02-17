<?php

namespace Webkul\Admin\Http\Controllers\Client;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Client\Repositories\ClientRepository;
use Webkul\Client\Repositories\ClientSlaRepository;

class ClientSlaController extends Controller
{
    public function __construct(
        protected ClientRepository $clientRepository,
        protected ClientSlaRepository $clientSlaRepository
    ) {}

    public function store(Request $request, int $clientId): RedirectResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'response_hours'   => 'nullable|integer|min:0',
            'resolution_hours' => 'nullable|integer|min:0',
            'terms'            => 'nullable|string',
            'document'         => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
        ]);

        $this->clientRepository->findOrFail($clientId);

        $data = array_merge($request->only([
            'name', 'response_hours', 'resolution_hours', 'terms',
        ]), ['client_id' => $clientId]);

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store(
                'client_slas/' . $clientId,
                'public'
            );
            $data['document_path'] = $path;
        }

        $this->clientSlaRepository->create($data);

        session()->flash('success', trans('admin::app.clients.slas.create-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }

    public function update(Request $request, int $clientId, int $id): RedirectResponse
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'response_hours'   => 'nullable|integer|min:0',
            'resolution_hours' => 'nullable|integer|min:0',
            'terms'            => 'nullable|string',
            'document'         => 'nullable|file|mimes:pdf,doc,docx,txt|max:10240',
        ]);

        $data = $request->only(['name', 'response_hours', 'resolution_hours', 'terms']);

        if ($request->hasFile('document')) {
            $sla = $this->clientSlaRepository->find($id);
            if ($sla && $sla->document_path) {
                Storage::disk('public')->delete($sla->document_path);
            }
            $path = $request->file('document')->store(
                'client_slas/' . $clientId,
                'public'
            );
            $data['document_path'] = $path;
        }

        $this->clientSlaRepository->update($data, $id);

        session()->flash('success', trans('admin::app.clients.slas.update-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }

    public function destroy(int $clientId, int $id): RedirectResponse
    {
        $sla = $this->clientSlaRepository->find($id);
        if ($sla && $sla->document_path) {
            Storage::disk('public')->delete($sla->document_path);
        }
        $this->clientSlaRepository->delete($id);
        session()->flash('success', trans('admin::app.clients.slas.delete-success'));

        return redirect()->route('admin.clients.view', $clientId);
    }
}
