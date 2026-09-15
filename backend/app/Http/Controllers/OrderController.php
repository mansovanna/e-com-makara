<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Services\BakongService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use KHQR\Helpers\KHQRData;

use function PHPUnit\Framework\isEmpty;

class OrderController extends Controller
{
    //

    public function index()
    {
        $data = Orders::with(['items.food', 'table'])->orderBy('created_at', 'desc')->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id'            => 'required|exists:tables,id',
            'payment_method'      => 'required|in:cash,payway',
            'note'                => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.food_id'     => 'required|exists:foods,id',
            'items.*.quantity'    => 'required|integer|min:1',
        ]);
        // ចំណាំ: 'total' លែងទទួលពី client ទៀត — គណនាពី server វិញ ដើម្បីកុំឲ្យ client ផ្ញើ total ក្លែងក្លាយ

        return DB::transaction(function () use ($validated) {
            $foodIds = collect($validated['items'])->pluck('food_id');
            $foods = Food::whereIn('id', $foodIds)->get()->keyBy('id');

            $order = Orders::create([
                'order_no'       => 'ORD' . now()->format('YmdHis') . rand(10, 99),
                'table_id'       => $validated['table_id'],
                'note'           => $validated['note'] ?? null,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'unpaid',
                'total'          => 0
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $food = $foods[$item['food_id']];
                $unitPrice = $food->is_discount && $food->discount_price !== null
                    ? $food->discount_price
                    : $food->price;
                $subtotal = $unitPrice * $item['quantity'];
                $total += $subtotal;

                $order->items()->create([
                    'food_id'  => $item['food_id'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                    'status'   => 'pending',
                ]);
            }

            $order->update(['total' => $total]);

            return response()->json(['order' => $order->load(['items.food', 'table'])], 201);
        });
    }
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'note' => 'nullable|string',
            'payment_method' => 'required|in:cash,payway,bakong',
            'items' => 'required|array|min:1',
            'subtotal' => 'required|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'currency' => 'nullable|in:KHR,USD',
        ]);

        $currencyLabel = $validated['currency'] ?? 'USD';
        $currency = $currencyLabel === 'USD'
            ? (string) KHQRData::CURRENCY_USD
            : (string) KHQRData::CURRENCY_KHR;

        $bakong = app(BakongService::class);

        $data = $bakong->generateQr(
            amount: $validated['total'],
            currency: $currency,
            billNumber: 'ORD' . now()->format('YmdHis'),
            expiresInSeconds: 300,
        );



        $data['amount'] = $validated['total'];
        $data['currency'] = $currencyLabel;

        return response()->json($data);
    }
    public function checkVerify(Request $request)
    {
        $request->validate(['md5' => 'required|string']);

        $bakong = app(BakongService::class);
        $rawResult = $bakong->checkTransactionByMd5($request->md5);

        return response()->json([
            'paid' => isset($rawResult['responseCode']) && $rawResult['responseCode'] === 0,
            'debug' => $rawResult,
        ]);
    }

    public function update($id, Request $request)
    {
        $data = orders::findOrFail($id);

        $data->update([
            'status' => $request->input('status', 'pending')
        ]);
        return response()->json($data);
    }




    public function summary()
    {
        $orders = orders::with(['items.food', 'table'])->get();

        $today = now()->toDateString();

        $todayOrders = $orders->where('created_at', '>=', $today);

        $todayRevenue = $todayOrders->where('status', '!=', 'cancelled')
            ->sum('total');

        $pendingCount = $orders->whereIn('status', ['pending', 'preparing'])->count();

        $occupiedTables = $orders
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->pluck('table.table_number')
            ->unique()
            ->count();

        // Top foods
        $foodMap = [];

        foreach ($orders as $order) {
            if ($order->status === 'cancelled')
                continue;

            foreach ($order->items as $item) {
                $id = $item->food_id;

                if (!isset($foodMap[$id])) {
                    $foodMap[$id] = [
                        'name' => $item->food->name,
                        'image' => $item->food->image_url,
                        'qty' => 0,
                        'revenue' => 0,
                    ];
                }

                $foodMap[$id]['qty'] += $item->quantity;
                $foodMap[$id]['revenue'] += $item->subtotal;
            }
        }

        $topFoods = collect($foodMap)
            ->sortByDesc('qty')
            ->take(5)
            ->values();

        return response()->json([
            'today_revenue' => $todayRevenue,
            'pending_count' => $pendingCount,
            'occupied_tables' => $occupiedTables,
            'top_foods' => $topFoods,
            'orders' => $orders,
        ]);
    }



    public function myOrders(Request $request)
    {
        $raw = $request->input('my-order-id');
        $idList = json_decode($raw, true) ?? [];

        if (empty($idList)) {
            return response()->json([
                'message' => 'Order ids are required',
            ], 422);
        }

        $orderNos = array_column($idList, 'order_no');

        $orders = orders::whereIn('order_no', $orderNos)
            ->with(['items.food', 'table'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Data retrieved successfully',
            'data' => $orders,
        ]);
    }



    public function updatePaymentStatus(Request $request, $orderId)
    {
        $request->merge([
            'paid_amount' => $request->paid_amount === '' ? null : $request->paid_amount,
            'change_amount' => $request->change_amount === '' ? null : $request->change_amount,
        ]);

        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid',
            'paid_amount' => 'nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric',
        ]);

        $order = Orders::with('items')->findOrFail($orderId);

        // Recalculate ឡើងវិញ ដើម្បីប្រាកដថា total ត្រឹមត្រូវមុនពេល record payment
        // (ករណី item ត្រូវ cancel ក្រោយពេលបង្កើត order តែមុនពេល confirm payment)
        $order->total = collect($order->items)
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');

        $order->payment_status = $validated['status'];
        $order->paid_amount = $validated['paid_amount'] ?? null;
        $order->change_amount = $validated['change_amount'] ?? null;
        $order->save();

        return response()->json([
            'message' => 'Payment status updated successfully',
            'data' => $order,
        ]);
    }

    /**
     * PUT /orders/items/{itemId}
     */



    public function updateItemStatus(Request $request, $itemId)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,served,cancelled',
        ]);

        $item = OrderItem::findOrFail($itemId);
        $item->status = $validated['status'];
        $item->save();

        $this->recalculateOrderTotal($item->order_id);

        return response()->json([
            'message' => 'Item status updated successfully',
            'data' => $item,
        ]);
    }

    /**
     * គណនា orders.total ឡើងវិញ = សរុប subtotal នៃ item ដែលមិនត្រូវ cancel
     */
    private function recalculateOrderTotal($orderId): void
    {
        $order = Orders::with('items')->findOrFail($orderId);

        $total = collect($order->items)
            ->where('status', '!=', 'cancelled')
            ->sum('subtotal');

        $order->total = $total;
        $order->save();
    }
}
