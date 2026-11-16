<?php

use Illuminate\Support\Facades\DB;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;

/**
 * The suite runs against the installed database, so every test runs inside a
 * transaction that is rolled back to avoid leaving data behind.
 */
beforeEach(function () {
    DB::beginTransaction();
});

afterEach(function () {
    DB::rollBack();
});

function createTargetPipeline(): \Webkul\Lead\Contracts\Pipeline
{
    return app(PipelineRepository::class)->create([
        'name'        => 'Move Test Pipeline',
        'rotten_days' => 30,
        'is_default'  => 0,
        'stages'      => [
            ['code' => 'new', 'name' => 'Move New', 'probability' => 100, 'sort_order' => 1],
            ['code' => 'won', 'name' => 'Move Won', 'probability' => 100, 'sort_order' => 2],
        ],
    ]);
}

function createLeadInDefaultPipeline(array $overrides = []): int
{
    return DB::table('leads')->insertGetId(array_merge([
        'title'                  => 'Move test lead',
        'lead_value'             => 100,
        'status'                 => 1,
        'lead_pipeline_id'       => 1,
        'lead_pipeline_stage_id' => 1,
        'created_at'             => now(),
        'updated_at'             => now(),
    ], $overrides));
}

it('moves a lead to another pipeline and logs a note', function () {
    $pipeline = createTargetPipeline();
    $stage = $pipeline->stages()->first();
    $leadId = createLeadInDefaultPipeline();

    test()->actingAs(getDefaultAdmin(), 'user')
        ->putJson(route('admin.leads.pipeline.update', $leadId), [
            'lead_pipeline_id'       => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
        ])
        ->assertOk();

    $lead = app(LeadRepository::class)->find($leadId);

    expect($lead->lead_pipeline_id)->toBe($pipeline->id)
        ->and($lead->lead_pipeline_stage_id)->toBe($stage->id)
        ->and($lead->activities()->where('type', 'note')->where('comment', 'like', '%Move Test Pipeline%')->exists())->toBeTrue();
});

it('defaults to the first stage when no stage is given', function () {
    $pipeline = createTargetPipeline();
    $leadId = createLeadInDefaultPipeline();

    test()->actingAs(getDefaultAdmin(), 'user')
        ->putJson(route('admin.leads.pipeline.update', $leadId), [
            'lead_pipeline_id' => $pipeline->id,
        ])
        ->assertOk();

    expect(app(LeadRepository::class)->find($leadId)->lead_pipeline_stage_id)
        ->toBe($pipeline->stages()->first()->id);
});

it('reopens a won lead when it is moved', function () {
    $pipeline = createTargetPipeline();
    $leadId = createLeadInDefaultPipeline([
        'lead_pipeline_stage_id' => 5,
        'closed_at'              => now(),
    ]);

    test()->actingAs(getDefaultAdmin(), 'user')
        ->putJson(route('admin.leads.pipeline.update', $leadId), [
            'lead_pipeline_id' => $pipeline->id,
        ])
        ->assertOk();

    expect(app(LeadRepository::class)->find($leadId)->closed_at)->toBeNull();
});

it('rejects moving a lead to the pipeline it is already in', function () {
    $leadId = createLeadInDefaultPipeline();

    test()->actingAs(getDefaultAdmin(), 'user')
        ->putJson(route('admin.leads.pipeline.update', $leadId), [
            'lead_pipeline_id' => 1,
        ])
        ->assertStatus(422);
});

it('rejects a stage that belongs to a different pipeline', function () {
    $pipeline = createTargetPipeline();
    $leadId = createLeadInDefaultPipeline();

    test()->actingAs(getDefaultAdmin(), 'user')
        ->putJson(route('admin.leads.pipeline.update', $leadId), [
            'lead_pipeline_id'       => $pipeline->id,
            'lead_pipeline_stage_id' => 1,
        ])
        ->assertStatus(422);

    expect(app(LeadRepository::class)->find($leadId)->lead_pipeline_id)->toBe(1);
});

it('rejects an unknown pipeline', function () {
    $leadId = createLeadInDefaultPipeline();

    test()->actingAs(getDefaultAdmin(), 'user')
        ->putJson(route('admin.leads.pipeline.update', $leadId), [
            'lead_pipeline_id' => 999999,
        ])
        ->assertStatus(422);
});

it('returns the pipeline id on stages so the kanban can load more leads', function () {
    $pipeline = createTargetPipeline();

    $response = test()->actingAs(getDefaultAdmin(), 'user')
        ->getJson(route('admin.leads.get').'?pipeline_id='.$pipeline->id)
        ->assertOk();

    foreach ($response->json() as $stage) {
        expect($stage['lead_pipeline_id'])->toBe($pipeline->id);
    }
});

it('finds older products through the product lookup search', function () {
    DB::table('products')->insert([
        'name' => 'Base System Setup', 'sku' => 'BSS-1', 'price' => 10, 'quantity' => 1,
        'created_at' => now()->subYear(), 'updated_at' => now()->subYear(),
    ]);

    foreach (range(1, 25) as $i) {
        DB::table('products')->insert([
            'name' => 'Newer product '.$i, 'sku' => 'NEW-'.$i, 'price' => 10, 'quantity' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $names = collect(test()->actingAs(getDefaultAdmin(), 'user')
        ->getJson(route('admin.products.search', ['query' => 'base system']))
        ->assertOk()
        ->json('data'))->pluck('name');

    expect($names->all())->toBe(['Base System Setup']);
});

it('renders the lead create and quote create pages with the product lookup', function () {
    test()->actingAs(getDefaultAdmin(), 'user')
        ->get(route('admin.leads.create'))
        ->assertOk()
        ->assertSee('min-chars', false);

    test()->actingAs(getDefaultAdmin(), 'user')
        ->get(route('admin.quotes.create'))
        ->assertOk();
});
