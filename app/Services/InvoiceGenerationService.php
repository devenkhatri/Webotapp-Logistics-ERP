<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class InvoiceGenerationService
{
    /**
     * Generate invoice for customer's shipments.
     */
    public function generateForCustomer(Customer $customer, array $shipmentIds = []): ?Invoice
    {
        return DB::transaction(function () use ($customer, $shipmentIds) {
            $query = Shipment::where('customer_id', $customer->id)
                ->where('payment_type', '!=', 'prepaid') // e.g. corporate_credit or to_pay
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('invoice_items')
                        ->whereColumn('invoice_items.shipment_id', 'shipments.id');
                });

            if (! empty($shipmentIds)) {
                $query->whereIn('id', $shipmentIds);
            }

            $shipments = $query->get();
            if ($shipments->isEmpty()) {
                return null;
            }

            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $subtotal = 0.0;
            $totalTax = 0.0;
            $itemsData = [];

            foreach ($shipments as $shipment) {
                $base = (float) $shipment->base_freight + (float) $shipment->fuel_surcharge + (float) $shipment->handling_fee + (float) $shipment->cod_fee;
                $tax = (float) $shipment->gst_tax;
                $tot = (float) $shipment->total_freight;

                $subtotal += $base;
                $totalTax += $tax;

                $itemsData[] = [
                    'shipment_id' => $shipment->id,
                    'description' => "Freight Charges for AWB {$shipment->awb_number} ({$shipment->sender_city} -> {$shipment->receiver_city})",
                    'quantity' => 1,
                    'unit_price' => $base,
                    'tax_percent' => 18.00,
                    'tax_amount' => $tax,
                    'total_amount' => $tot,
                ];
            }

            $invoice = Invoice::create([
                'company_id' => $customer->company_id,
                'customer_id' => $customer->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($totalTax, 2),
                'total_amount' => round($subtotal + $totalTax, 2),
                'paid_amount' => 0.00,
                'payment_status' => 'unpaid',
            ]);

            foreach ($itemsData as $item) {
                $item['invoice_id'] = $invoice->id;
                InvoiceItem::create($item);
            }

            return $invoice;
        });
    }
}

