<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\TestAuthJWTController;


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return 'HIHI';
// });

Route::get('/check-database', function() {
    try {
        DB::connection()->getPdo();
        return "Connected successfully to the database!";
    } catch (\Exception $e) {
        return "Could not connect to the database. Error: " . $e->getMessage();
    }
});

Route::get('forget-password/{email}', [AuthController::class, 'forgetPassword']);

Route::group(['middleware'=>'api'],function ($routes)
{
    Route::post('/user', [AuthController::class, 'getAllUser']);
    Route::get('/userinfo/{id}', [AuthController::class, 'getUserInfo']);
    Route::put('/user/{user_id}', [AuthController::class , 'updateUserInfo']);
    Route::post('/user/{user_id}/avatar', [AuthController::class , 'uploadAvatar']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/verifyemail/{email}', [AuthController::class, 'sendVerifyMail']);
    Route::get('refresh-token', [AuthController::class, 'refreshToken']);
    Route::put('/users/role/{id}', [AuthController::class, 'updateRole']);
    Route::delete('/delete-user/{id}', [AuthController::class, 'deleteUser']);

});

// Category
    Route::get('/categorys',[CategoryController::class,'index']);
    Route::get('/category/{id}',[CategoryController::class,'edit']);
    Route::post('/category/{id}',[CategoryController::class,'update']);
    Route::delete('/category/{id}',[CategoryController::class,'destroy']);
    Route::post('/categorys/{search}',[CategoryController::class,'search']);
    Route::post('/admin/categorys',[CategoryController::class,'getcateadmin']);


Route::middleware('jwt.auth')->group(function () {
        // Các tuyến đường yêu cầu xác thực
        Route::post('/category',[CategoryController::class,'create']);
    });

// Product
    Route::get('/products',[ProductController::class,'index']);
    Route::post('/product',[ProductController::class,'create']);
    Route::get('/product/get-category/{id}',[ProductController::class,'getProductbyCategory']);
    Route::get('/product/{id}',[ProductController::class,'edit']);
    Route::post('/product/{id}',[ProductController::class,'update']);
    Route::delete('/product/{id}',[ProductController::class,'destroy']);
    Route::get('/product/search-product/{search}',[ProductController::class,'search']);

    Route::post('/admin/products',[ProductController::class,'searchAdminProd']);


//Cart
    Route::get('/carts/products/{userId}',[CartController::class,'getCartWithProducts']);
    Route::get('/cart/get-user/{id}',[CartController::class,'viewCart']);
    Route::post('/cart/add-cart',[CartController::class,'addToCart']);
    Route::post('/cart/deleteProduct',[CartController::class,'deleteProduct']);
    Route::put('/cart/update-cart',[CartController::class,'updateProduct']);
    Route::get('/cart/cart-preview/{id}',[CartController::class,'cartCount']);
    Route::get('/cart/total-cart/{id}',[CartController::class,'totalCart']);


    // ORDER
    Route::post('/admin/order',[OrderController::class,'index']);
    Route::post('/admin/order-status',[OrderController::class,'statusOrderAdmin']);
    Route::delete('/orders/{id}', [OrderController::class, 'deleteOrder']);
    Route::get('/cart/my-order',[OrderController::class,'myOrder']);
    Route::get('/order/{id}',[OrderController::class,'show']);
    Route::post('/cart/cart-placeorder',[CheckoutController::class,'placeorder']);
    Route::post('/placeorder1',[CheckoutController::class,'placeorder1']);
    Route::post('/address', [CheckoutController::class, 'store']);
    Route::get('/get-address/{user_id}', [CheckoutController::class, 'getAddressByUserId']);
    Route::put('/checkout/update-address', [CheckoutController::class, 'updateAddress']);
    Route::delete('/dele-addresses/{id}', [CheckoutController::class, 'deleteAddress']);
    Route::post('momo-payment/{id}',[CheckoutController::class,'momo_payment']);

?>
