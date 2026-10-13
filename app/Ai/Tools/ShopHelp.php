<?php

namespace App\Ai\Tools;

use App\Models\Setting;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ShopHelp implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Answer general ChinaBayBD questions: human support contact details, shipping/delivery charges, returns and refunds, order tracking, payment and cash on delivery. Call this with the matching topic instead of guessing.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $topic = mb_strtolower((string) ($request['topic'] ?? 'general'));

        $contact = [
            'phone' => Setting::get('contact.phone', '+8801849415277'),
            'email' => Setting::get('contact.email', 'info@chinabaybd.com'),
            'website' => Setting::get('contact.website', 'www.chinabaybd.com'),
            'address' => Setting::get('contact.address', 'Dhaka, Bangladesh'),
            'hours' => Setting::get('contact.hours', '9am–11pm, replies within 2 hours'),
            'contact_page' => '/contact',
        ];

        if (str_contains($topic, 'human') || str_contains($topic, 'contact') || str_contains($topic, 'agent') || str_contains($topic, 'person') || str_contains($topic, 'call') || str_contains($topic, 'phone') || str_contains($topic, 'support')) {
            return 'Human support: phone '.$contact['phone'].', email '.$contact['email'].', website '.$contact['website'].', address '.$contact['address'].' ('.$contact['hours'].'). Contact page: '.$contact['contact_page'].'. Share these details and the contact page link with the customer.';
        }

        if (str_contains($topic, 'ship') || str_contains($topic, 'deliver') || str_contains($topic, 'charge')) {
            return 'Delivery across Bangladesh: Dhaka ৳60 inside / ৳100 outside; Chattogram ৳80/৳120; other districts ৳90–100 inside / ৳130–140 outside. Cash on delivery available. More: /shipping.';
        }

        if (str_contains($topic, 'return') || str_contains($topic, 'refund') || str_contains($topic, 'exchange')) {
            return 'Easy returns and refunds on eligible items. Ask the customer for their order ID and point them to /returns for the policy and /contact for help. More: /returns.';
        }

        if (str_contains($topic, 'track') || str_contains($topic, 'order') || str_contains($topic, 'status') || str_contains($topic, 'kothay') || str_contains($topic, 'kobe')) {
            return 'Orders can be tracked at /track-order using the order ID or phone number. Order history for logged-in customers is at /account/orders.';
        }

        if (str_contains($topic, 'pay') || str_contains($topic, 'cod') || str_contains($topic, 'bkash') || str_contains($topic, 'nagad')) {
            return 'We accept cash on delivery across Bangladesh plus online payment. Checkout is at /checkout and help is at /contact.';
        }

        return 'ChinaBayBD sells China-imported electronics, gadgets, home appliances, fashion and lifestyle products with cash on delivery across Bangladesh. Shop: /shop. Deals: /flash-sale. Help: /contact ('.$contact['phone'].', '.$contact['email'].').';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'topic' => $schema->string()->description('One of: human, shipping, returns, tracking, payment, or general.')->required(),
        ];
    }
}
