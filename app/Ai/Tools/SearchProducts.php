<?php

namespace App\Ai\Tools;

use App\Ai\Support\ProductFinder;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProducts implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search the ChinaBayBD live product catalogue by keywords with an optional max price in BDT. Always use this when the customer asks for product suggestions, e.g. "t-shirt in 1000". Returns up to 5 products as JSON with name, price, old_price, discount percent, stock and product URL.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $products = ProductFinder::search(
            (string) $request['query'],
            isset($request['max_price']) ? (int) $request['max_price'] : null,
            isset($request['limit']) ? min(5, max(1, (int) $request['limit'])) : 5,
        );

        if ($products === []) {
            return 'No products matched. Tell the customer nothing matches right now and point them to /shop to browse everything.';
        }

        return json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Customer search text, e.g. "t-shirt", "gift for kids", "wireless earbuds".')->required(),
            'max_price' => $schema->integer()->description('Optional budget cap in BDT, e.g. 1000.')->min(1),
            'limit' => $schema->integer()->description('How many products to return (1-5).')->min(1)->max(5),
        ];
    }
}
