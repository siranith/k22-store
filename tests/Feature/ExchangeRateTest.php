<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageExchangeRate;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_get_and_set_exchange_rate()
    {
        // Default when not set
        $this->assertEquals(4000, Setting::getExchangeRate());

        // Update rate
        Setting::setExchangeRate(4100);
        $this->assertEquals(4100, Setting::getExchangeRate());

        // Test database record
        $this->assertDatabaseHas('settings', [
            'key' => 'usd_to_khr_rate',
            'value' => '4100',
        ]);
    }

    public function test_sale_creation_snapshots_current_exchange_rate()
    {
        Setting::setExchangeRate(4150);

        $cart = [
            ['product_id' => 1, 'unit_price' => 10.00, 'quantity' => 1, 'line_total' => 10.00],
        ];

        $data = [
            'customer_type' => 'regular',
            'cod' => true,
        ];

        // Ensure user exists
        $user = User::factory()->create();

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-001',
            'user_id' => $user->id,
            'customer_type' => 'regular',
            'delivery_fee' => 0,
            'total' => 10.00,
            'paid' => 10.00,
            'discount' => 0,
            'cod' => true,
            'exchange_rate' => Setting::getExchangeRate(),
        ]);

        $this->assertEquals(4150, $sale->exchange_rate);
    }

    public function test_old_sale_without_rate_falls_back_to_global_setting()
    {
        Setting::setExchangeRate(4050);

        $user = User::factory()->create();

        // Simulate legacy sale where exchange_rate column was null
        $sale = Sale::create([
            'invoice_number' => 'INV-LEGACY-001',
            'user_id' => $user->id,
            'customer_type' => 'regular',
            'delivery_fee' => 0,
            'total' => 10.00,
            'paid' => 10.00,
            'discount' => 0,
            'cod' => true,
            'exchange_rate' => null,
        ]);

        $this->assertEquals(4050, $sale->exchange_rate);
    }

    public function test_manage_exchange_rate_page_can_update_rate()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ManageExchangeRate::class)
            ->assertSuccessful()
            ->set('data.usd_to_khr_rate', 4120)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals(4120, Setting::getExchangeRate());
    }
}
