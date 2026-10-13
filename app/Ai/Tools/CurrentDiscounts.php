<?php

namespace App\Ai\Tools;

use App\Ai\Support\ProductFinder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CurrentDiscounts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get the products with the biggest live discounts on ChinaBayBD right now. Use this when the customer asks about discounts, deals, offers, flash sale or cheapest prices. Returns up to 5 products as JSON with discount percent.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $products = ProductFinder::discounts(
            isset($request['limit']) ? min(5, max(1, (int) $request['limit'])) : 5,
        );

        if ($products === []) {
            return 'There are no discounted products right now. Tell the customer to watch /flash-sale for upcoming deals.';
        }

        return json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'limit' => $schema->integer()->description('How many discounted products to return (1-5).')->min(1)->max(5),
        ];
    }
}
