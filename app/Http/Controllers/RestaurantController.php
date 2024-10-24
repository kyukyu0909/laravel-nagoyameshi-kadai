<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Category;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $category_id = $request->category_id;
        $price = $request->price;

        $sorts = [
            '掲載日が新しい順' => 'created_at desc',
            '価格が安い順' => 'lowest_price asc'
        ];

        $sort_query = [];
    if ($request->has('select_sort')) {
        $slices = explode(' ', $request->input('select_sort'));
        $sort_query[$slices[0]] = $slices[1];
    } else {
        $sort_query = ['created_at' => 'desc'];
    }

    $categories = Category::all();

    $restaurants = Restaurant::query();

    if ($keyword) {
        $restaurants->where('name', 'like', "%{$keyword}%")
            ->orWhere('address', 'like', "%{$keyword}%")
            ->orWhereHas('categories', function ($query) use ($keyword) {
                $query->where('categories.name', 'like', "%{$keyword}%");
            });
    }

    if ($category_id) {
        $restaurants->whereHas('categories', function ($query) use ($category_id) {
            $query->where('categories.id', '=', $category_id);
        });
    }

    if ($price) {
        $priceRange = explode('-', $price);
        if (count($priceRange) == 2) {
            $restaurants->whereBetween('lowest_price', [$priceRange[0], $priceRange[1]]);
        } else {
            $restaurants->where('lowest_price', '<=', $price);
        }
    }

    $restaurants = $restaurants->sortable($sort_query)->paginate(15);

    $total = $restaurants->total();

    return view('restaurants.index', compact('keyword', 'category_id', 'price', 'sorts', 'sorted', 'restaurants', 'categories', 'total'));
    }   
}
