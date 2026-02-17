<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.clients.leads.title', ['name' => $client->organization->name ?? 'Client'])
    </x-slot>

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="clients.leads" :entity="$client" />
                <div class="text-xl font-bold dark:text-gray-300">
                    @lang('admin::app.clients.leads.title', ['name' => $client->organization->name ?? __('admin::app.layouts.client')])
                </div>
            </div>
            <a href="{{ route('admin.clients.view', $client->id) }}" class="secondary-button">
                @lang('admin::app.clients.leads.back-to-client')
            </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            @if ($leads->isEmpty())
                <p class="text-sm text-gray-500">@lang('admin::app.clients.leads.no-leads')</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.leads.lead')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.leads.contact')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.leads.status')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.leads.value')</th>
                                <th class="py-2 text-left font-medium">@lang('admin::app.clients.leads.created')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($leads as $lead)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-2 font-medium">{{ $lead->title }}</td>
                                    <td class="py-2">{{ $lead->person->name ?? '—' }}</td>
                                    <td class="py-2">{{ $lead->stage->name ?? '—' }}</td>
                                    <td class="py-2">{{ $lead->lead_value ? core()->formatPrice($lead->lead_value) : '—' }}</td>
                                    <td class="py-2">{{ $lead->created_at?->format('M d, Y') ?? '—' }}</td>
                                    <td class="py-2">
                                        <a href="{{ route('admin.leads.view', $lead->id) }}" class="link-primary text-sm">
                                            @lang('admin::app.clients.leads.view-lead')
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin::layouts>
