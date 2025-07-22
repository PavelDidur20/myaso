<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ProductOutOfStockException;
use App\Models\Product;

class OrderService
{
     /**
     * Создает новый заказ 
     *
     * @param array $data 
     * @return Order 
     * @throws ProductOutOfStockException  Если нет товара.
     */

    public function createOrder(array $data)
    {
       return DB::transaction(function () use ($data) {
        $items = $data['items'];
        $user = $data['user_id'];
        
       
        $productIds = array_column($items, 'product_id');
        

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        
    
        $totalPrice = 0;
        $outOfStock = [];

        foreach ($items as $item) {
            $product = $products[$item['product_id']] ?? null;
            
            if (!$product) {
                throw new Exception("Товара не найдено: {$item['product_id']}");
            }
            
            if (!$product->in_stock) {
                $outOfStock[] = $product->name;
                continue;
            }
            
            $totalPrice += round($product->price * $item['count'], 2);
        }

        if (!empty($outOfStock)) 
            throw new ProductOutOfStockException($outOfStock);
    

    
        $order = Order::create([
            'user_id' => $user,
            'created_at' => now(),
            'comment' => $data['comment'] ?? null,
            'total_price' => (int) round($totalPrice),
        ]);


        foreach ($items as $item) {
            $product = $products[$item['product_id']];
            
            OrderItem::create([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'count' => $item['count'],
                'price' => $product->price, 
            ]);
        }

        return $order;
    });
}
}