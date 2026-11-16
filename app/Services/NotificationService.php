<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send booking confirmation.
     */
    public function sendBookingNotification(Shipment $shipment): void
    {
        $content = sprintf(
            'Hello %s, your shipment with AWB %s has been booked to %s. Total: $%s. Track online anytime.',
            $shipment->sender_name,
            $shipment->awb_number,
            $shipment->receiver_city,
            number_format($shipment->total_freight, 2)
        );

        $this->logNotification($shipment->company_id, 'sms', $shipment->sender_phone, 'Shipment Booked', $content);

        if ($shipment->receiver_phone) {
            $receiverContent = sprintf(
                'Hello %s, a package with AWB %s from %s is on its way to you.',
                $shipment->receiver_name,
                $shipment->awb_number,
                $shipment->sender_name
            );
            $this->logNotification($shipment->company_id, 'sms', $shipment->receiver_phone, 'Shipment Incoming', $receiverContent);
        }
    }

    /**
     * Send Out For Delivery notification with OTP.
     */
    public function sendOutForDeliveryNotification(Shipment $shipment): void
    {
        $content = sprintf(
            'Dear %s, your shipment %s is out for delivery today. Your Delivery Verification PIN is %s.',
            $shipment->receiver_name,
            $shipment->awb_number,
            $shipment->delivery_otp
        );

        if ($shipment->isCod()) {
            $content .= sprintf(' Cash on Delivery amount to pay: $%s.', number_format($shipment->cod_amount, 2));
        }

        $this->logNotification($shipment->company_id, 'sms', $shipment->receiver_phone, 'Out For Delivery', $content);
    }

    /**
     * Send Delivery Success notification.
     */
    public function sendDeliveredNotification(Shipment $shipment): void
    {
        $content = sprintf(
            'Shipment %s has been successfully delivered to %s on %s.',
            $shipment->awb_number,
            $shipment->recipient_name ?? $shipment->receiver_name,
            now()->format('d M Y, h:i A')
        );

        $this->logNotification($shipment->company_id, 'sms', $shipment->sender_phone, 'Shipment Delivered', $content);
    }

    /**
     * Log notification to database and app logger.
     */
    public function logNotification(int $companyId, string $channel, string $recipient, string $subject, string $content): void
    {
        try {
            NotificationLog::create([
                'company_id' => $companyId,
                'channel' => $channel,
                'recipient' => $recipient,
                'subject' => $subject,
                'content' => $content,
                'status' => 'sent',
            ]);
            Log::info("Notification sent [$channel to $recipient]: $content");
        } catch (\Throwable $e) {
            Log::error("Failed to log notification: " . $e->getMessage());
        }
    }
}

