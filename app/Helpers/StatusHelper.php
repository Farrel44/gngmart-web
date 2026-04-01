<?php

namespace App\Helpers;

use App\Models\Order;
use App\Models\Payment;

class StatusHelper
{
    /**
     * Label status pesanan untuk ditampilkan di UI (Bahasa Indonesia).
     */
    public static function getOrderStatusLabel(string $status): string
    {
        return Order::getStatusLabels()[$status] ?? $status;
    }

    /**
     * Label status pembayaran untuk ditampilkan di UI (Bahasa Indonesia).
     */
    public static function getPaymentStatusLabel(string $status): string
    {
        return Payment::getStatusLabels()[$status] ?? $status;
    }

    /**
     * Label metode pembayaran untuk ditampilkan di UI (Bahasa Indonesia).
     */
    public static function getPaymentMethodLabel(string $method): string
    {
        return Payment::getMethodLabels()[$method] ?? $method;
    }
}
