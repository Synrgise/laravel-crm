<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.clients.view.title', ['name' => $client->organization->name ?? 'Client'])
    </x-slot>

    @push('styles')
        <style>
            .modal-container { display: none; position: fixed; inset: 0; z-index: 10002; align-items: center; justify-content: center; padding: 1rem; }
            .modal-container:not(.hidden) { display: flex; }
            .modal-container .modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.5); cursor: pointer; }
            .modal-container .modal-content { position: relative; z-index: 1; max-height: 90vh; overflow-y: auto; background: white; border-radius: 0.5rem; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
            .dark .modal-container .modal-content { background: rgb(17 24 39); }
        </style>
    @endpush

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="clients.view" :entity="$client" />
                <div class="text-xl font-bold dark:text-gray-300">
                    {{ $client->organization->name ?? __('admin::app.layouts.client') }}
                </div>
            </div>
            @if (bouncer()->hasPermission('clients.edit'))
                <a href="{{ route('admin.clients.edit', $client->id) }}" class="primary-button">
                    @lang('admin::app.clients.view.edit-client')
                </a>
            @endif
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Billing details --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-base font-semibold dark:text-white">
                    @lang('admin::app.clients.view.billing-details')
                </h3>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">@lang('admin::app.clients.view.client-since')</dt>
                        <dd class="font-medium">{{ $client->client_since ? $client->client_since->format('M d, Y') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">@lang('admin::app.clients.view.billing-email')</dt>
                        <dd class="font-medium">{{ $client->billing_email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">@lang('admin::app.clients.view.payment-terms')</dt>
                        <dd class="font-medium">{{ $client->payment_terms ?: '—' }}</dd>
                    </div>
                    @if ($client->billing_address && is_array($client->billing_address))
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Address</dt>
                            <dd class="font-medium">{{ implode(', ', array_filter($client->billing_address)) ?: '—' }}</dd>
                        </div>
                    @endif
                    @if ($client->notes)
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">@lang('admin::app.clients.view.notes')</dt>
                            <dd class="font-medium">{{ $client->notes }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- SLAs --}}
            <div id="sla" class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold dark:text-white">@lang('admin::app.clients.view.sla')</h3>
                    @if (bouncer()->hasPermission('clients.edit'))
                        <button type="button" class="link-primary text-sm" data-toggle="modal" data-target="#add-sla-modal">
                            @lang('admin::app.clients.view.add-sla')
                        </button>
                    @endif
                </div>
                @if ($client->slas->isEmpty())
                    <p class="text-sm text-gray-500">@lang('admin::app.clients.view.no-slas')</p>
                @else
                    <ul class="space-y-3">
                        @foreach ($client->slas as $sla)
                            <li class="rounded border border-gray-200 p-3 dark:border-gray-700">
                                <div class="flex justify-between">
                                    <span class="font-medium">{{ $sla->name }}</span>
                                    @if (bouncer()->hasPermission('clients.edit'))
                                        <form action="{{ route('admin.clients.slas.destroy', [$client->id, $sla->id]) }}" method="POST" class="inline" onsubmit="return confirm('@lang('admin::app.clients.slas.delete-success')');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">@lang('admin::app.acl.delete')</button>
                                        </form>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    @lang('admin::app.clients.view.response-time'): {{ $sla->response_hours ?? '—' }}h |
                                    @lang('admin::app.clients.view.resolution-time'): {{ $sla->resolution_hours ?? '—' }}h
                                </p>
                                @if ($sla->terms)
                                    <p class="mt-1 text-sm">{{ Str::limit($sla->terms, 100) }}</p>
                                @endif
                                @if ($sla->document_path)
                                    <p class="mt-2">
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($sla->document_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline dark:text-blue-400">
                                            <span class="icon-download"></span>
                                            @lang('admin::app.clients.view.download') @lang('admin::app.clients.view.sla-document')
                                        </a>
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Uploaded Documents --}}
        @php
            $contractsWithDocuments = $client->contracts->filter(fn ($c) => !empty($c->document_path));
        @endphp
        <div id="documents" class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-3 text-base font-semibold dark:text-white">@lang('admin::app.clients.view.documents')</h3>
            @if ($contractsWithDocuments->isEmpty())
                <p class="text-sm text-gray-500">@lang('admin::app.clients.view.no-documents')</p>
            @else
                <ul class="space-y-2">
                    @foreach ($contractsWithDocuments as $contract)
                        <li class="flex items-center justify-between rounded border border-gray-200 p-2 dark:border-gray-700">
                            <span class="text-sm font-medium">{{ $contract->name }}</span>
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($contract->document_path) }}" target="_blank" rel="noopener" class="link-primary text-sm">
                                @lang('admin::app.clients.view.download')
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Leads --}}
        <div id="leads" class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold dark:text-white">@lang('admin::app.clients.view.leads')</h3>
                <a href="{{ route('admin.clients.leads.index', $client->id) }}" class="link-primary text-sm">
                    @lang('admin::app.clients.view.view-leads') ({{ $leadsCount }})
                </a>
            </div>
            <p class="text-sm text-gray-500">@lang('admin::app.clients.view.leads-count', ['count' => $leadsCount])</p>
        </div>

        {{-- Contracts & Agreements --}}
        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold dark:text-white">@lang('admin::app.clients.view.contracts')</h3>
                @if (bouncer()->hasPermission('clients.edit'))
                    <button type="button" class="link-primary text-sm" data-toggle="modal" data-target="#add-contract-modal">
                        @lang('admin::app.clients.view.add-contract')
                    </button>
                @endif
            </div>
            @if ($client->contracts->isEmpty())
                <p class="text-sm text-gray-500">@lang('admin::app.clients.view.no-contracts')</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.view.contract-name')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.view.contract-type')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.view.start-date')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.view.end-date')</th>
                                @if (bouncer()->hasPermission('clients.edit'))
                                    <th></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($client->contracts as $contract)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-2">{{ $contract->name }}</td>
                                    <td class="py-2">{{ $contract->type === 'agreement' ? __('admin::app.clients.contracts.type-agreement') : __('admin::app.clients.contracts.type-contract') }}</td>
                                    <td class="py-2">{{ $contract->start_date?->format('M d, Y') ?? '—' }}</td>
                                    <td class="py-2">{{ $contract->end_date?->format('M d, Y') ?? '—' }}</td>
                                    @if (bouncer()->hasPermission('clients.edit'))
                                        <td class="py-2">
                                            <form action="{{ route('admin.clients.contracts.destroy', [$client->id, $contract->id]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this contract?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">@lang('admin::app.acl.delete')</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if (bouncer()->hasPermission('clients.edit'))
        {{-- Add Contract modal --}}
        <div id="add-contract-modal" class="modal-container hidden">
            <div class="modal-backdrop" data-toggle="modal" data-target="#add-contract-modal"></div>
            <div class="modal-content max-w-md">
                <h3 class="mb-4 text-lg font-semibold">@lang('admin::app.clients.view.add-contract')</h3>
                <form action="{{ route('admin.clients.contracts.store', $client->id) }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium">@lang('admin::app.clients.view.contract-name') <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900" required maxlength="255">
                        </div>
                        <div>
                            <label class="block text-sm font-medium">@lang('admin::app.clients.view.contract-type')</label>
                            <select name="type" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
                                <option value="contract">@lang('admin::app.clients.contracts.type-contract')</option>
                                <option value="agreement">@lang('admin::app.clients.contracts.type-agreement')</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium">@lang('admin::app.clients.view.start-date')</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">@lang('admin::app.clients.view.end-date')</label>
                                <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium">@lang('admin::app.clients.view.notes')</label>
                            <textarea name="notes" rows="2" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="secondary-button" data-toggle="modal" data-target="#add-contract-modal">Cancel</button>
                        <button type="submit" class="primary-button">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Add SLA modal --}}
        <div id="add-sla-modal" class="modal-container hidden">
            <div class="modal-backdrop" data-toggle="modal" data-target="#add-sla-modal"></div>
            <div class="modal-content max-w-md">
                <h3 class="mb-4 text-lg font-semibold">@lang('admin::app.clients.view.add-sla')</h3>
                <form action="{{ route('admin.clients.slas.store', $client->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300">@lang('admin::app.clients.view.sla-name') <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" required maxlength="255" placeholder="e.g. Standard Support">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300">@lang('admin::app.clients.view.response-time')</label>
                                <input type="number" name="response_hours" value="{{ old('response_hours') }}" min="0" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Hours">
                            </div>
                            <div>
                                <label class="block text-sm font-medium dark:text-gray-300">@lang('admin::app.clients.view.resolution-time')</label>
                                <input type="number" name="resolution_hours" value="{{ old('resolution_hours') }}" min="0" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Hours">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300">@lang('admin::app.clients.view.terms')</label>
                            <textarea name="terms" rows="4" class="mt-1 w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900 dark:text-white" placeholder="Describe SLA terms and conditions...">{{ old('terms') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium dark:text-gray-300">@lang('admin::app.clients.view.sla-document')</label>
                            <input type="file" name="document" accept=".pdf,.doc,.docx,.txt" class="mt-1 w-full text-sm text-gray-500 file:mr-3 file:rounded file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-800 dark:file:text-gray-300 dark:hover:file:bg-gray-700">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('admin::app.clients.view.sla-document-hint')</p>
                            @error('document')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" class="secondary-button" data-toggle="modal" data-target="#add-sla-modal">@lang('admin::app.clients.view.cancel')</button>
                        <button type="submit" class="primary-button">@lang('admin::app.clients.view.save-sla')</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            (function() {
                function handleModalToggle(e) {
                    var trigger = e.target.closest('[data-toggle="modal"]');
                    if (!trigger) return;
                    var targetId = trigger.getAttribute('data-target');
                    if (!targetId) return;
                    var modal = document.querySelector(targetId);
                    if (!modal) return;
                    e.preventDefault();
                    modal.classList.toggle('hidden');
                    document.body.style.overflow = modal.classList.contains('hidden') ? '' : 'hidden';
                }
                document.addEventListener('click', handleModalToggle);
            })();
        </script>
    @endpush
</x-admin::layouts>
