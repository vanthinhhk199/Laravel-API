<?php

namespace App\Http\Controllers\Api;

use App\Models\Orders;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $orders = Orders::query();

            if ($search) {
                $orders->where('name', 'LIKE', '%' . $search . '%');
            }

            $orders = $orders->with('orderItems.products')->paginate(10);

            return response()->json([
                'success' => true,
                'orders' => $orders,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function show($user_id)
    {
        $orders = Orders::with('orderItems.products')->where('user_id', $user_id)->orderByDesc('id')->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng của người dùng'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }
    public function statusOrderAdmin (Request $request)
    {
        $order_id = $request->id;
        $orders = Orders::where('id', $order_id)->firstOrFail();
        $orders->status = $request->input('status');
        $orders->update();
        return response()->json([
            'message'=>'Change Status success'
        ]);
    }
    public function myOrder(Request $request )
    {
        $id = auth()->user()->id;
        $orders = Orders::with('orderItems.products')->where('user_id',$id)->get();
        return response()->json([
            'myOrder'=>$orders
        ]);
    }

    public function deleteOrder($id)
    {
        $order = Orders::findOrFail($id);
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
?>
