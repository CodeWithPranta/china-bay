<?php

namespace App\Ai\Support;

use App\Models\Product;
use App\Support\ShopDemo;
use Illuminate\Support\Collection;

/**
 * Live catalogue lookup shared by the concierge tools, the chat
 * controller fallback, and the product cards in the chat response.
 *
 * Reads active products from the database; falls back to the static
 * demo catalogue so fresh installs still answer sensibly.
 */
class ProductFinder
{
    /**
     * Search active products by keywords with an optional budget cap.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function search(string $query, ?int $maxPrice = null, int $limit = 5): array
    {
        $cap = $maxPrice ?? self::parseBudget($query);
        $words = self::keywords($query);
        $all = self::all();

        if ($all->isEmpty()) {
            return [];
        }

        $scored = $all
            ->map(fn (array $p) => [self::score($p, $words), $p])
            ->filter(fn (array $row) => ($row[0] > 0 || $words === []) && ($cap === null || $row[1]['price'] <= $cap))
            ->sort(fn (array $a, array $b) => $b[0] <=> $a[0] ?: self::tiebreak($a[1], $b[1], $cap, $words))
            ->values();

        return $scored->take(max(1, $limit))->map(fn (array $row) => $row[1])->all();
    }

    /**
     * Order equally-scored products: cheapest first when the shopper gave
     * keywords or a budget, most-loved first for open requests like "gift".
     *
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     * @param  array<int, string>  $words
     */
    private static function tiebreak(array $a, array $b, ?int $cap, array $words): int
    {
        if ($words !== [] || $cap !== null) {
            return $a['price'] <=> $b['price'];
        }

        return [$b['rating'] ?? 0, $b['reviews'] ?? 0] <=> [$a['rating'] ?? 0, $a['reviews'] ?? 0];
    }

    /**
     * Products with the biggest discounts first.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function discounts(int $limit = 5): array
    {
        return self::all()
            ->filter(fn (array $p) => ($p['off'] ?? 0) > 0)
            ->sortByDesc('off')
            ->take(max(1, $limit))
            ->values()
            ->all();
    }

    /**
     * Load every active product as a chat-card array.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function all(): Collection
    {
        try {
            $rows = Product::active()->with(['category', 'media'])->orderBy('id')->get();

            if ($rows->isNotEmpty()) {
                return $rows->map(fn (Product $p) => self::card($p->toCardArray()))->collect();
            }
        } catch (\Throwable) {
            // Fall through to the demo catalogue.
        }

        return collect(ShopDemo::products())->map(fn (array $p) => self::card($p));
    }

    /**
     * Normalize a card shape so chat clients always get the same keys.
     *
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    public static function card(array $card): array
    {
        $card['url'] = '/product/'.($card['slug'] ?? '');

        return $card;
    }

    /**
     * Extract a budget cap (BDT) from free text.
     *
     * Accepts cue words ("under", "within", "budget", "in 1000", …) as
     * well as bare "৳1500" / "1500tk" / "1500 taka" amounts.
     */
    public static function parseBudget(string $query): ?int
    {
        $text = mb_strtolower(str_replace([',', '৳'], ['', ' '], $query));

        $patterns = [
            '/\b(?:under|below|less than|within|inside|budget|max|up ?to|upto|in|kom|porjonto)\s+(\d[\d\s]*)/u',
            '/(\d[\d\s]*)\s*(?:taka|takas|tk|bdt)\b/u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $amount = (int) preg_replace('/\s+/', '', $m[1]);

                if ($amount > 0) {
                    return $amount;
                }
            }
        }

        return null;
    }

    /**
     * Words that carry no product signal on their own.
     *
     * @var array<int, string>
     */
    private const STOP = ['the', 'a', 'an', 'for', 'me', 'my', 'show', 'want', 'need', 'buy', 'under', 'below', 'within', 'inside', 'in', 'of', 'and', 'with', 'have', 'has', 'get', 'got', 'like', 'something', 'one', 'taka', 'takas', 'tk', 'bdt', 'pls', 'please', 'any', 'best', 'good', 'gift', 'gifts', 'present', 'presents', 'shirt', 'shirts'];

    /**
     * Shopper words mapped to catalogue words, e.g. kids → toy.
     *
     * @var array<string, array<int, string>>
     */
    private const SYNONYMS = [
        'kid' => ['toy', 'toys', 'baby'],
        'kids' => ['toy', 'toys', 'baby'],
        'child' => ['toy', 'toys', 'baby'],
        'children' => ['toy', 'toys', 'baby'],
        'baby' => ['toy', 'toys', 'kid', 'kids'],
        'babies' => ['toy', 'toys'],
        'toy' => ['toys', 'kid', 'kids'],
        'toys' => ['toy', 'kid', 'kids'],
        'game' => ['toy', 'toys'],
        'games' => ['toy', 'toys'],
        'shirt' => ['tshirt', 'tee', 'polo'],
        'shirts' => ['tshirt', 'tee', 'polo'],
        'tshirt' => ['shirt', 'tee', 'polo'],
        'tee' => ['shirt', 'tshirt'],
    ];

    /**
     * @return array<int, string>
     */
    public static function keywords(string $query): array
    {
        $text = mb_strtolower($query);
        $raw = preg_split('/[^a-z0-9\x{0980}-\x{09FF}]+/u', $text) ?: [];

        $words = [];
        foreach ($raw as $w) {
            $w = trim((string) $w);

            if ($w === '' || is_numeric($w) || mb_strlen($w) < 2 || in_array($w, self::STOP, true)) {
                continue;
            }

            $words[] = $w;

            foreach (self::SYNONYMS[$w] ?? [] as $alt) {
                $words[] = $alt;
            }
        }

        // Keep "shirt" searchable but don't let the plural dominate scoring.
        if ($words === [] && preg_match('/shirt|t-?shirt|tee|panjabi|kurta/u', $text)) {
            $words[] = 'shirt';
        }

        return array_values(array_unique($words));
    }

    /**
     * @param  array<string, mixed>  $product
     * @param  array<int, string>  $words
     */
    private static function score(array $product, array $words): int
    {
        $hay = self::normalize(($product['name'] ?? '').' '.($product['sub'] ?? '').' '.($product['cat'] ?? ''));
        $score = 0;

        foreach ($words as $w) {
            $needle = self::normalize($w);

            if ($needle !== '' && str_contains($hay, $needle)) {
                $score += mb_strlen($w) > 4 ? 2 : 1;
            }
        }

        return $score;
    }

    private static function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9\x{0980}-\x{09FF}]+/u', '', mb_strtolower($value)) ?? '';
    }
}
