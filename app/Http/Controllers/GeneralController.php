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

    // GET: Show all advertisement criterias
    public function getAdCriterias()
    {
        $criterias = DB::table('advertisement_criterias')->get();
        $categories = DB::table('categories')->where('is_active', 1)->get();
        return view('adcriterias.index', compact('criterias', 'categories'));
    }

    // POST: Add new criteria
    public function addAdCriteria(Request $request)
    {
        $request->validate([
            'advertisement_criteria_name' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        DB::table('advertisement_criterias')->insert([
            'advertisement_criteria_name' => $request->advertisement_criteria_name,
            'field_type' => $request->field_type,
            'category_id' => $request->category_id,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Advertisement criteria added successfully!');
    }

    // POST: Update criteria
    public function updateAdCriteria(Request $request, $id)
    {
        $request->validate([
            'advertisement_criteria_name' => 'required|string|max:255',
            'field_type' => 'required|string|max:50',
            'category_id' => 'required|integer|exists:categories,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('advertisement_criterias')->where('id', $id)->update([
            'advertisement_criteria_name' => $request->advertisement_criteria_name,
            'field_type' => $request->field_type,
            'category_id' => $request->category_id,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Advertisement criteria updated successfully!');
    }

    // GET: Show all criteria options
    public function getAdCriteriaOptions()
    {
        // Join with categories for label display
        $criterias = DB::table('advertisement_criterias')
            ->join('categories', 'advertisement_criterias.category_id', '=', 'categories.id')
            ->select(
                'advertisement_criterias.id',
                'advertisement_criterias.advertisement_criteria_name',
                'advertisement_criterias.field_type',
                'advertisement_criterias.category_id',
                'categories.category_name'
            )
            ->where('advertisement_criterias.is_active', 1)
            ->get();

        $options = DB::table('advertisement_criteria_options')->get();

        return view('adcriteriaoptions.index', compact('criterias', 'options'));
    }

    // POST: Add option
    public function addAdCriteriaOption(Request $request)
    {
        $request->validate([
            'advertisement_criteria_option_name' => 'required|string|max:255',
            'advertisement_criteria_id' => 'required|integer|exists:advertisement_criterias,id',
        ]);

        DB::table('advertisement_criteria_options')->insert([
            'advertisement_criteria_option_name' => $request->advertisement_criteria_option_name,
            'advertisement_criteria_id' => $request->advertisement_criteria_id,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Criteria option added successfully!');
    }

    // POST: Update option
    public function updateAdCriteriaOption(Request $request, $id)
    {
        $request->validate([
            'advertisement_criteria_option_name' => 'required|string|max:255',
            'advertisement_criteria_id' => 'required|integer|exists:advertisement_criterias,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('advertisement_criteria_options')->where('id', $id)->update([
            'advertisement_criteria_option_name' => $request->advertisement_criteria_option_name,
            'advertisement_criteria_id' => $request->advertisement_criteria_id,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Criteria option updated successfully!');
    }

    // GET: Show all districts
    public function getDistricts()
    {
        $districts = DB::table('districts')->get();
        return view('districts.index', compact('districts'));
    }

    // POST: Add district
    public function addDistrict(Request $request)
    {
        $request->validate([
            'district_name' => 'required|string|max:255',
        ]);

        DB::table('districts')->insert([
            'district_name' => $request->district_name,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'District added successfully!');
    }

    // POST: Update district
    public function updateDistrict(Request $request, $id)
    {
        $request->validate([
            'district_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        DB::table('districts')->where('id', $id)->update([
            'district_name' => $request->district_name,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'District updated successfully!');
    }
    // GET: Show all cities
    public function getCities()
    {
        $cities = DB::table('cities')->get();
        $districts = DB::table('districts')->where('is_active', 1)->get();
        return view('cities.index', compact('cities', 'districts'));
    }

    // POST: Add city
    public function addCity(Request $request)
    {
        $request->validate([
            'city_name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
        ]);

        DB::table('cities')->insert([
            'city_name' => $request->city_name,
            'district_id' => $request->district_id,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'City added successfully!');
    }

    // POST: Update city
    public function updateCity(Request $request, $id)
    {
        $request->validate([
            'city_name' => 'required|string|max:255',
            'district_id' => 'required|exists:districts,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('cities')->where('id', $id)->update([
            'city_name' => $request->city_name,
            'district_id' => $request->district_id,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'City updated successfully!');
    }
    // GET: Show all advertisements
    public function getAdvertisements()
    {
        $ads = DB::table('advertisements')
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name',
                'districts.district_name',
                'cities.city_name'
            )
            ->orderBy('advertisements.created_at', 'desc')
            ->get();

        return view('advertisements.index', compact('ads'));
    }

    // GET: View single advertisement
    public function viewAdvertisement($id)
    {
        $ad = DB::table('advertisements')
            ->where('advertisements.id', $id)
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'customers.address',
                'customers.telephone',
                'customers.email',
                'customers.nic_passport',
                'categories.category_name',
                'districts.district_name',
                'cities.city_name'
            )
            ->first();

        if (!$ad) {
            abort(404);
        }

        return view('advertisements.view', compact('ad'));
    }
}
