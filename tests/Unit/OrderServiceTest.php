<?php
namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_order_with_items_and_calculates_total_price()
    {
        $user = User::factory()->create();

        $product1 = Product::factory()->create(['price' => 100]);
        $product2 = Product::factory()->create(['price' => 200]);

        $data = [
            'user_id' => $user->id,
            'comment' => 'Test comment',
            'items' => [
                ['product_id' => $product1->id, 'count' => 2],
                ['product_id' => $product2->id, 'count' => 3],
            ]
        ];

        $service = new OrderService();
        $order = $service->createOrder($data);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $user->id,
            'comment' => 'Test comment',
        ]);

        $this->assertDatabaseCount('order_items', 2);

        $expectedTotal = 2 * 100 + 3 * 200;
        $this->assertEquals($expectedTotal, $order->fresh()->total_price);
    }
}
