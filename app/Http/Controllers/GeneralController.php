<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    public function getCategories()
    {
        $categories = DB::table('categories')->get();
        return view('categories.index', compact('categories'));
    }

    public function addCategory(Request $request)
    {
        $request->validate([
            'category_name' => 'required|string|max:255'
        ]);

        DB::table('categories')->insert([
            'category_name' => $request->category_name,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Category added successfully!');
    }
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::table('categories')
            ->where('id', $id)
            ->update([
                'category_name' => $request->category_name,
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    // GET: Show all ad types
    public function getAdTypes()
    {
        $adtypes = DB::table('advertisement_types')->get();
        $categories = DB::table('categories')->where('is_active', 1)->get();
        return view('adtypes.index', compact('adtypes', 'categories'));
    }

    // POST: Add new ad type
    public function addAdType(Request $request)
    {
        $request->validate([
            'advertisement_type' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric',
        ]);

        $adTypeId = DB::table('advertisement_types')->insertGetId([
            'advertisement_type' => $request->advertisement_type,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Also insert into the category_has_advertisement_types pivot table
        DB::table('category_has_advertisement_types')->insert([
            'category_id' => $request->category_id,
            'advertisement_type_id' => $adTypeId,
        ]);

        return redirect()->back()->with('success', 'Advertisement type added successfully!');
    }

    // POST: Update ad type
    public function updateAdType(Request $request, $id)
    {
        $request->validate([
            'advertisement_type' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric',
            'is_active' => 'required|boolean',
        ]);

        DB::table('advertisement_types')->where('id', $id)->update([
            'advertisement_type' => $request->advertisement_type,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        // Update pivot table (optional logic: delete old and insert new)
        DB::table('category_has_advertisement_types')->where('advertisement_type_id', $id)->delete();
        DB::table('category_has_advertisement_types')->insert([
            'category_id' => $request->category_id,
            'advertisement_type_id' => $id,
        ]);

        return redirect()->back()->with('success', 'Advertisement type updated successfully!');
    }

    // GET: Show all ad sizes
    public function getAdSizes()
    {
        $adSizes = DB::table('advertisement_sizes')->get();
        $adTypes = DB::table('advertisement_types')->where('is_active', 1)->get();
        return view('adsizes.index', compact('adSizes', 'adTypes'));
    }

    // POST: Add new ad size
    public function addAdSize(Request $request)
    {
        $request->validate([
            'advertisement_size' => 'required|string|max:255',
            'advertisement_type_id' => 'required|integer|exists:advertisement_types,id',
            'price' => 'required|numeric',
            'img_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('img_url')) {
            $imagePath = $request->file('img_url')->store('adsizes', 'public');
        }

        $adSizeId = DB::table('advertisement_sizes')->insertGetId([
            'advertisement_size' => $request->advertisement_size,
            'advertisement_type_id' => $request->advertisement_type_id,
            'price' => $request->price,
            'img_url' => $imagePath,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('advertisement_type_has_advertisement_sizes')->insert([
            'advertisement_type_id' => $request->advertisement_type_id,
            'advertisement_size_id' => $adSizeId,
        ]);

        return redirect()->back()->with('success', 'Advertisement size added successfully!');
    }

    // POST: Update ad size
    public function updateAdSize(Request $request, $id)
    {
        $request->validate([
            'advertisement_size' => 'required|string|max:255',
            'advertisement_type_id' => 'required|integer|exists:advertisement_types,id',
            'price' => 'required|numeric',
            'is_active' => 'required|boolean',
            'img_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'advertisement_size' => $request->advertisement_size,
            'advertisement_type_id' => $request->advertisement_type_id,
            'price' => $request->price,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ];

        if ($request->hasFile('img_url')) {
            $imagePath = $request->file('img_url')->store('adsizes', 'public');
            $data['img_url'] = $imagePath;
        }

        DB::table('advertisement_sizes')->where('id', $id)->update($data);

        // Update pivot table
        DB::table('advertisement_type_has_advertisement_sizes')->where('advertisement_size_id', $id)->delete();
        DB::table('advertisement_type_has_advertisement_sizes')->insert([
            'advertisement_type_id' => $request->advertisement_type_id,
            'advertisement_size_id' => $id,
        ]);

        return redirect()->back()->with('success', 'Advertisement size updated successfully!');
    }
}
