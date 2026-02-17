<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.clients.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.clients.update', $client->id)"
        method="PUT"
    >
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div class="flex flex-col gap-2">
                    <x-admin::breadcrumbs name="clients.edit" :entity="$client" />
                    <div class="text-xl font-bold dark:text-gray-300">
                        @lang('admin::app.clients.edit.title') – {{ $client->organization->name ?? '' }}
                    </div>
                </div>
                <button type="submit" class="primary-button">
                    @lang('admin::app.clients.edit.save-btn')
                </button>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-3 text-base font-semibold dark:text-white">@lang('admin::app.clients.view.billing-details')</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">@lang('admin::app.clients.view.billing-email')</label>
                        <input type="email" name="billing_email" value="{{ old('billing_email', $client->billing_email) }}" class="w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900" placeholder="billing@company.com">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">@lang('admin::app.clients.view.payment-terms')</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms', $client->payment_terms) }}" class="w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900" placeholder="e.g. Net 30">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">@lang('admin::app.clients.view.client-since')</label>
                        <input type="text" value="{{ $client->client_since?->format('M d, Y') }}" class="w-full rounded border border-gray-200 bg-gray-100 px-3 py-2 dark:border-gray-700 dark:bg-gray-800" readonly disabled>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium">@lang('admin::app.clients.view.notes')</label>
                        <textarea name="notes" rows="3" class="w-full rounded border border-gray-200 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">{{ old('notes', $client->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
