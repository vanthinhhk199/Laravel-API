<?php

namespace App\Http\Controllers\Api;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    public function placeorder( Request $request)
    {
        $order = new Orders();
        $order->user_id = auth()->user()->id;
        $order->name = $request->input('name');
        $order->email = $request->input('email');
        $order->phone = $request->input('phone');
        $order->address = $request->input('address');
        $order->pincode = $request->input('pincode');
        $order->message = $request->input('message');

        //Tính tổng giá
        $total=0;
        $cartItems_total = Cart::where('user_id', $order->user_id)->get();
        foreach($cartItems_total as $prod){
            $total += $prod->product->price * $prod->prod_qty;
        }
        $order->total_price = $total;
        $order->save();

        $cartItems = Cart::where('user_id', $order->user_id)->get();
        foreach($cartItems as $item){
           OrderItem::create([
                'order_id' => $order->id,
                'prod_id'=>$item->prod_id,
                'qty' =>$item->prod_qty,
                'price'=>$item->product->price,
            ]);
            $prod = Products::where('id',$item->prod_id)->first();
            $prod->qty = $prod->qty -  $item->prod_qty;
            $prod ->update();
        }

        $cartItems = Cart::where('user_id', $order->user_id)->get();
        Cart::destroy($cartItems);

       return response()->json([
        'message'=>'Payment Success'
       ]);
    }

    public function placeorder1( Request $request)
    {
        $order = new Orders();
        $order->user_id = $request->input('user_id');
        $order->name = $request->input('name');
        $order->phone = $request->input('phone');
        $order->address = $request->input('address');
        $order->total_price = $request->input('total');
        $order->status = $request->input('status');
        $order->paymentmode = $request->input('paymentmode');


        $order->idPayment = $request->input('idPayment');
        $order->paymentTime = $request->input('paymentTime');
        $order->save();

        $orderItems = $request->input('order_items');

        foreach ($orderItems as $item) {
            $order_items = new OrderItem();
            $order_items->order_id = $order->id;
            $order_items->prod_id = $item['id'];
            $order_items->qty = $item['quantity'];
            $order_items->save();

            $product = Products::find($item['id']);
            if ($product && $product->qty >= $item['quantity']) {
                $product->qty -= $item['quantity'];
                $product->save();
            } else {
                // Số lượng sản phẩm không đủ
                return response()->json(['message' => 'Số lượng sản phẩm không đủ'], 400);
            }
        }

        return response()->json(['message' => 'Đơn hàng đã được đặt thành công']);
    }
    public function getAddressByUserId($user_id)
    {
        try {
            $address = Address::where('user_id', $user_id)->get();
            if ($address) {
                return response()->json($address);
            } else {
                return response()->json(['error' => 'Address not found'], 404);
            }
        }catch (\Throwable $e) {
            return response()->json(['success'=>false,'messages'=>$e->getMessage()]);
        }
    }

    public function updateAddress(Request $request)
    {
        try {
            $address = Address::find($request->input('id'));

            if (!$address) {
                return response()->json(['message' => 'Address not found'], 404);
            }

            $address->name = $request->input('name');
            $address->phone = $request->input('phone');
            $address->address = $request->input('address');
            $address->save();

            return response()->json(['message' => 'Address updated successfully']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }



    //add address
    public function store(Request $request)
    {
         try {
             $data = $request->validate([
                 'user_id' => 'required',
                 'name' => 'required',
                 'phone' => 'required',
                 'address' => 'required',

             ]);

             $address = Address::create($data);

             return response()->json([
                 'message' => 'Address  successfully.',
                 'address' => $address,
             ]);
         }catch (\Throwable $e) {
            return response()->json(['success'=>false,'messages'=>$e->getMessage()]);
        }
    }

    public function deleteAddress($id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }

    public function upStatus( Request $request)
    {
        $order = new Orders();
        $order->user_id = $request->input('user_id');

        $order->save();

        return response()->json(['message' => 'Đơn hàng đã được đặt thành công']);
    }

    public function execPostRequest($url, $data){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        //execute post
        $result = curl_exec($ch);
        //close connection
        curl_close($ch);
        return $result;
    }

    public function momo_payment(Request $request,$id){
        $total=0;
        $cartItems_total = Cart::where('user_id', $id)->get();
        foreach($cartItems_total as $prod){
            $total += $prod->product->price * $prod->prod_qty;
        }
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

        $partnerCode = 'MOMOBKUN20180529';
        $accessKey = 'klm05TvNBzhg7h7j';
        $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $orderInfo = "Thanh toán qua MoMo";
        $amount = $total;
        $orderId = time() . "";
        $redirectUrl = "http://localhost:8080/";
        $ipnUrl = "http://localhost:8080/";
        $extraData = "";

        // TK để test
        // 9704 0000 0000 0018
        // NGUYEN VAN A
        // 03/07

        $requestId = time() . "";
        $requestType = "payWithATM";
        //$extraData = ($_POST["extraData"] ? $_POST["extraData"] : "");
        //before sign HMAC SHA256 signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        $data = array('partnerCode' => $partnerCode,
            'partnerName' => "Test",
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature);
        $result =$this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);  // decode json
        // $cartItems = Cart::where('user_id', $id)->get();
        // Cart::destroy($cartItems);
        //Just a example, please check more in there
            return response()->json($jsonResult['payUrl']);

    }
}
?>
