<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // 商品一覧を表示
    public function index()
    {
        $products = Product::latest()->paginate(5);
        return view('products.index', compact('products'));
    }

    // 商品登録フォームを表示
    public function create()
    {
        return view('products.create');
    }

    // 商品を保存する
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required',
            'stock' => 'required|integer|min:0',
            'category' => 'required|max:50',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', '商品を登録しました');
    }

    // 商品詳細を表示
    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    // 編集フォームを表示
    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    // 商品の更新を保存する
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'required',
            'stock' => 'required|integer|min:0',
            'category' => 'required|max:50',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', '商品を更新しました');
    }

    // 商品を削除する
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', '商品を削除しました');
    }
}
