<?php

namespace App\Exceptions;

use Exception;

class ProductOutOfStockException extends Exception
{
    protected $outOfStockProducts;

    /**
     * Создать новое исключение
     *
     * @param array $products Названия отсутствующих товаров
     * @param int $code Код ошибки
     * @param Exception|null $previous Предыдущее исключение
     */
    public function __construct(array $products = [], $code = 422, Exception $previous = null)
    {
        $this->outOfStockProducts = $products;
        
        $message = !empty($products)
            ? 'Некоторые товары отсутствуют на складе: ' . implode(', ', $products)
            : 'Один или несколько товаров отсутствуют на складе';
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Получить список отсутствующих товаров
     *
     * @return array
     */
    public function getOutOfStockProducts()
    {
        return $this->outOfStockProducts;
    }
}