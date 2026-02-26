<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * Fires after a new product has been saved to the database.
     */
    public function created(Product $product): void
    {
        // Example: index the new product in a search engine.
        // Example: notify the inventory team of the new listing.
    }

    /**
     * Handle the Product "updated" event.
     *
     * Fires after a product record has been changed and saved.
     *
     * Cascade: Check whether the stock has just dropped to zero.
     * If it has, this is the right place to fire a low-stock notification
     * or trigger a reorder process.
     *
     * wasChanged() returns true only if that column changed in this save,
     * so this block does NOT run on every update — only when stock changes.
     */
    public function updated(Product $product): void
    {
        $stockWasChanged = $product->wasChanged('stock');

        if ($stockWasChanged && $product->stock <= 0) {
            // Example: event(new ProductOutOfStock($product));
            // Example: Mail::to('inventory@example.com')->send(new LowStockMail($product));
        }
    }

    /**
     * Handle the Product "deleting" event.
     *
     * Fires BEFORE the product is removed from the database.
     * Clean up any related records here while the product still exists.
     */
    public function deleting(Product $product): void
    {
        // Example: remove this product from any active shopping carts.
        // Example: cancel any pending purchase orders for this product.
    }

    /**
     * Handle the Product "deleted" event.
     *
     * Fires AFTER the product has been removed from the database.
     */
    public function deleted(Product $product): void
    {
        // Example: remove the product from a search index.
        // Example: notify connected systems that this SKU no longer exists.
    }
}
