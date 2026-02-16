<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    public function index(Request $request): Response
    {
        $order = null;
        $error = null;
        $invoiceId = $request->input('invoice_id');

        if ($invoiceId) {
            $order = Order::where('invoice_id', $invoiceId)
                ->with(['products.product:id,name,slug,thumb_image', 'user:id,name'])
                ->first();

            if (!$order) {
                $error = 'Заказ не найден. Проверьте номер заказа и попробуйте снова.';
            }
        }

        return Inertia::render('client/track-order', [
            'order' => $order,
            'error' => $error,
            'invoiceId' => $invoiceId,
        ]);
    }
}
