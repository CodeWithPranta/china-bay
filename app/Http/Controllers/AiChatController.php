<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ShopConcierge;
use App\Ai\Support\ProductFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Enums\Lab;

class AiChatController extends Controller
{
    /**
     * Chat with the Bay AI concierge.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:12'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1000'],
        ]);

        $message = trim($data['message']);
        $history = array_values($data['history'] ?? []);
        $user = $request->user();

        $products = $this->cardsFor($message);

        try {
            $prompt = $this->promptWithShopperContext($message, $user?->name, $user?->loyalty_points);

            $response = ShopConcierge::make(customer: $user, history: $history)->prompt(
                $prompt,
                provider: Lab::OpenRouter,
                model: config('ai.shop_concierge.model', 'openai/gpt-4o-mini'),
            );

            return response()->json([
                'reply' => trim($response->text),
                'products' => $products,
                'source' => 'ai',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Bay AI concierge fell back to local answers.', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => $this->localReply($message, $user?->name, $products),
                'products' => $products,
                'source' => 'local',
            ]);
        }
    }

    /**
     * Product cards relevant to the message (empty for help-type queries).
     *
     * @return array<int, array<string, mixed>>
     */
    private function cardsFor(string $message): array
    {
        if ($this->isHelpIntent($message)) {
            return [];
        }

        return ProductFinder::search($message, limit: 5);
    }

    private function isHelpIntent(string $message): bool
    {
        return (bool) preg_match('/human|real person|real-person|agent|contact|phone|number|call|support|ship|deliver|return|refund|track|order status|payment|cod|bkash|nagad|kothay|kobe|discount|offer|deal|flash sale|off\b/i', $message)
            && ProductFinder::keywords($message) === []
            && ProductFinder::parseBudget($message) === null;
    }

    private function promptWithShopperContext(string $message, ?string $name, mixed $loyaltyPoints): string
    {
        if ($name === null) {
            return 'Shopper context: guest browsing (not logged in). Customer message: '.$message;
        }

        return 'Shopper context: logged in as '.$name.' with '.(int) $loyaltyPoints.' loyalty points. Customer message: '.$message;
    }

    /**
     * Deterministic reply when the AI provider is unreachable.
     *
     * @param  array<int, array<string, mixed>>  $products
     */
    private function localReply(string $message, ?string $name, array $products): string
    {
        $greeting = $name ? 'Thanks '.$this->firstName($name).'! ' : 'Thanks! ';

        if (preg_match('/human|real person|real-person|agent|person|call|contact|support/i', $message)) {
            return $greeting.'You can reach our human team at +8801849415277 (9am–11pm, replies within 2 hours) or info@chinabaybd.com — or send a message from our contact page: /contact.';
        }

        if (preg_match('/track|kothay|kobe|order.*(status|where)/i', $message)) {
            return $greeting.'You can track any order at /track-order with your order ID or phone number. Your order history is at /account/orders.';
        }

        if (preg_match('/discount|offer|deal|flash sale|off\b|cheap/i', $message)) {
            return $greeting.'Here are our biggest live discounts right now:';
        }

        if (preg_match('/ship|deliver|charge|delivery/i', $message)) {
            return $greeting.'We deliver across Bangladesh with cash on delivery — Dhaka ৳60/৳100, Chattogram ৳80/৳120, other districts ৳90+ — details at /shipping.';
        }

        if (preg_match('/return|refund|exchange/i', $message)) {
            return $greeting.'Easy returns on eligible items — see /returns for the policy or /contact and our team will help with your order.';
        }

        if ($products !== []) {
            $budget = ProductFinder::parseBudget($message);

            return $greeting.($budget ? 'Here are my top picks under ৳'.number_format($budget).':' : 'Here is what I found for you:');
        }

        return $greeting.'Tell me what you are looking for (product + budget, e.g. "t-shirt in 1000") and I will pick from our collection. For human help: +8801849415277 or /contact.';
    }

    private function firstName(string $name): string
    {
        return explode(' ', trim($name))[0] ?? $name;
    }
}
