<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;

class StockService
{
    /**
     * Record a stock movement and adjust product stock if required.
     */
    public function recordMovement(
        Product $product,
        int $quantityChange,
        string $type,
        int $userId,
        ?string $refType = null,
        ?int $refId = null,
        ?string $notes = null
    ): StockMovement {
        $before = $product->stock_quantity;
        $after = $before + $quantityChange;

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantityChange,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'notes' => $notes,
        ]);

        $product->update(['stock_quantity' => $after]);

        return $movement;
    }

    /**
     * Get products that reached or fell below their alert quantity.
     */
    public function getLowStockProducts(int $limit = 15): Collection
    {
        return Product::whereRaw('stock_quantity <= alert_quantity')
            ->orderBy('stock_quantity', 'asc')
            ->take($limit)
            ->get();
    }

    /**
     * Manually adjust stock for inventory audits.
     */
    public function adjustStock(Product $product, int $newQuantity, int $userId, string $reason): StockMovement
    {
        $change = $newQuantity - $product->stock_quantity;
        return $this->recordMovement(
            $product,
            $change,
            'adjustment',
            $userId,
            'manual',
            null,
            $reason
        );
    }
}
