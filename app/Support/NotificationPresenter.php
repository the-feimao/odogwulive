<?php

namespace App\Support;

use App\Models\AppUser;
use App\Models\Notification;
use App\Models\Order;

/**
 * Prepares notifications for web pages without changing the appended fields
 * used by the organization API.
 */
class NotificationPresenter
{
    public function forOrganizer(int $organizerId)
    {
        $notifications = Notification::where('organizer_id', $organizerId)
            ->orderBy('id', 'DESC')
            ->get();

        $this->prepare($notifications);

        return $notifications;
    }

    public function forUser(int $userId)
    {
        $notifications = Notification::where(function ($query) use ($userId) {
            $query->where('organizer_id', $userId)
                ->orWhere('user_id', $userId);
        })->orderBy('id', 'DESC')->get();

        $this->prepare($notifications);

        return $notifications;
    }

    public function prepare($notifications): void
    {
        $notifications->each->setAppends([]);
        $orderIds = $notifications->pluck('order_id')->filter()->unique()->values();

        if ($orderIds->isEmpty()) {
            foreach ($notifications as $notification) {
                $notification->actor = null;
            }

            return;
        }

        $orders = Order::whereIn('id', $orderIds)->get()->keyBy('id');
        $customerIds = $orders->pluck('customer_id')->filter()->unique()->values();
        $customers = $customerIds->isNotEmpty()
            ? AppUser::whereIn('id', $customerIds)->get(['id', 'name', 'last_name', 'image', 'email'])->keyBy('id')
            : collect();

        foreach ($notifications as $notification) {
            $order = $orders->get($notification->order_id);
            $notification->actor = $order ? $customers->get($order->customer_id) : null;
        }
    }
}
