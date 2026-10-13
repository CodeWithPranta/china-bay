<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dummy storefront orders for testing checkout → tracking → delivery.
 * Run manually: php artisan db:seed --class=DemoOrderSeeder
 * NOT part of DatabaseSeeder (keeps production clean).
 */
class DemoOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::active()->orderBy('id')->take(6)->get();

        if ($products->count() < 2) {
            $this->command->warn('Seed the catalogue first (CatalogSeeder).');

            return;
        }

        $dummies = [
            ['Rahim Uddin', '01711222333', 'House 12, Road 5, Dhanmondi', 'Dhaka', 'Dhanmondi', 'inside', 'cod', 'pending'],
            ['Nusrat Jahan', '01811444555', 'GEC Circle, Nasirabad', 'Chattogram', 'GEC Circle', 'outside', 'bkash', 'confirmed'],
            ['Tanvir Ahmed', '01911666777', 'Zindabazar Point', 'Sylhet', 'Zindabazar', 'outside', 'cod', 'processing'],
            ['Mim Akter', '01611888999', 'House 3, Mirpur 10', 'Dhaka', 'Mirpur', 'inside', 'nagad', 'shipped'],
            ['Arif Hossain', '01511000111', 'Sonadanga R/A', 'Khulna', 'Sonadanga', 'outside', 'cod', 'out_for_delivery'],
            ['Sadia Rahman', '01311222444', 'Shaheb Bazar Zero Point', 'Rajshahi', 'Shaheb Bazar', 'outside', 'cod', 'delivered'],
            ['Farhana Islam', '01799112233', 'House 8, Uttara 11', 'Dhaka', 'Uttara', 'inside', 'cod', 'cancelled'],
        ];

        foreach ($dummies as $i => [$name, $phone, $address, $district, $thana, $method, $pay, $status]) {
            $code = 'CB-'.now()->format('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            if (Order::where('code', $code)->exists()) {
                continue;
            }

            DB::transaction(function () use ($products, $i, $code, $name, $phone, $address, $district, $thana, $method, $pay, $status) {
                $pick = $products->skip($i % $products->count())->take(2);
                $subtotal = 0;

                foreach ($pick as $p) {
                    $subtotal += (float) $p->price;
                }

                $delivery = $method === 'inside' ? 60 : 120;

                $order = Order::create([
                    'code' => $code,
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
                    'district' => $district,
                    'thana' => $thana,
                    'delivery_method' => $method,
                    'delivery_charge' => $delivery,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'total' => $subtotal + $delivery,
                    'payment_method' => $pay,
                    'payment_status' => 'unpaid',
                    'status' => $status,
                ]);

                foreach ($pick as $p) {
                    $order->items()->create([
                        'product_id' => $p->id,
                        'name' => $p->name,
                        'unit_price' => $p->price,
                        'qty' => 1,
                    ]);
                }
            });
        }

        $this->command->info('Demo orders seeded. Track e.g. CB-'.now()->format('Ymd').'-0001 / 01711222333.');
    }
}
