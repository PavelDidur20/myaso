<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    // Привязка фабрики к модели
    protected $model = Product::class;

    public function definition()
    {
        // Пример списка категорий
        $categories = ['свинина', 'говядянина', 'индейка', 'баранина', 'мебель'];

        return [
            'name'        => $this->faker->words(3, true),                      
            'description' => $this->faker->paragraph(),               
            'price'       => $this->faker->randomFloat(2, 5, 1000),               
            'category'    => $this->faker->randomElement($categories),          
            'in_stock'    => $this->faker->boolean(80),                      
        ];
    }
}
