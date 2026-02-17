<?php

namespace Webkul\Admin\DataGrids\Client;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ClientDataGrid extends DataGrid
{
    public function prepareQueryBuilder(): Builder
    {
        $prefix = DB::getTablePrefix();
        $queryBuilder = DB::table('clients')
            ->leftJoin('organizations', 'clients.organization_id', '=', 'organizations.id')
            ->addSelect(
                'clients.id',
                'clients.organization_id',
                'clients.client_since',
                'clients.billing_email',
                'clients.payment_terms',
                'clients.created_at',
                'organizations.name as organization_name',
                DB::raw("(SELECT COUNT(*) FROM {$prefix}leads INNER JOIN {$prefix}persons ON {$prefix}leads.person_id = {$prefix}persons.id WHERE {$prefix}persons.organization_id = {$prefix}clients.organization_id) as leads_count")
            );

        // Clients list shows all organizations that have won leads; no per-user filtering

        $this->addFilter('id', 'clients.id');
        $this->addFilter('organization_name', 'organizations.name');

        return $queryBuilder;
    }

    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('admin::app.clients.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'organization_name',
            'label'      => trans('admin::app.clients.index.datagrid.organization'),
            'type'       => 'string',
            'sortable'   => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'leads_count',
            'label'      => trans('admin::app.clients.index.datagrid.leads-count'),
            'type'       => 'integer',
            'sortable'   => true,
            'filterable' => false,
            'closure'    => fn ($row) => (int) $row->leads_count,
        ]);

        $this->addColumn([
            'index'           => 'client_since',
            'label'           => trans('admin::app.clients.index.datagrid.client-since'),
            'type'            => 'date',
            'searchable'      => false,
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
            'closure'         => fn ($row) => $row->client_since ? core()->formatDate($row->client_since) : '—',
        ]);

        $this->addColumn([
            'index'      => 'billing_email',
            'label'      => trans('admin::app.clients.index.datagrid.billing-email'),
            'type'       => 'string',
            'sortable'   => false,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'payment_terms',
            'label'      => trans('admin::app.clients.index.datagrid.payment-terms'),
            'type'       => 'string',
            'sortable'   => false,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('admin::app.settings.tags.index.datagrid.created-at'),
            'type'            => 'date',
            'searchable'      => false,
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
            'closure'         => fn ($row) => core()->formatDate($row->created_at),
        ]);
    }

    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('clients.view')) {
            $this->addAction([
                'icon'   => 'icon-eye',
                'title'  => trans('admin::app.clients.index.datagrid.view'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.clients.view', $row->id),
            ]);
            $this->addAction([
                'icon'   => 'icon-activity',
                'title'  => trans('admin::app.clients.index.datagrid.view-sla'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.clients.view', $row->id) . '#sla',
            ]);
            $this->addAction([
                'icon'   => 'icon-download',
                'title'  => trans('admin::app.clients.index.datagrid.view-documents'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.clients.view', $row->id) . '#documents',
            ]);
            $this->addAction([
                'icon'   => 'icon-leads',
                'title'  => trans('admin::app.clients.index.datagrid.view-leads'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.clients.leads.index', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('clients.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('admin::app.clients.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('admin.clients.edit', $row->id),
            ]);
        }
    }

    public function prepareMassActions(): void
    {
        // No mass delete by default to avoid accidental removal of client records
    }
}
