<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Models\Products;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cate = $request->query('cate');
            $priceMin = $request->query('min');
            $priceMax = $request->query('max');
            $search = $request->query('search');

            $products = Products::query();

            if ($cate) {
                $products->where('cate_id', $cate);
            }

            if ($priceMin) {
                $products->where('price', '>=', $priceMin);
            }

            if ($priceMax) {
                $products->where('price', '<=', $priceMax);
            }

            if ($search) {
                $products->where('name', 'LIKE', '%' . $search . '%');
            }


            $products = $products->paginate(16);

            return response()->json($products);
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage(),
            ]);
        }
    }

    public function show()
    {
        try {
            $products =Products::orderBy('id', 'desc')->paginate(10);
            if($products){
                return response()->json([
                    'success'=>true,
                    'product'=>$products,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'error'=>$e->getMessage(),
            ]);
        }
    }

    public function create(Request $request)
    {
       try {
        $validator = Validator::make($request->all(),[
            'cate_id'=>' required',
            'name'=>' required|string | max:191',
            'slug'=>' required |string | max:191',
            'description'=>' required |string | max:2000',
            'price'=>' required |string | max:191',
            'image'=>'required',
            'qty'=>' required |string | max:191',

        ]);
        if($validator->fails()){
            return response()->json([
                'success'=>false,
                'message'=>$validator->errors()->all(),
            ]);
        }else{
            $product = new Products();
            if($request->hasFile('image')){
                $file =$request->file('image');
                $ext =$file->getClientOriginalExtension();
                $filename = rand().'.'.$ext;
                $file->move('assets/uploads/product/',$filename );
                $product->image = $filename;
            }
            $product->cate_id = $request->cate_id;
            $product->name = $request->name;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->qty = $request->qty;
            $product->save();

            if($product){
                return response()->json([
                    'success'=>true,
                    'message'=>"Product Add Successfufly",
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => "Some Problem",
                ]);
            }
        }

       } catch (Exception $e) {
             return response()->json([
                'success'=>false,
                'error'=>$e->getMessage(),
            ]);
        }
    }

    public function edit($id)
    {
        try {
            $products =Products::findOrFail($id);
            if($products){
                return response()->json([
                    'success'=>true,
                    'product'=>$products,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'error'=>$e->getMessage(),
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'cate_id' => 'required',
            'name' => 'required',
            'slug' => 'required',
            'description' => 'required',
            'price' => 'required|numeric|gt:0',
            'qty' => 'required|integer|gt:0',
        ]);

        $product = Products::findOrFail($id);

        $product->update($validatedData);

        if($request->hasFile('image')){
            $file =$request->file('image');
            $ext =$file->getClientOriginalExtension();
            $filename = rand().'.'.$ext;
            $file->move('assets/uploads/product/',$filename );
            $product->image = $filename;
            $product->save();
        }

        return response()->json(['message' => 'Product updated successfully']);
    }

    public function destroy($id)
    {
        try {
            $result =Products::findOrFail($id)->delete();
            if($result){
                return response()->json([
                    'success'=>true,
                    'message'=>"Product Delete Successfufly",
                ]);
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>"Some Problem",
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'error'=>$e->getMessage(),
            ]);
        }
    }
    public function search($search)
    {

        try {
            $products = Product::where('name','LIKE','%'.$search.'%')->orderBy('id','desc')->with('category')->get();
            if($products){
                return response()->json([
                    'success'=>true,
                    'products'=>$products,
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success'=>false,
                'error'=>$e->getMessage(),
            ]);
        }
    }
}
?>
