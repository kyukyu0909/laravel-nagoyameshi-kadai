<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;


class RestaurantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request)
    {
        $keyword = $request->keyword;

        if ($keyword !== null) {
            $restaurants = Restaurant::where('name', 'like', "%{$keyword}%")->paginate(15);
            $total = $restaurants->total();
        } else {
            $restaurants = Restaurant::paginate(15);
            $total = Restaurant::count();
        }

        return view('admin.restaurants.index', compact('restaurants', 'total', 'keyword'));
    }

    public function show($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.restaurants.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' =>'required|string|max:255',
            'description' =>'required',
            'lowest_price' =>'required|integer',
            'highest_price' =>'required|integer',
            'postal_code' =>'required|string',
            'address' =>'required|string',
            'opening_time' =>'required',
            'closing_time' =>'required|date_format:H:i|after:opening_time',
            'seating_capacity' =>'required|between:0,200|integer',
            'category_ids' => 'required|array|max:3',  // カテゴリのバリデーション
            'image'=>'image|max:2048',
        ]);

        $restaurant = new Restaurant();
        $restaurant->name = $request->input('name');
        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('public/restaurants');
            $restaurant->image_name = basename($image);
        } else {
            $restaurant->image_name = '';
        }
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');
        $restaurant->save();

        $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を登録しました。');
        }

    public function edit($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $categories = Category::all();
        $category_ids = $restaurant->categories->pluck('id')->toArray();

        return view('admin.restaurants.edit', compact('restaurant', 'categories', 'category_ids'));
    }

    public function update(Request $request, string $id)
    {
        $restaurant = Restaurant::findOrFail($id);

        $request->validate([
            'category_ids' => 'required|array|max:3',
        ]);

        $restaurant->name = $request->input('name');
        $restaurant->description = $request->input('description');
        $restaurant->lowest_price = $request->input('lowest_price');
        $restaurant->highest_price = $request->input('highest_price');
        $restaurant->postal_code = $request->input('postal_code');
        $restaurant->address = $request->input('address');
        $restaurant->opening_time = $request->input('opening_time');
        $restaurant->closing_time = $request->input('closing_time');
        $restaurant->seating_capacity = $request->input('seating_capacity');

        if ($request->hasFile('image')) {
            $restaurant->image_name = base64_encode(file_get_contents($request->file('image')->getRealPath()));
            $file = $request->file('image')->move('storage/restaurants');
        }

        $restaurant->save();

        // カテゴリの更新
        $category_ids = array_filter($request->input('category_ids'));
        $restaurant->categories()->sync($category_ids);

        return redirect()->route('admin.restaurants.edit', ['restaurant' => $id])->with('flash_message', '店舗を編集しました。');
    }

    public function destroy($id)
    {
        $restaurant = Restaurant::findOrFail($id);
        $restaurant->delete();

        return redirect()->route('admin.restaurants.index')->with('flash_message', '店舗を削除しました。');
    }
}
