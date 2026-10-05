<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Marketplace\EscrowService;
use App\Services\PesapalService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create payment and redirect to Pesapal.
     */
    public function store(
        Request $request,
        PesapalService $pesapal
    ) {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone' => 'required|string',
        ]);

        $order = Order::with('buyer')->findOrFail($data['order_id']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payer_id' => auth()->id(),
            'amount' => $order->total_amount,
            'method' => 'pesapal',
            'phone' => $data['phone'],
            'status' => 'pending',
            'payment_type' => 'order',
        ]);

        $buyer = $order->buyer;

        $names = explode(' ', $buyer->name, 2);

        $firstName = $names[0];
        $lastName = $names[1] ?? '';

        $response = $pesapal->checkoutPayment(
            $payment,
            "Order #{$order->id}",
            $buyer->email,
            $data['phone'],
            $firstName,
            $lastName
        );

        return response()->json([
            'message' => 'Redirect to Pesapal',
            'payment' => $payment->fresh(),
            'redirect_url' => $response['redirect_url'] ?? null,
        ]);
    }

    /**
     * Pesapal callback.
     */
    public function callback(
        Request $request,
        PesapalService $pesapal
    ) {
        $trackingId = $request->get('OrderTrackingId');

        if (!$trackingId) {
            return response()->json([
                'message' => 'Missing OrderTrackingId',
            ], 400);
        }

        $status = $pesapal->getTransactionStatus($trackingId);

        $payment = Payment::where(
            'pesapal_order_tracking_id',
            $trackingId
        )->firstOrFail();

        // Prevent duplicate processing
        if ($payment->status === 'paid') {

            if ($payment->payment_type === 'subscription') {
                return redirect(
                    env('FRONTEND_URL') . '/subscriptions'
                );
            }

            return redirect(
                env('FRONTEND_URL') . '/buyer/orders'
            );
        }

        if (
            isset($status['payment_status_description']) &&
            strtolower($status['payment_status_description']) === 'completed'
        ) {

            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_ref' => $status['confirmation_code'] ?? null,
                'escrow_amount' => $payment->amount,
            ]);

            // Marketplace Order → escrow hold (90/10 computed on release)
            if ($payment->payment_type === 'order' || !$payment->payment_type) {
                if ($payment->order) {
                    app(EscrowService::class)->hold($payment->order, $payment);
                }
            }

            // Subscription
            if ($payment->payment_type === 'subscription') {

                if ($payment->subscription) {

                    $payment->subscription->update([
                        'status' => 'active',
                        'starts_at' => now(),
                        'expires_at' => now()->addMonths(
                            match ($payment->subscription->price->billing_period ?? 'monthly') {
                                'monthly' => 1,
                                'quarterly' => 3,
                                'biannual' => 6,
                                'annual' => 12,
                                default => 1,
                            }
                        ),
                    ]);
                }
            }

            // Course enrollment
            if ($payment->payment_type === 'course' && $payment->course_enrollment_id) {
                $enrollment = \App\Models\CourseEnrollment::find($payment->course_enrollment_id);
                if ($enrollment) {
                    $wasPending = $enrollment->status === 'pending_payment';
                    $enrollment->update([
                        'status' => 'enrolled',
                        'payment_status' => 'paid',
                        'amount_paid' => $payment->amount,
                        'enrolled_at' => $enrollment->enrolled_at ?: now(),
                    ]);
                    if ($wasPending && $enrollment->course) {
                        $enrollment->course->increment('enrollments_count');
                    }
                }
            }
        }

        if ($payment->payment_type === 'subscription') {
            return redirect(
                env('FRONTEND_URL') . '/subscriptions'
            );
        }

        return redirect(
            env('FRONTEND_URL') . '/buyer/orders'
        );
    }

    /**
     * Pesapal IPN endpoint.
     */
    public function ipn(Request $request)
    {
        logger()->info('Pesapal IPN', $request->all());

        return response()->json([
            'status' => 'OK',
        ]);
    }

    /**
     * Manual / test mark payment as paid → move order to escrow.
     */
    public function markPaid($id, EscrowService $escrow)
    {
        $payment = Payment::with('order')->findOrFail($id);

        if ($payment->payer_id !== auth()->id() && (auth()->user()->role ?? '') !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (in_array($payment->status, ['paid', 'released'], true)) {
            return response()->json(['message' => 'Payment already processed', 'payment' => $payment], 422);
        }

        if ($payment->order) {
            $escrow->hold($payment->order, $payment);
        } else {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'escrow_amount' => $payment->amount,
            ]);
        }

        return response()->json([
            'message' => 'Payment marked paid. Funds held in escrow until buyer confirms delivery.',
            'payment' => $payment->fresh(),
            'order' => $payment->order?->fresh(),
        ]);
    }


    /**
     * Confirm mock / return-page payment (dev + Pesapal return).
     */
    public function confirmReturn(Request $request, \App\Services\PesapalService $pesapal)
    {
        $trackingId = $request->get('OrderTrackingId') ?: $request->get('orderTrackingId');
        $paymentId = $request->get('payment_id');

        $payment = null;
        if ($trackingId) {
            $payment = Payment::where('pesapal_order_tracking_id', $trackingId)->first();
        }
        if (!$payment && $paymentId) {
            $payment = Payment::find($paymentId);
        }
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        if ($payment->payer_id && auth()->check() && (int) $payment->payer_id !== (int) auth()->id()
            && (auth()->user()->role ?? '') !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $status = $trackingId ? $pesapal->getTransactionStatus($trackingId) : ['status' => 'COMPLETED'];
        $ok = in_array(strtoupper((string) ($status['status'] ?? $status['payment_status_description'] ?? '')), [
            'COMPLETED', 'COMPLETE', 'PAID', 'SUCCESS', '0',
        ], true) || str_starts_with((string) $trackingId, 'MOCK-');

        if ($ok && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'transaction_ref' => $status['confirmation_code'] ?? $trackingId,
            ]);

            if ($payment->payment_type === 'course' && $payment->course_enrollment_id) {
                $enrollment = \App\Models\CourseEnrollment::find($payment->course_enrollment_id);
                if ($enrollment) {
                    $wasPending = $enrollment->status === 'pending_payment';
                    $enrollment->update([
                        'status' => 'enrolled',
                        'payment_status' => 'paid',
                        'amount_paid' => $payment->amount,
                        'enrolled_at' => $enrollment->enrolled_at ?: now(),
                    ]);
                    if ($wasPending && $enrollment->course) {
                        try { $enrollment->course->increment('enrollments_count'); } catch (\Throwable $e) {}
                    }
                }
            }

            if (($payment->payment_type === 'order' || !$payment->payment_type) && $payment->order) {
                app(EscrowService::class)->hold($payment->order, $payment);
            }
        }

        return response()->json([
            'message' => $ok ? 'Payment confirmed' : 'Payment not completed',
            'payment' => $payment->fresh(),
            'enrollment' => $payment->course_enrollment_id
                ? \App\Models\CourseEnrollment::find($payment->course_enrollment_id)
                : null,
            'access_granted' => $ok && $payment->payment_type === 'course',
        ]);
    }
}
