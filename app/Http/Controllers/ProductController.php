<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    protected $user;


    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = $this->user->products()->orderBy('created_at','desc')->get();
        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title'     => 'required|string',
                'description'=> 'nullable|string',
                'price'      => 'required|numeric|min:0',
                'image' => 'nullable|mimes:jpeg,jpg,png|max:10000',
            ]
        );
        
        $product            = new Product();
        $product->title     = $request->title;
        $product->description = $request->description ?? '';
        $product->price = $request->price;
        $product->image = $request->image ? imageUploadFromBase64($request->image) : '';

        if ($this->user->products()->save($product)) {
            return ProductResource::make($product);
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the product could not be saved.',
                ]
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return ProductResource::make($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title'     => 'required|string',
                'description'=> 'nullable|string',
                'price'      => 'required|numeric|min:0',
                'image' => 'nullable|mimes:jpeg,jpg,png|max:10000',
            ]
        );

        $image = null;
        if ($request->image == null) {
            imageDelete($product->image);
            $image = null;
        } else if ($request->image && $request->image == $product->image) {
            $image = $product->image;
        } else if ($request->image && $request->image != $product->image) {
            imageDelete($product->image);
            $image = imageUploadFromBase64($request->image);
        }

        $product->title     = $request->title;
        $product->description = $request->description ?? '';
        $product->price = $request->price;
        $product->image = $image;

        if ($this->user->products()->save($product)) {
            return ProductResource::make($product);
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the product could not be updated.',
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        if ($product->delete()) {
            return ProductResource::make($product);
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the product could not be deleted.',
                ]
            );
        }
    }

    protected function guard()
    {
        return Auth::guard();

    }
}
