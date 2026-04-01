<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_fee_is_recorded()
    {
        $cart = [
            ['product_id' => 1, 'unit_price' => 10.00, 'quantity' => 2, 'line_total' => 20.00],
        ];

        $data = [
            'customer_type' => 'regular',
            'delivery_fee' => true,
            'discount' => 0,
        ];

        // Create a sale directly to test delivery_fee storage
        $financials = Sale::calculateFinancials($cart, true, 0);

        $sale = Sale::create([
            'invoice_number' => 'TEST-123',
            'user_id' => 1,
            'customer_type' => 'regular',
            'delivery_fee' => $financials['delivery_fee'],
            'total' => $financials['total'],
            'paid' => $financials['paid'],
            'discount' => $financials['discount'],
        ]);

        $this->assertEquals(2.00, $sale->delivery_fee);
        $this->assertEquals(22.00, $sale->total);
        $this->assertEquals(22.00, $sale->paid);
    }

    public function test_delivery_fee_calculation()
    {
        $financials = Sale::calculateFinancials([
            ['line_total' => 50.00],
        ], true, 5.00);

        $this->assertEquals(2.00, $financials['delivery_fee']);
        $this->assertEquals(52.00, $financials['total']);
        $this->assertEquals(47.00, $financials['paid']);
    }
}
