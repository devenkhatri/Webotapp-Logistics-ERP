<?php

namespace App\Services;

use App\Models\DeliveryAttempt;
use App\Models\DeliveryProof;
use App\Models\DeliveryRun;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryProofService
{
    public function __construct(
        protected ShipmentTrackingService $trackingService,
        protected CodCollectionService $codService,
        protected RtoManagementService $rtoService
    ) {}

    /**
     * Confirm delivery with proof (POD) and optional OTP validation.
     */
    public function deliver(
        Shipment $shipment,
        string $recipientName,
        string $recipientRelation = 'Self',
        ?string $enteredOtp = null,
        ?string $signatureData = null,
        ?string $photoPath = null,
        ?int $agentId = null,
        string $codPaymentMethod = 'cash'
    ): DeliveryProof {
        return DB::transaction(function () use (
            $shipment,
            $recipientName,
            $recipientRelation,
            $enteredOtp,
            $signatureData,
            $photoPath,
            $agentId,
            $codPaymentMethod
        ) {
            // Verify OTP if required and entered
            if ($enteredOtp) {
                if ($shipment->delivery_otp && trim($enteredOtp) !== trim($shipment->delivery_otp)) {
                    throw new InvalidArgumentException('Invalid delivery OTP entered.');
                }
                $shipment->otp_verified_at = now();
            }

            // Update shipment
            $shipment->recipient_name = $recipientName;
            $shipment->recipient_relation = $recipientRelation;
            $shipment->pod_signature = $signatureData;
            $shipment->pod_photo = $photoPath;
            $shipment->status = 'delivered';
            $shipment->delivered_at = now();
            $shipment->save();

            // Create Delivery Proof
            $proof = DeliveryProof::create([
                'shipment_id' => $shipment->id,
                'delivery_agent_id' => $agentId ?? $shipment->delivery_agent_id,
                'recipient_name' => $recipientName,
                'recipient_relation' => $recipientRelation,
                'signature_data' => $signatureData,
                'pod_photo' => $photoPath,
                'otp_code' => $enteredOtp,
                'delivered_at' => now(),
            ]);

            // If COD shipment, collect payment
            if ($shipment->isCod() && $shipment->cod_amount > 0) {
                $this->codService->recordCollection(
                    shipment: $shipment,
                    amount: (float) $shipment->cod_amount,
                    paymentMethod: $codPaymentMethod,
                    agentId: $agentId
                );
            }

            // Update active DRS run if any
            $run = DeliveryRun::whereHas('shipments', function ($q) use ($shipment) {
                $q->where('shipments.id', $shipment->id);
            })->whereIn('status', ['created', 'out_for_delivery'])->latest()->first();

            if ($run) {
                $run->shipments()->updateExistingPivot($shipment->id, [
                    'status' => 'delivered',
                    'completed_at' => now(),
                ]);
                $run->delivered_count += 1;
                if ($shipment->isCod()) {
                    $run->total_cod_collected += (float) $shipment->cod_amount;
                }
                if ($run->delivered_count + $run->failed_count >= $run->total_shipments) {
                    $run->status = 'completed';
                }
                $run->save();
            }

            // Timeline event
            $this->trackingService->updateStatus(
                shipment: $shipment,
                newStatus: 'delivered',
                statusTitle: 'Delivered',
                location: $shipment->receiver_city,
                description: "Package successfully delivered to {$recipientName} ({$recipientRelation}).",
                userId: $agentId ?? $shipment->delivery_agent_id
            );

            return $proof;
        });
    }

    /**
     * Record a failed delivery attempt.
     */
    public function recordFailure(
        Shipment $shipment,
        string $reasonCode,
        ?string $remarks = null,
        ?int $agentId = null
    ): DeliveryAttempt {
        return DB::transaction(function () use ($shipment, $reasonCode, $remarks, $agentId) {
            $shipment->attempt_count += 1;
            $shipment->failed_reason = $reasonCode;
            $shipment->status = 'failed_attempt';
            $shipment->save();

            $attempt = DeliveryAttempt::create([
                'shipment_id' => $shipment->id,
                'delivery_agent_id' => $agentId ?? $shipment->delivery_agent_id,
                'attempt_no' => $shipment->attempt_count,
                'reason_code' => $reasonCode,
                'remarks' => $remarks,
                'attempted_at' => now(),
            ]);

            // Update DRS run
            $run = DeliveryRun::whereHas('shipments', function ($q) use ($shipment) {
                $q->where('shipments.id', $shipment->id);
            })->whereIn('status', ['created', 'out_for_delivery'])->latest()->first();

            if ($run) {
                $run->shipments()->updateExistingPivot($shipment->id, [
                    'status' => 'failed',
                    'completed_at' => now(),
                    'failure_reason' => $reasonCode,
                ]);
                $run->failed_count += 1;
                if ($run->delivered_count + $run->failed_count >= $run->total_shipments) {
                    $run->status = 'completed';
                }
                $run->save();
            }

            $reasonTitles = [
                'customer_unavailable' => 'Customer Unavailable / Door Closed',
                'wrong_address' => 'Incorrect Address / Land Mark not found',
                'refused_to_accept' => 'Customer Refused Delivery',
                'cash_not_ready' => 'COD Cash Not Ready',
                'future_delivery_requested' => 'Customer Requested Future Date Delivery',
            ];
            $title = $reasonTitles[$reasonCode] ?? ucwords(str_replace('_', ' ', $reasonCode));

            $this->trackingService->updateStatus(
                shipment: $shipment,
                newStatus: 'failed_attempt',
                statusTitle: 'Delivery Attempt Failed',
                location: $shipment->receiver_city,
                description: "Attempt #{$shipment->attempt_count} failed: {$title}. Remarks: {$remarks}",
                userId: $agentId ?? $shipment->delivery_agent_id
            );

            // Auto-trigger RTO if 3 failed attempts or explicitly refused
            if ($shipment->attempt_count >= 3 || $reasonCode === 'refused_to_accept') {
                $this->rtoService->initiateRto(
                    shipment: $shipment,
                    reason: $reasonCode === 'refused_to_accept' ? 'Customer Refused Acceptance' : 'Max Delivery Attempts (3) Exhausted',
                    userId: $agentId
                );
            }

            return $attempt;
        });
    }
}
