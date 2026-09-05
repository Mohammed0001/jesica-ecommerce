<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

/**
 * Report -- and optionally repair -- products whose sold-out flag disagrees
 * with the stock they actually hold.
 *
 * OrderService used to write `is_sold_out = true` whenever a product had no
 * ProductSize row with stock, which was every product that had never had sizes
 * entered (the admin form offered no way to enter them). Those rows are still
 * flagged in the database long after the logic was fixed, so this command finds
 * them and clears the flag.
 */
class ResyncProductStock extends Command
{
    protected $signature = 'products:resync-stock
                            {--apply : Actually clear the flag; without this the command only reports}';

    protected $description = 'Find products marked sold out that are actually in stock, and optionally un-mark them';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $stuck = Product::withTrashed()
            ->with('sizes')
            ->where('is_sold_out', true)
            ->get()
            ->filter(fn (Product $product) => $product->hasStock());

        // The other half of the picture: nothing is flagged, but the storefront
        // still shows these as sold out because there is genuinely no stock.
        $empty = Product::with('sizes')
            ->where('is_sold_out', false)
            ->where('visible', true)
            ->get()
            ->reject(fn (Product $product) => $product->hasStock());

        if ($stuck->isEmpty()) {
            $this->info('No products are flagged sold out while holding stock.');
        } else {
            $this->warn($stuck->count() . ' product(s) flagged sold out despite having stock:');
            $this->table(
                ['ID', 'Title', 'One of a kind', 'Quantity', 'Sizes in stock'],
                $stuck->map(fn (Product $product) => [
                    $product->id,
                    $product->title,
                    $product->is_one_of_a_kind ? 'yes' : 'no',
                    $product->quantity,
                    $product->sizes->where('quantity', '>', 0)->count(),
                ])->all()
            );

            if ($apply) {
                foreach ($stuck as $product) {
                    $product->update(['is_sold_out' => false]);
                }

                $this->info('Cleared the sold-out flag on ' . $stuck->count() . ' product(s).');
            } else {
                $this->line('');
                $this->comment('Nothing was changed. Re-run with --apply to clear the flag on the products above.');
                $this->comment('Any product you deliberately marked sold out by hand will also be cleared, so check the list first.');
            }
        }

        if ($empty->isNotEmpty()) {
            $this->line('');
            $this->warn($empty->count() . ' visible product(s) will show as sold out because they hold no stock:');
            $this->table(
                ['ID', 'Title', 'One of a kind', 'Quantity', 'Size rows', 'Fix'],
                $empty->map(fn (Product $product) => [
                    $product->id,
                    $product->title,
                    $product->is_one_of_a_kind ? 'yes' : 'no',
                    $product->quantity,
                    $product->sizes->count(),
                    $product->sizes->isEmpty()
                        ? 'set Quantity above 0, or add sizes with stock'
                        : 'give at least one size a quantity above 0',
                ])->all()
            );
        }

        return self::SUCCESS;
    }
}
