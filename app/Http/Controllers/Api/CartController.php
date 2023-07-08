<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function getCartWithProducts($userId)
    {
        try {
            $cart = Cart::where('user_id', $userId)->get();


            $cartItems = [];
            foreach ($cart as $item) {
                $product = $item->product;
                $cartItems[] = [
                    'id' => $item->prod_id,
                    'product' => $product,
                    'quantity' => $item->prod_qty,

                ];
            }

            return response()->json([
                'user_id' => $userId,
                'cartItems' => $cartItems
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function addToCart(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $prod_id = $request->input('idProd');
            $prod_qty = $request->input('quantity');

            $prod_check = Cart::where('prod_id', $prod_id)->first();
            if ($prod_check) {
                $prod_check->prod_qty += $prod_qty;
                $prod_check->save();
            } else {
                Cart::create([
                    'user_id' => $user_id,
                    'prod_id' => $prod_id,
                    'prod_qty' => $prod_qty
                ]);
            }

            // Xóa các idProd không có trong request
            $ids_in_request = $request->input('idProd');
            Cart::whereNotIn('prod_id', $ids_in_request)->delete();

            return response()->json(['message' => 'Product added to cart successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while adding the product to the cart'], 500);
        }
    }



    public function viewCart($id){
        try {
            if (isset(auth()->user()->id)) {
                $cartItems =Cart::where('user_id',$id)->with('product')->get();
                if($cartItems){
                    return response()->json([
                        'success'=>true,
                        'cartItem'=>$cartItems
                    ]);
                }
            } else {
                return response()->json(['status' => "Đăng nhập để tiếp tục"]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
            ]);
        }
    }

    public function deleteProduct(Request $request)
{
    try {
        $user_id = $request->input('user_id');
        $prod_id = $request->input('prod_id');
        $cartItem = Cart::where('prod_id', $prod_id)->where('user_id', $user_id)->first();
        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['message' => 'Product deleted from cart successfully'], 200);
        } else {
            return response()->json(['message' => 'Product not found in cart'], 404);
        }
    } catch (\Exception $e) {
        return response()->json(['error' => 'An error occurred while deleting the product from the cart'], 500);
    }
}

    public function updateProduct(Request $request){

        if(isset(auth()->user()->id)){

            $prod_id=$request->input('prod_id');
            $prod_qty= $request->input('prod_qty');
            $user_id = auth()->user()->id;

            if(Cart::where('prod_id',$prod_id)->where('user_id', $user_id)->exists()){
                $cart = Cart::where('prod_id',$prod_id)->where('user_id',  $user_id)->first();
                $cart ->prod_qty = $prod_qty;
                $cart->update();
                return response()->json(['message' => "Thêm số lượng thành công"]);
            }
        }
        else{
            return response()->json(['message' => "Đăng nhập để tiếp tục"]);
        }
    }
    public function cartCount(Request $request)
    {
        $user_id= auth()->user()->id;
        $cartcount = Cart::where('user_id', $user_id)->count();
        return response()->json(['count'=> $cartcount]);
    }
    public function totalCart(Request $request)
    {
        $user_id = auth()->user()->id;
        $total = 0;
        $cartItems_total = Cart::where('user_id', $user_id)->get();
        foreach($cartItems_total as $prod){
            $total += $prod->product->price * $prod->prod_qty;
        }
        return response()->json($total);
    }
}

?>
