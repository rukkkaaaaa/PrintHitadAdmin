<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'category_name_en' => 'nullable|string|max:255|required_without:category_name_si',
            'category_name_si' => 'nullable|string|max:255|required_without:category_name_en'
        ]);

        DB::table('categories')->insert([
            'category_name_en' => $request->category_name_en ?: null,
            'category_name_si' => $request->category_name_si ?: null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Category added successfully!');
    }
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category_name_en' => 'nullable|string|max:255|required_without:category_name_si',
            'category_name_si' => 'nullable|string|max:255|required_without:category_name_en',
            'is_active' => 'required|boolean',
        ]);

        DB::table('categories')
            ->where('id', $id)
            ->update([
                'category_name_en' => $request->category_name_en ?: null,
                'category_name_si' => $request->category_name_si ?: null,
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    // GET: Show all ad types
    public function getAdTypes()
    {
        $adtypes = DB::table('advertisement_types')
            ->join('categories', 'advertisement_types.category_id', '=', 'categories.id')
            ->select(
                'advertisement_types.*',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name')
            )
            ->get();

        $categories = DB::table('categories')->where('is_active', 1)->get();

        return view('adtypes.index', compact('adtypes', 'categories'));
    }

    // POST: Add new ad type
    public function addAdType(Request $request)
    {
        $request->validate([
            'advertisement_type_en' => 'nullable|string|max:255|required_without:advertisement_type_si',
            'advertisement_type_si' => 'nullable|string|max:255|required_without:advertisement_type_en',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric',
        ]);

        $adTypeId = DB::table('advertisement_types')->insertGetId([
            'advertisement_type_en' => $request->advertisement_type_en ?: null,
            'advertisement_type_si' => $request->advertisement_type_si ?: null,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
            'advertisement_type_en' => 'nullable|string|max:255|required_without:advertisement_type_si',
            'advertisement_type_si' => 'nullable|string|max:255|required_without:advertisement_type_en',
            'category_id' => 'required|integer|exists:categories,id',
            'price' => 'required|numeric',
            'is_active' => 'required|boolean',
        ]);

        DB::table('advertisement_types')->where('id', $id)->update([
            'advertisement_type_en' => $request->advertisement_type_en ?: null,
            'advertisement_type_si' => $request->advertisement_type_si ?: null,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        DB::table('category_has_advertisement_types')
            ->where('advertisement_type_id', $id)
            ->delete();

        DB::table('category_has_advertisement_types')->insert([
            'category_id' => $request->category_id,
            'advertisement_type_id' => $id,
        ]);

        return redirect()->back()->with('success', 'Advertisement type updated successfully!');
    }

    // ================= TINTS =================
    // GET: Show all advertisement tints
    public function getTints()
    {
        $tints = DB::table('advertisement_tints')->get();
        return view('tints.index', compact('tints'));
    }

    // POST: Add new tint
    public function addTint(Request $request)
    {
        $request->validate([
            'advertisement_tint_en' => 'nullable|string|max:255|required_without:advertisement_tint_si',
            'advertisement_tint_si' => 'nullable|string|max:255|required_without:advertisement_tint_en',
            'price' => 'nullable|numeric',
        ]);

        DB::table('advertisement_tints')->insert([
            'advertisement_tint_en' => $request->advertisement_tint_en ?: '',
            'advertisement_tint_si' => $request->advertisement_tint_si ?: '',
            'color' => $request->color ?: '',
            'price' => $request->price ?: 0,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tint added successfully!');
    }

    // POST: Update tint
    public function updateTint(Request $request, $id)
    {
        $request->validate([
            'advertisement_tint_en' => 'nullable|string|max:255|required_without:advertisement_tint_si',
            'advertisement_tint_si' => 'nullable|string|max:255|required_without:advertisement_tint_en',
            'is_active' => 'required|boolean',
            'price' => 'nullable|numeric',
        ]);

        DB::table('advertisement_tints')->where('id', $id)->update([
            'advertisement_tint_en' => $request->advertisement_tint_en ?: '',
            'advertisement_tint_si' => $request->advertisement_tint_si ?: '',
            'color' => $request->color ?: '',
            'is_active' => $request->is_active,
            'price' => $request->price ?: 0,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tint updated successfully!');
    }

    // GET: Show all ad sizes
    public function getAdSizes()
    {
        $adSizes = DB::table('advertisement_sizes')
            ->join('advertisement_types', 'advertisement_sizes.advertisement_type_id', '=', 'advertisement_types.id')
            ->select(
                'advertisement_sizes.*',
                'advertisement_types.advertisement_type_en as type_name'
            )
            ->orderBy('advertisement_sizes.id', 'asc')
            ->get()
            ->map(function ($size) {
                $size->display_img_url = $this->resolveAdSizeImageUrl($size->img_url ?? null);

                return $size;
            });

        $adTypesEn = DB::table('advertisement_types')
            ->where('is_active', 1)
            ->whereNotNull('advertisement_type_en')
            ->where('advertisement_type_en', '!=', '')
            ->orderBy('advertisement_type_en')
            ->get();

        $adTypesSi = DB::table('advertisement_types')
            ->where('is_active', 1)
            ->whereNotNull('advertisement_type_si')
            ->where('advertisement_type_si', '!=', '')
            ->orderBy('advertisement_type_si')
            ->get();

        return view('adsizes.index', compact('adSizes', 'adTypesEn', 'adTypesSi'));
    }

    // POST: Add new ad size
    public function addAdSize(Request $request)
    {
        $request->validate([
            'advertisement_size_en' => 'nullable|string|max:255|required_without:advertisement_size_si',
            'advertisement_size_si' => 'nullable|string|max:255|required_without:advertisement_size_en',
            'ad_word_count' => 'required|integer|min:1',
            'max_images' => 'required|integer|min:1',
            'advertisement_type_id' => 'required|integer|exists:advertisement_types,id',
            'price' => 'required|numeric',
            'img_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('img_url')) {
            $imagePath = $request->file('img_url')->storePublicly('adsizes', 'oracle');
            // convert stored key to public url and save that to DB
            try {
                $imagePath = Storage::disk('oracle')->url($imagePath);
            } catch (\Throwable $e) {
                // fallback to the returned path
            }
        }

        $adSizeId = DB::table('advertisement_sizes')->insertGetId([
            'advertisement_size_en' => $request->advertisement_size_en ?: null,
            'advertisement_size_si' => $request->advertisement_size_si ?: null,
            'ad_word_count' => $request->ad_word_count,
            'max_images' => $request->max_images,
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
            'advertisement_size_en' => 'nullable|string|max:255|required_without:advertisement_size_si',
            'advertisement_size_si' => 'nullable|string|max:255|required_without:advertisement_size_en',
            'ad_word_count' => 'required|integer|min:1',
            'max_images' => 'required|integer|min:1',
            'advertisement_type_id' => 'required|integer|exists:advertisement_types,id',
            'price' => 'required|numeric',
            'is_active' => 'required|boolean',
            'img_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'advertisement_size_en' => $request->advertisement_size_en ?: null,
            'advertisement_size_si' => $request->advertisement_size_si ?: null,
            'ad_word_count' => $request->ad_word_count,
            'max_images' => $request->max_images,
            'advertisement_type_id' => $request->advertisement_type_id,
            'price' => $request->price,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ];

        if ($request->hasFile('img_url')) {
            $imagePath = $request->file('img_url')->storePublicly('adsizes', 'oracle');
            try {
                $data['img_url'] = Storage::disk('oracle')->url($imagePath);
            } catch (\Throwable $e) {
                $data['img_url'] = $imagePath;
            }
        }

        DB::table('advertisement_sizes')->where('id', $id)->update($data);

        DB::table('advertisement_type_has_advertisement_sizes')
            ->where('advertisement_size_id', $id)
            ->delete();

        DB::table('advertisement_type_has_advertisement_sizes')->insert([
            'advertisement_type_id' => $request->advertisement_type_id,
            'advertisement_size_id' => $id,
        ]);

        return redirect()->back()->with('success', 'Advertisement size updated successfully!');
    }

    private function resolveAdSizeImageUrl(?string $imgUrl): ?string
    {
        if (!$imgUrl) {
            return null;
        }

        if (Str::startsWith($imgUrl, ['http://', 'https://', '//'])) {
            return $imgUrl;
        }

        if (Str::startsWith($imgUrl, '/')) {
            return $imgUrl;
        }

        try {
            return Storage::disk('oracle')->url($imgUrl);
        } catch (\Throwable $e) {
            return asset('storage/' . ltrim($imgUrl, '/'));
        }
    }

    // GET: Show all advertisement criterias
    public function getAdCriterias()
    {
        $criterias = DB::table('advertisement_criterias')
            ->join('categories', 'advertisement_criterias.category_id', '=', 'categories.id')
            ->select(
                'advertisement_criterias.*',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name')
            )
            ->get();

        $categoriesEn = DB::table('categories')
            ->where('is_active', 1)
            ->whereNotNull('category_name_en')
            ->where('category_name_en', '!=', '')
            ->orderBy('category_name_en')
            ->get();

        $categoriesSi = DB::table('categories')
            ->where('is_active', 1)
            ->whereNotNull('category_name_si')
            ->where('category_name_si', '!=', '')
            ->orderBy('category_name_si')
            ->get();

        $categories = DB::table('categories')
            ->where('is_active', 1)
            ->get();

        return view('adcriterias.index', compact('criterias', 'categoriesEn', 'categoriesSi', 'categories'));
    }

    // POST: Add new criteria
    public function addAdCriteria(Request $request)
    {
        $request->validate([
            'advertisement_criteria_name_en' => 'nullable|string|max:255|required_without:advertisement_criteria_name_si',
            'advertisement_criteria_name_si' => 'nullable|string|max:255|required_without:advertisement_criteria_name_en',
            'field_type' => 'required|string|max:50',
            'category_id' => 'required|integer|exists:categories,id',
        ]);

        DB::table('advertisement_criterias')->insert([
            'advertisement_criteria_name_en' => $request->advertisement_criteria_name_en ?: null,
            'advertisement_criteria_name_si' => $request->advertisement_criteria_name_si ?: null,
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
            'advertisement_criteria_name_en' => 'nullable|string|max:255|required_without:advertisement_criteria_name_si',
            'advertisement_criteria_name_si' => 'nullable|string|max:255|required_without:advertisement_criteria_name_en',
            'field_type' => 'required|string|max:50',
            'category_id' => 'required|integer|exists:categories,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('advertisement_criterias')->where('id', $id)->update([
            'advertisement_criteria_name_en' => $request->advertisement_criteria_name_en ?: null,
            'advertisement_criteria_name_si' => $request->advertisement_criteria_name_si ?: null,
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
        $criterias = DB::table('advertisement_criterias')
            ->join('categories', 'advertisement_criterias.category_id', '=', 'categories.id')
            ->select(
                'advertisement_criterias.id',
                'advertisement_criterias.advertisement_criteria_name_en',
                'advertisement_criterias.advertisement_criteria_name_si',
                'advertisement_criterias.field_type',
                'advertisement_criterias.category_id',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name')
            )
            ->where('advertisement_criterias.is_active', 1)
            ->get();

        $criterias = $criterias->map(function ($crit) {
            $label = $crit->advertisement_criteria_name_en ?: $crit->advertisement_criteria_name_si;

            if (filled($crit->category_name)) {
                $label .= ' (' . $crit->category_name . ')';
            }

            $crit->criteria_label = $label ?: 'N/A';

            return $crit;
        });

        $criteriasEn = $criterias
            ->filter(fn ($crit) => filled($crit->advertisement_criteria_name_en))
            ->map(function ($crit) {
                $label = $crit->advertisement_criteria_name_en;

                if (filled($crit->category_name)) {
                    $label .= ' (' . $crit->category_name . ')';
                }

                $crit->criteria_label = $label;

                return $crit;
            })
            ->values();

        $criteriasSi = $criterias
            ->filter(fn ($crit) => filled($crit->advertisement_criteria_name_si))
            ->map(function ($crit) {
                $label = $crit->advertisement_criteria_name_si;

                if (filled($crit->category_name)) {
                    $label .= ' (' . $crit->category_name . ')';
                }

                $crit->criteria_label = $label;

                return $crit;
            })
            ->values();

        $options = DB::table('advertisement_criteria_options')->get();

        return view('adcriteriaoptions.index', compact('criterias', 'criteriasEn', 'criteriasSi', 'options'));
    }

    // POST: Add option
    public function addAdCriteriaOption(Request $request)
    {
        $request->validate([
            'advertisement_criteria_option_name_en' => 'nullable|string|max:255|required_without:advertisement_criteria_option_name_si',
            'advertisement_criteria_option_name_si' => 'nullable|string|max:255|required_without:advertisement_criteria_option_name_en',
            'advertisement_criteria_id' => 'required|integer|exists:advertisement_criterias,id',
        ]);

        DB::table('advertisement_criteria_options')->insert([
            'advertisement_criteria_option_name_en' => $request->advertisement_criteria_option_name_en ?: null,
            'advertisement_criteria_option_name_si' => $request->advertisement_criteria_option_name_si ?: null,
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
            'advertisement_criteria_option_name_en' => 'nullable|string|max:255|required_without:advertisement_criteria_option_name_si',
            'advertisement_criteria_option_name_si' => 'nullable|string|max:255|required_without:advertisement_criteria_option_name_en',
            'advertisement_criteria_id' => 'required|integer|exists:advertisement_criterias,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('advertisement_criteria_options')->where('id', $id)->update([
            'advertisement_criteria_option_name_en' => $request->advertisement_criteria_option_name_en ?: null,
            'advertisement_criteria_option_name_si' => $request->advertisement_criteria_option_name_si ?: null,
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
            'district_name_en' => 'nullable|string|max:255|required_without:district_name_si',
            'district_name_si' => 'nullable|string|max:255|required_without:district_name_en',
        ]);

        DB::table('districts')->insert([
            'district_name_en' => $request->district_name_en ?: null,
            'district_name_si' => $request->district_name_si ?: null,
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
            'district_name_en' => 'nullable|string|max:255|required_without:district_name_si',
            'district_name_si' => 'nullable|string|max:255|required_without:district_name_en',
            'is_active' => 'required|boolean',
        ]);

        DB::table('districts')->where('id', $id)->update([
            'district_name_en' => $request->district_name_en ?: null,
            'district_name_si' => $request->district_name_si ?: null,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'District updated successfully!');
    }
    // GET: Show all cities
    public function getCities()
    {
        $cities = DB::table('cities')
            ->join('districts', 'cities.district_id', '=', 'districts.id')
            ->select(
                'cities.*',
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name')
            )
            ->get();

        $districts = DB::table('districts')
            ->where('is_active', 1)
            ->get();

        $districtsEn = $districts
            ->filter(fn ($dist) => filled($dist->district_name_en))
            ->values();

        $districtsSi = $districts
            ->filter(fn ($dist) => filled($dist->district_name_si))
            ->values();

        return view('cities.index', compact('cities', 'districts', 'districtsEn', 'districtsSi'));
    }

    // POST: Add city
    public function addCity(Request $request)
    {
        $request->validate([
            'city_name_en' => 'nullable|string|max:255|required_without:city_name_si',
            'city_name_si' => 'nullable|string|max:255|required_without:city_name_en',
            'district_id' => 'required|exists:districts,id',
        ]);

        DB::table('cities')->insert([
            'city_name_en' => $request->city_name_en ?: null,
            'city_name_si' => $request->city_name_si ?: null,
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
            'city_name_en' => 'nullable|string|max:255|required_without:city_name_si',
            'city_name_si' => 'nullable|string|max:255|required_without:city_name_en',
            'district_id' => 'required|exists:districts,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('cities')
            ->where('id', $id)
            ->update([
                'city_name_en' => $request->city_name_en ?: null,
                'city_name_si' => $request->city_name_si ?: null,
                'district_id' => $request->district_id,
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'City updated successfully!');
    }
    // GET: Show all advertisements
    public function getAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            // ✅ PAYMENTS
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                'payments.payment_status',
                'payments.is_success'
            )

            // ✅ 🔥 IMPORTANT FILTER (THIS IS WHAT YOU WANT)
            ->where('advertisements.publication', 'hitad_print');

        // 🔍 Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
                    ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
            });
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only('search'));

        return view('advertisements.index', compact('ads'))
            ->with('search', $request->search);
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

            // ✅ LEFT JOIN payments (important)
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')

            // ✅ LEFT JOIN payment methods
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            ->select(
                'advertisements.*',

                'customers.customer_name',
                'customers.address',
                'customers.telephone',
                'customers.email',
                'customers.nic_passport',

                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                // ✅ Payment fields
                'payments.amount',
                'payments.payment_status',
                'payments.payment_date',
                'payments.is_success',

                'payment_methods.payment_method_name as payment_method'
            )
            ->first();

        if (!$ad) {
            abort(404);
        }

        // Load category-specific criterias and existing values for display
        $criterias = DB::table('advertisement_criterias')
            ->where('category_id', $ad->category_id)
            ->where('is_active', 1)
            ->get();

        $criteriaOptions = DB::table('advertisement_criteria_options')
            ->whereIn('advertisement_criteria_id', $criterias->pluck('id'))
            ->where('is_active', 1)
            ->get()
            ->groupBy('advertisement_criteria_id');

        $criteriaValuesRaw = DB::table('advertisement_criteria_values')
            ->where('advertisement_id', $id)
            ->get();

        $criteriaValues = [];
        foreach ($criteriaValuesRaw as $cv) {
            $criteriaValues[$cv->advertisement_criteria_id] = $cv->advertisement_criteria_option_value;
        }

        return view('advertisements.view', compact('ad', 'criterias', 'criteriaOptions', 'criteriaValues'));
    }

    public function getPaidAdvertisements()
    {
        $ads = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->join('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY HITAD PRINT ADS
            ->where('advertisements.publication', 'hitad_print')

            // ✅ ONLY PAID
            ->where('payments.payment_status', 'completed')
            ->where('payments.is_success', true)

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payments.is_success',
                'payment_methods.payment_method_name as payment_method'
            )

            ->orderBy('advertisements.id', 'desc')
            ->get();

        return view('advertisements.paid', compact('ads'));
    }
    public function getUnpaidAdvertisements()
    {
        $ads = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY HITAD PRINT ADS
            ->where('advertisements.publication', 'hitad_print')

            // ✅ UNPAID LOGIC
            ->where(function ($query) {
                $query->whereNull('payments.id') // no payment
                    ->orWhere('payments.payment_status', 'pending') // pending
                    ->orWhere('payments.is_success', false); // failed
            })

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payments.is_success',
                'payment_methods.payment_method_name as payment_method'
            )

            ->orderBy('advertisements.id', 'desc')
            ->get();

        return view('advertisements.unpaid', compact('ads'));
    }
    public function getLahipitaAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                'payments.payment_status',
                'payments.is_success'
            )

            // ✅ MAIN FILTER
            ->where('advertisements.publication', 'lahipita');

        // 🔍 search (same as your existing)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
                    ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
            });
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only('search'));

        return view('advertisements.lahipita_all', compact('ads'))
            ->with('search', $request->search);
    }
    public function getLahipitaPaidAdvertisements()
    {
        $ads = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->join('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY LAHIPITA ADS
            ->where('advertisements.publication', 'lahipita')

            // ✅ ONLY PAID
            ->where('payments.payment_status', 'completed')
            ->where('payments.is_success', true)

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payments.is_success',
                'payment_methods.payment_method_name as payment_method'
            )

            ->orderBy('advertisements.id', 'desc')
            ->get();

        return view('advertisements.lahipita_paid', compact('ads'));
    }
    public function getLahipitaUnpaidAdvertisements()
    {
        $ads = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY LAHIPITA ADS
            ->where('advertisements.publication', 'lahipita')

            // ✅ UNPAID LOGIC (IMPORTANT)
            ->where(function ($query) {
                $query->whereNull('payments.id')
                    ->orWhere('payments.payment_status', 'pending')
                    ->orWhere('payments.is_success', false);
            })

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payments.is_success',
                'payment_methods.payment_method_name as payment_method'
            )

            ->orderBy('advertisements.id', 'desc')
            ->get();

        return view('advertisements.lahipita_unpaid', compact('ads'));
    }
    public function downloadAdvertisement($id)
    {
        $ad = DB::table('advertisements')
            ->where('advertisements.id', $id)
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'customers.address',
                'customers.telephone',
                'customers.email',
                'categories.category_name_en as category_name',
                'districts.district_name_en as district_name',
                'cities.city_name_en as city_name',
                'payments.amount',
                'payments.payment_status',
                'payments.payment_date',
                'payment_methods.payment_method_name as payment_method'
            )
            ->first();

        if (!$ad) {
            abort(404);
        }

        // ✅ Generate PDF
        $pdf = Pdf::loadView('advertisements.pdf', compact('ad'));

        $pdfPath = storage_path("app/public/ad_{$id}.pdf");
        file_put_contents($pdfPath, $pdf->output());

        // ✅ Create ZIP
        $zipPath = storage_path("app/public/ad_{$id}.zip");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($pdfPath, "advertisement_{$id}.pdf");
            $zip->close();
        }

        // ✅ Download ZIP
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
    public function editAdvertisement($id)
    {
        $ad = DB::table('advertisements')
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'customers.address',
                'customers.telephone',
                'customers.email',
                'customers.nic_passport'
            )
            ->where('advertisements.id', $id)
            ->first();

        $categories = DB::table('categories')->where('is_active', 1)->get();
        $districts = DB::table('districts')->where('is_active', 1)->get();
        $cities = DB::table('cities')->where('is_active', 1)->get();

        // Load category-specific criterias and options
        $criterias = DB::table('advertisement_criterias')
            ->where('category_id', $ad->category_id)
            ->where('is_active', 1)
            ->get();

        $criteriaOptions = DB::table('advertisement_criteria_options')
            ->whereIn('advertisement_criteria_id', $criterias->pluck('id'))
            ->where('is_active', 1)
            ->get()
            ->groupBy('advertisement_criteria_id');

        // Existing values for this advertisement
        $criteriaValuesRaw = DB::table('advertisement_criteria_values')
            ->where('advertisement_id', $id)
            ->get();

        $criteriaValues = [];
        foreach ($criteriaValuesRaw as $cv) {
            $criteriaValues[$cv->advertisement_criteria_id] = $cv->advertisement_criteria_option_value;
        }

        if (!$ad) {
            abort(404);
        }

        return view('advertisements.edit', compact('ad', 'categories', 'districts', 'cities', 'criterias', 'criteriaOptions', 'criteriaValues'));
    }
    public function updateAdvertisement(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'nic_passport' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'advertisement_description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'city_id' => 'required|exists:cities,id',
            'publish_date' => 'required|date',
            'web_combined_ad' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request, $id) {
            $ad = DB::table('advertisements')->where('id', $id)->first();

            if (!$ad) {
                abort(404);
            }

            DB::table('customers')
                ->where('id', $ad->customer_id)
                ->update([
                    'customer_name' => $request->customer_name,
                    'address' => $request->address,
                    'telephone' => $request->telephone,
                    'nic_passport' => $request->nic_passport,
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);

            DB::table('advertisements')->where('id', $id)->update([
                'advertisement_description' => $request->advertisement_description,
                'category_id' => $request->category_id,
                'district_id' => $request->district_id,
                'city_id' => $request->city_id,
                'publish_date' => $request->publish_date,
                'web_combined_ad' => $request->web_combined_ad,
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            // Process advertisement criterias (if any)
            $criteriaInput = $request->input('criteria', []);
            if (is_array($criteriaInput) && count($criteriaInput) > 0) {
                foreach ($criteriaInput as $criteriaId => $criteriaValue) {
                    // normalize scalar values
                    if (is_array($criteriaValue)) {
                        $value = implode(', ', $criteriaValue);
                    } else {
                        $value = $criteriaValue;
                    }

                    $existing = DB::table('advertisement_criteria_values')
                        ->where('advertisement_id', $id)
                        ->where('advertisement_criteria_id', $criteriaId)
                        ->first();

                    if ($existing) {
                        DB::table('advertisement_criteria_values')
                            ->where('id', $existing->id)
                            ->update([
                                'advertisement_criteria_option_value' => $value,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('advertisement_criteria_values')->insert([
                            'advertisement_id' => $id,
                            'advertisement_criteria_id' => $criteriaId,
                            'advertisement_criteria_option_value' => $value,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        return redirect('/advertisements')->with('success', 'Advertisement updated successfully!');
    }
}
