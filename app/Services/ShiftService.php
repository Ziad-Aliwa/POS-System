<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shift;
use Carbon\Carbon;
use Exception;

class ShiftService
{
    /**
     * Retrieve the currently open shift for the given user, if any.
     */
    public function getActiveShift(int $userId): ?Shift
    {
        return Shift::where('user_id', $userId)
            ->where('status', 'open')
            ->latest()
            ->first();
    }

    /**
     * Start a new shift with initial opening cash.
     */
    public function openShift(int $userId, float $openingCash, ?string $notes = null): Shift
    {
        $existing = $this->getActiveShift($userId);
        if ($existing) {
            throw new Exception('You already have an active shift (#'.$existing->id.') open.');
        }

        return Shift::create([
            'user_id' => $userId,
            'opening_cash' => $openingCash,
            'status' => 'open',
            'opened_at' => Carbon::now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Record an order's payment inside the shift.
     */
    public function recordSale(Shift $shift, Order $order): void
    {
        $method = $order->payment_method ?? 'cash';
        $total = $order->total_price;
        $discount = $order->discount ?? 0;

        $updateData = [
            'total_orders' => $shift->total_orders + 1,
            'total_discount' => $shift->total_discount + $discount,
        ];

        if ($method === 'cash') {
            $updateData['cash_sales'] = $shift->cash_sales + $total;
        } elseif ($method === 'card') {
            $updateData['card_sales'] = $shift->card_sales + $total;
        } elseif ($method === 'transfer') {
            $updateData['transfer_sales'] = $shift->transfer_sales + $total;
        }

        $shift->update($updateData);
    }

    /**
     * Close the shift, calculate discrepancies (expected vs actual), and mark closed.
     */
    public function closeShift(Shift $shift, float $actualCash, ?string $notes = null): Shift
    {
        $expectedCash = $shift->opening_cash + $shift->cash_sales;

        $shift->update([
            'closing_cash' => $actualCash,
            'expected_cash' => $expectedCash,
            'status' => 'closed',
            'closed_at' => Carbon::now(),
            'notes' => $notes ? ($shift->notes . "\nClosing notes: " . $notes) : $shift->notes,
        ]);

        return $shift->fresh();
    }
}
