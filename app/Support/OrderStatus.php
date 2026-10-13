<?php

namespace App\Support;

/**
 * Single source of truth for the storefront order pipeline.
 * Used by checkout, account pages, track-order and the admin manager.
 */
class OrderStatus
{
    public const PENDING = 'pending';

    public const CONFIRMED = 'confirmed';

    public const PROCESSING = 'processing';

    public const SHIPPED = 'shipped';

    public const OUT_FOR_DELIVERY = 'out_for_delivery';

    public const DELIVERED = 'delivered';

    public const CANCELLED = 'cancelled';

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public static function steps(): array
    {
        return [
            ['key' => self::PENDING, 'label' => 'Placed'],
            ['key' => self::CONFIRMED, 'label' => 'Confirmed'],
            ['key' => self::PROCESSING, 'label' => 'Processing'],
            ['key' => self::SHIPPED, 'label' => 'Shipped'],
            ['key' => self::OUT_FOR_DELIVERY, 'label' => 'Out for Delivery'],
            ['key' => self::DELIVERED, 'label' => 'Delivered'],
        ];
    }

    /**
     * @return array<int, string> step keys reached by the given status
     */
    public static function reached(string $status): array
    {
        $keys = array_column(self::steps(), 'key');
        $pos = array_search($status, $keys, true);

        if ($pos === false) {
            return $status === self::PENDING ? [] : [];
        }

        return array_slice($keys, 0, $pos + 1);
    }

    public static function label(string $status): string
    {
        foreach (self::steps() as $step) {
            if ($step['key'] === $status) {
                return $step['label'];
            }
        }

        return ucfirst($status);
    }

    public static function badge(string $status): string
    {
        return match ($status) {
            self::DELIVERED => 'bg-emerald-100 text-emerald-700',
            self::SHIPPED, self::OUT_FOR_DELIVERY => 'bg-sky-100 text-sky-700',
            self::CONFIRMED, self::PROCESSING => 'bg-indigo-100 text-indigo-700',
            self::CANCELLED => 'bg-red-100 text-brand',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function next(string $status): array
    {
        return match ($status) {
            self::PENDING => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::PROCESSING, self::CANCELLED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::OUT_FOR_DELIVERY],
            self::OUT_FOR_DELIVERY => [self::DELIVERED],
            default => [],
        };
    }

    public static function cancellable(string $status): bool
    {
        return in_array($status, [self::PENDING, self::CONFIRMED], true);
    }
}
