<?php

namespace App\Http\Controllers;

use App\Models\orders;
use App\Services\BakongService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use KHQR\Helpers\KHQRData;

class OrderController extends Controller
{
    //

    public function index()
    {
        $data = orders::with(['items.food', 'table'])->orderBy('created_at', 'desc')->get();

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_id' => 'required|exists:tables,id',
            'payment_method' => 'required|in:cash,payway',
            'items' => 'required|array|min:1',
            'items.*.food_id' => 'required|exists:foods,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',

        ]);

        return DB::transaction(function () use ($validated) {
            $order = orders::create([
                'order_no' => 'ORD' . now()->format('YmdHis') . rand(10, 99),
                'table_id' => $validated['table_id'],
                'note' => $validated['note'] ?? null,
                'payment_method' => $validated['payment_method'],
                'status' => 'pending',
                'discount' => $validated['discount'] ?? 0,
                'subtotal' => $validated['subtotal'] ?? 0,
                'total' => $validated['total'] ?? 0,
                'coupon_id' => $validated['coupon_id'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }

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

        // ✅ បន្ថែម deeplink ដោយប្រើ qr string ដែលទើប generate
        // $data['deeplink'] = $bakong->generateDeepLink($data['qr']);

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

}
