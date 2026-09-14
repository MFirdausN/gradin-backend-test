<?php

namespace Tests\Feature;

use App\Enums\CourierLevel;
use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CourierTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace(['name' => 'Budi Santoso', 'phone' => '081234567890', 'email' => 'budi@example.com', 'level' => 3], $overrides);
    }

    public function test_index_paginates_and_sorts_by_name_with_stable_ties(): void
    {
        $second = Courier::factory()->create(['name' => 'Zainal']);
        $first = Courier::factory()->create(['name' => 'Andi']);
        Courier::factory()->create(['name' => 'Andi']);
        $this->getJson('/api/couriers?per_page=2')->assertOk()
            ->assertJsonPath('data.0.id', $first->id)->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)->assertJsonPath('meta.last_page', 2);
        $this->getJson('/api/couriers?per_page=2&page=2')->assertJsonPath('data.0.id', $second->id);
        $this->getJson('/api/couriers')->assertJsonPath('meta.per_page', 15);
    }

    public function test_index_combines_search_levels_date_sort_and_preserves_query_links(): void
    {
        Courier::factory()->create(['name' => 'Budiono Hadi Agung', 'level' => 2, 'created_at' => '2025-01-01']);
        $latest = Courier::factory()->create(['name' => 'Agung Budiman', 'level' => 3, 'created_at' => '2025-02-01']);
        Courier::factory()->create(['name' => 'Budi Agung', 'level' => 4]);
        Courier::factory()->create(['name' => 'Budi Santoso', 'level' => 2]);
        $response = $this->getJson('/api/couriers?search=budi+agung&level=2,3&sort=created_at&direction=desc&per_page=1');
        $response->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('data.0.id', $latest->id);
        parse_str(parse_url($response->json('links.next'), PHP_URL_QUERY), $query);
        $this->assertSame('budi agung', $query['search']);
        $this->assertSame('2,3', $query['level']);
        $this->getJson('/api/couriers?search=++BUDI+++AGUNG++')->assertJsonPath('meta.total', 3);
        $this->getJson('/api/couriers?search=')->assertJsonPath('meta.total', 4);
    }

    public function test_search_treats_sql_wildcards_as_literal_text(): void
    {
        Courier::factory()->create(['name' => 'Budi 100%_!']);
        Courier::factory()->create(['name' => 'Budi normal']);
        $this->getJson('/api/couriers?'.http_build_query(['search' => '%_!']))
            ->assertOk()->assertJsonPath('meta.total', 1);
    }

    #[DataProvider('invalidQueries')]
    public function test_invalid_query_is_rejected(string $query, string $field): void
    {
        $this->getJson('/api/couriers?'.$query)->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public static function invalidQueries(): array
    {
        return [['level=0', 'level.0'], ['level=2,6', 'level.1'], ['level=2,', 'level.1'],
            ['level=abc', 'level.0'], ['sort=password', 'sort'], ['direction=sideways', 'direction'],
            ['per_page=101', 'per_page'], ['page=0', 'page'], ['search[]=budi', 'search']];
    }

    public function test_store_persists_every_level_and_casts_the_enum(): void
    {
        foreach (CourierLevel::cases() as $level) {
            $payload = $this->payload(['phone' => '08123456700'.$level->value, 'email' => null, 'level' => $level->value]);
            $response = $this->postJson('/api/couriers', $payload)->assertCreated()
                ->assertJsonPath('data.level', $level->value)->assertJsonPath('data.is_active', true);
            $this->assertDatabaseHas('couriers', [...$payload, 'id' => $response->json('data.id')]);
            $this->assertSame($level, Courier::findOrFail($response->json('data.id'))->level);
        }
    }

    #[DataProvider('invalidPayloads')]
    public function test_store_and_update_validate_inputs(array $overrides, string $field): void
    {
        $courier = Courier::factory()->create();
        $this->postJson('/api/couriers', $this->payload($overrides))->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->patchJson('/api/couriers/'.$courier->id, $overrides)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('couriers', 1);
        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => $courier->name]);
    }

    public static function invalidPayloads(): array
    {
        return [[['name' => ' '], 'name'], [['name' => str_repeat('a', 256)], 'name'],
            [['phone' => 'invalid'], 'phone'], [['phone' => null], 'phone'],
            [['email' => 'invalid'], 'email'], [['level' => 0], 'level'], [['level' => 6], 'level'],
            [['level' => 2.5], 'level'], [['level' => null], 'level'], [['is_active' => 'yes'], 'is_active']];
    }

    public function test_store_requires_fields_and_rejects_duplicate_contacts(): void
    {
        $this->postJson('/api/couriers', [])->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone', 'level']);
        $courier = Courier::factory()->create($this->payload());
        $this->postJson('/api/couriers', $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['phone', 'email']);
        $other = Courier::factory()->create();
        $this->patchJson('/api/couriers/'.$other->id, $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['phone', 'email']);
        $this->patchJson('/api/couriers/'.$courier->id, $this->payload())->assertOk();
    }

    public function test_show_returns_all_courier_attributes(): void
    {
        $courier = Courier::factory()->create($this->payload());
        $this->getJson('/api/couriers/'.$courier->id)->assertOk()->assertJsonPath('data.name', 'Budi Santoso')
            ->assertJsonStructure(['data' => ['id', 'name', 'phone', 'email', 'level', 'is_active', 'created_at', 'updated_at', 'deleted_at']]);
    }

    public function test_partial_and_put_updates_persist_and_ignore_unfillable_fields(): void
    {
        $courier = Courier::factory()->create();
        $this->patchJson('/api/couriers/'.$courier->id, ['name' => 'Updated', 'deleted_at' => '2025-01-01', 'id' => 999])
            ->assertOk()->assertJsonPath('data.id', $courier->id)->assertJsonPath('data.deleted_at', null);
        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => 'Updated', 'phone' => $courier->phone]);
        $this->putJson('/api/couriers/'.$courier->id, $this->payload(['email' => null, 'is_active' => false]))->assertOk();
        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => 'Budi Santoso', 'email' => null, 'is_active' => false, 'level' => 3]);
    }

    public function test_delete_soft_deletes_and_hides_courier_but_reserves_contacts(): void
    {
        $courier = Courier::factory()->create($this->payload());
        $this->deleteJson('/api/couriers/'.$courier->id)->assertNoContent();
        $this->assertSoftDeleted($courier);
        $this->assertDatabaseHas('couriers', ['id' => $courier->id]);
        $this->getJson('/api/couriers')->assertJsonCount(0, 'data');
        $this->getJson('/api/couriers/'.$courier->id)->assertNotFound();
        $this->patchJson('/api/couriers/'.$courier->id, ['name' => 'Changed'])->assertNotFound();
        $this->deleteJson('/api/couriers/'.$courier->id)->assertNotFound();
        $this->postJson('/api/couriers', $this->payload())->assertUnprocessable()->assertJsonValidationErrors(['phone', 'email']);
    }

    #[DataProvider('deletionStates')]
    public function test_force_delete_removes_active_or_trashed_couriers_and_releases_contacts(bool $trashed): void
    {
        $courier = Courier::factory()->create($this->payload());
        $other = Courier::factory()->create();

        if ($trashed) {
            $this->deleteJson('/api/couriers/'.$courier->id)->assertNoContent();
            $this->assertSoftDeleted($courier);
        }

        $this->deleteJson('/api/couriers/'.$courier->id.'/force')->assertNoContent();
        $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
        $this->assertDatabaseHas('couriers', ['id' => $other->id]);
        $this->getJson('/api/couriers')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $other->id);
        $this->getJson('/api/couriers/'.$courier->id)->assertNotFound();
        $this->patchJson('/api/couriers/'.$courier->id, ['name' => 'Changed'])->assertNotFound();
        $this->deleteJson('/api/couriers/'.$courier->id.'/force')->assertNotFound();
        $this->postJson('/api/couriers', $this->payload())->assertCreated();
    }

    public static function deletionStates(): array
    {
        return ['active' => [false], 'soft deleted' => [true]];
    }

    public function test_deleted_filter_combines_with_search_levels_and_pagination(): void
    {
        Courier::factory()->create(['name' => 'Budi Agung', 'level' => 2]);
        $deleted = Courier::factory()->create(['name' => 'Budiono Hadi Agung', 'level' => 2]);
        $deleted->delete();
        Courier::factory()->create(['name' => 'Budi Agung', 'level' => 3])->delete();
        Courier::factory()->create(['name' => 'Budi Santoso', 'level' => 2])->delete();

        $this->getJson('/api/couriers')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/couriers?trashed=with')->assertJsonPath('meta.total', 4);
        $response = $this->getJson('/api/couriers?trashed=only&search=budi+agung&level=2,3&per_page=1&sort=name&direction=desc');
        $response->assertOk()->assertJsonPath('meta.total', 2)->assertJsonPath('data.0.id', $deleted->id);
        $this->assertNotNull($response->json('data.0.deleted_at'));
        parse_str(parse_url($response->json('links.next'), PHP_URL_QUERY), $query);
        $this->assertSame('only', $query['trashed']);
        $this->getJson('/api/couriers?trashed=invalid')->assertUnprocessable()->assertJsonValidationErrors('trashed');
    }

    public function test_restore_returns_deleted_courier_to_default_list_and_preserves_attributes(): void
    {
        $courier = Courier::factory()->create(['is_active' => false]);
        $courier->delete();
        $this->patchJson('/api/couriers/'.$courier->id.'/restore')->assertOk()
            ->assertJsonPath('data.id', $courier->id)->assertJsonPath('data.deleted_at', null)
            ->assertJsonPath('data.is_active', false);
        $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'deleted_at' => null, 'phone' => $courier->phone]);
        $this->getJson('/api/couriers')->assertJsonPath('data.0.id', $courier->id);
        $this->getJson('/api/couriers?trashed=only')->assertJsonCount(0, 'data');
        $this->getJson('/api/couriers/'.$courier->id)->assertOk();
        $this->patchJson('/api/couriers/'.$courier->id.'/restore')->assertConflict();
        $this->deleteJson('/api/couriers/'.$courier->id.'/force')->assertNoContent();
        $this->patchJson('/api/couriers/'.$courier->id.'/restore')->assertNotFound();
        $this->patchJson('/api/couriers/999999/restore')->assertNotFound();
    }

    public function test_missing_resources_return_json_not_found(): void
    {
        $this->get('/api/couriers/999')->assertNotFound()->assertHeader('Content-Type', 'application/json');
        $this->putJson('/api/couriers/999', $this->payload())->assertNotFound();
        $this->deleteJson('/api/couriers/999')->assertNotFound();
        $this->deleteJson('/api/couriers/999/force')->assertNotFound();
    }
}
