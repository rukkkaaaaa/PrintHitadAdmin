<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use ZipStream\ZipStream;
use App\Models\AdvertisementEmail;
use App\Models\Advertisement;
use App\Mail\UnpaidAdvertisementMail;
use App\Models\PaymentMethod;
use Spatie\Browsershot\Browsershot;
use Illuminate\Validation\Rule;

class GeneralController extends Controller
{
    public function getMembers(Request $request)
{
    $search = trim($request->input('search', ''));

    $members = DB::table('customers')
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('nic_passport', 'LIKE', "%{$search}%");
            });
        })
        ->orderBy('id', 'desc')
        ->get();

    return view('members.index', compact('members', 'search'));
}

    /**
     * Return list of categories and render the categories.index view.
     *
     * @return \Illuminate\View\View
     */
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
    /**
     * Update an existing category by id.
     * Validates input and updates category_name_en/si and is_active flag.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Update advertisement type by id and its category mapping.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
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

        $categories = DB::table('categories')
            ->where('is_active', 1)
            ->orderBy('category_name_en')
            ->orderBy('category_name_si')
            ->get();

        $categoriesEn = $categories
            ->filter(fn($category) => filled($category->category_name_en))
            ->values();

        $categoriesSi = $categories
            ->filter(fn($category) => filled($category->category_name_si))
            ->values();

        $tints = DB::table('advertisement_tints')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($tint) {
                $tint->advertisement_type_label = null;

                return $tint;
            });

        $tintCategories = DB::table('category_has_advertisement_tints')
            ->join('categories', 'category_has_advertisement_tints.category_id', '=', 'categories.id')
            ->select(
                'category_has_advertisement_tints.advertisement_tint_id',
                'categories.id',
                'categories.category_name_en',
                'categories.category_name_si'
            )
            ->orderBy('categories.category_name_en')
            ->orderBy('categories.category_name_si')
            ->get()
            ->groupBy('advertisement_tint_id');

        $tints = $tints->map(function ($tint) use ($tintCategories) {
            $categories = ($tintCategories[$tint->id] ?? collect())->values();

            $tint->categories = $categories;
            $tint->category_ids = $categories->pluck('id')->map(fn($id) => (int) $id)->all();

            return $tint;
        });

        return view('tints.index', compact('tints', 'categories', 'categoriesEn', 'categoriesSi'));
    }

    // POST: Add new tint
    public function addTint(Request $request)
    {
        $request->validate([
            'advertisement_tint_en' => 'nullable|string|max:255|required_without:advertisement_tint_si',
            'advertisement_tint_si' => 'nullable|string|max:255|required_without:advertisement_tint_en',
            'price' => 'nullable|numeric',
            'category_ids' => 'required|array|size:1',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $categoryIds = collect($request->input('category_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($categoryIds->count() !== 1) {
            return redirect()->back()
                ->withErrors(['category_ids' => 'Please select exactly one category.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $categoryIds) {
            $tintData = [
                'advertisement_tint_en' => $request->advertisement_tint_en ?: '',
                'advertisement_tint_si' => $request->advertisement_tint_si ?: '',
                'category_id' => $categoryIds->first(),
                'color' => $request->color ?: '',
                'price' => $request->price ?: 0,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $tintId = DB::table('advertisement_tints')->insertGetId($tintData);


            DB::table('category_has_advertisement_tints')->insert(
                $categoryIds->map(fn($categoryId) => [
                    'category_id' => $categoryId,
                    'advertisement_tint_id' => $tintId,
                ])->all()
            );
        });

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
            'category_ids' => 'required|array|size:1',
            'category_ids.*' => 'integer|exists:categories,id',
        ]);

        $categoryIds = collect($request->input('category_ids', []))
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($categoryIds->count() !== 1) {
            return redirect()->back()
                ->withErrors(['category_ids' => 'Please select exactly one category.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $id, $categoryIds) {
            $tintData = [
                'advertisement_tint_en' => $request->advertisement_tint_en ?: '',
                'advertisement_tint_si' => $request->advertisement_tint_si ?: '',
                'category_id' => $categoryIds->first(),
                'color' => $request->color ?: '',
                'is_active' => $request->is_active,
                'price' => $request->price ?: 0,
                'updated_at' => now(),
            ];

            DB::table('advertisement_tints')->where('id', $id)->update($tintData);


            DB::table('category_has_advertisement_tints')
                ->where('advertisement_tint_id', $id)
                ->delete();

            DB::table('category_has_advertisement_tints')->insert(
                $categoryIds->map(fn($categoryId) => [
                    'category_id' => $categoryId,
                    'advertisement_tint_id' => $id,
                ])->all()
            );
        });

        return redirect()->back()->with('success', 'Tint updated successfully!');
    }


    // GET: Show all ad sizes
    public function getAdSizes()
    {
        $adSizes = DB::table('advertisement_sizes')
            // join advertisement_types and categories. prefer English labels but fall back to Sinhala when EN is missing
            ->leftJoin('advertisement_types', 'advertisement_sizes.advertisement_type_id', '=', 'advertisement_types.id')
            ->leftJoin('categories', 'advertisement_types.category_id', '=', 'categories.id')
            ->select(
                'advertisement_sizes.*',
                'advertisement_types.category_id as type_category_id',
                DB::raw('COALESCE(advertisement_types.advertisement_type_en, advertisement_types.advertisement_type_si) as type_name'),
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name')
            )
            ->orderBy('advertisement_sizes.id', 'asc')
            ->get()
            ->map(function ($size) {
                $size->display_img_url = $this->resolveAdSizeImageUrl($size->img_url ?? null);

                return $size;
            });

        $adTypesEn = DB::table('advertisement_types')
            ->where('is_active', 1)
            ->orderBy('advertisement_type_en')
            ->orderBy('advertisement_type_si')
            ->get();

        $adTypesSi = DB::table('advertisement_types')
            ->where('is_active', 1)
            ->orderBy('advertisement_type_si')
            ->orderBy('advertisement_type_en')
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

        return view('adsizes.index', compact('adSizes', 'adTypesEn', 'adTypesSi', 'categoriesEn', 'categoriesSi'));
    }

    /**
     * AJAX: Return advertisement types for a given category.
     * Responds with JSON containing id, localized label and category_id.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    // AJAX: return ad types for a given category
    public function getAdTypesByCategory(Request $request, $categoryId)
    {
        $lang = $request->query('lang', 'en');

        $types = DB::table('advertisement_types')
            ->where('is_active', 1)
            ->where('category_id', $categoryId)
            ->get()
            ->map(function ($t) use ($lang) {
                $label = $lang === 'si'
                    ? ($t->advertisement_type_si ?: $t->advertisement_type_en)
                    : ($t->advertisement_type_en ?: $t->advertisement_type_si);

                return [
                    'id' => $t->id,
                    'label' => $label,
                    'label_en' => $t->advertisement_type_en,
                    'label_si' => $t->advertisement_type_si,
                    'category_id' => $t->category_id,
                ];
            });

        return response()->json($types);
    }


    /**
     * AJAX: Return advertisement tints for a given category.
     * Responds with JSON containing id and localized label.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTintsByCategory(Request $request, $categoryId)
    {
        $lang = $request->query('lang', 'en');

        $tintsQuery = DB::table('advertisement_tints')
            ->join('category_has_advertisement_tints', 'advertisement_tints.id', '=', 'category_has_advertisement_tints.advertisement_tint_id')
            ->where('advertisement_tints.is_active', 1)
            ->where('category_has_advertisement_tints.category_id', $categoryId)
            ->select(
                'advertisement_tints.id',
                'advertisement_tints.advertisement_tint_en',
                'advertisement_tints.advertisement_tint_si'
            )
            ->orderBy('advertisement_tints.advertisement_tint_en')
            ->orderBy('advertisement_tints.advertisement_tint_si');

        $tints = $tintsQuery
            ->get()
            ->map(function ($tint) use ($lang) {
                $label = $lang === 'si'
                    ? ($tint->advertisement_tint_si ?: $tint->advertisement_tint_en)
                    : ($tint->advertisement_tint_en ?: $tint->advertisement_tint_si);

                return [
                    'id' => $tint->id,
                    'label' => $label,
                ];
            });

        return response()->json($tints);
    }


    /**
     * AJAX: Return advertisement sizes for a given advertisement type.
     * Returns localized label as JSON.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $typeId
     * @return \Illuminate\Http\JsonResponse
     */
    // AJAX: get ad sizes for a type
    public function getAdSizesByType(Request $request, $typeId)
    {
        $lang = $request->query('lang', 'en');

        $sizes = DB::table('advertisement_sizes')
            ->where('is_active', 1)
            ->where('advertisement_type_id', $typeId)
            ->get()
            ->map(function ($s) use ($lang) {
                $label = $lang === 'si'
                    ? ($s->advertisement_size_si ?: $s->advertisement_size_en)
                    : ($s->advertisement_size_en ?: $s->advertisement_size_si);

                return [
                    'id'    => $s->id,
                    'label' => $label,
                ];
            });

        return response()->json($sizes);
    }

    /**
     * AJAX: Get advertisement criterias and their options for a category.
     * Returns an array of criterias with localized labels and options grouped.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    // AJAX: get criterias with options for a category
    public function getCriteriasByCategory(Request $request, $categoryId)
    {
        $lang = $request->query('lang', 'en');

        $criterias = DB::table('advertisement_criterias')
            ->where('is_active', 1)
            ->where('category_id', $categoryId)
            ->orderBy('id')
            ->get();

        $criteriaIds = $criterias->pluck('id')->toArray();

        $options = DB::table('advertisement_criteria_options')
            ->where('is_active', 1)
            ->whereIn('advertisement_criteria_id', $criteriaIds)
            ->get()
            ->groupBy('advertisement_criteria_id');

        $result = $criterias->map(function ($c) use ($lang, $options) {
            $nameEn = trim($c->advertisement_criteria_name_en ?? '');
            $nameSi = trim($c->advertisement_criteria_name_si ?? '');
            $label  = $lang === 'si' ? ($nameSi ?: $nameEn) : ($nameEn ?: $nameSi);

            $opts = ($options[$c->id] ?? collect())->map(function ($o) use ($lang) {
                $optEn = trim($o->advertisement_criteria_option_name_en ?? '');
                $optSi = trim($o->advertisement_criteria_option_name_si ?? '');
                $optLabel = $lang === 'si' ? ($optSi ?: $optEn) : ($optEn ?: $optSi);
                return [
                    'id'    => $o->id,
                    'label' => $optLabel,
                    'en'    => $optEn,
                    'si'    => $optSi,
                ];
            })->filter(fn($o) => $o['label'] !== '')->values();

            return [
                'id'         => $c->id,
                'label'      => $label ?: ('Criteria #' . $c->id),
                'name_en'    => $nameEn,
                'name_si'    => $nameSi,
                'field_type' => $c->field_type,
                'options'    => $opts,
            ];
        });

        return response()->json($result);
    }

    // POST: Add new ad size
    public function addAdSize(Request $request)
    {
        $request->validate([
            'advertisement_size_en' => 'nullable|string|max:255|required_without:advertisement_size_si',
            'advertisement_size_si' => 'nullable|string|max:255|required_without:advertisement_size_en',
            'category_id' => 'required|integer|exists:categories,id',
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
            'advertisement_type_id' => $request->advertisement_type_id,
            'price' => $request->price,
            // if storage isn't configured or migration not run yet, store empty string instead of null
            'img_url' => $imagePath ?: '',
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

    /**
     * POST: Update advertisement size record by id. Handles optional image upload and updates the mapping to the ad type.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Update ad size
    public function updateAdSize(Request $request, $id)
    {
        $request->validate([
            'advertisement_size_en' => 'nullable|string|max:255|required_without:advertisement_size_si',
            'advertisement_size_si' => 'nullable|string|max:255|required_without:advertisement_size_en',
            'category_id' => 'required|integer|exists:categories,id',
            'advertisement_type_id' => 'required|integer|exists:advertisement_types,id',
            'price' => 'required|numeric',
            'is_active' => 'required|boolean',
            'img_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'advertisement_size_en' => $request->advertisement_size_en ?: null,
            'advertisement_size_si' => $request->advertisement_size_si ?: null,
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

    /**
     * GET: Show all advertisement criterias joined with their category names.
     *
     * @return \Illuminate\View\View
     */
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

    /**
     * POST: Add a new advertisement criteria for a category.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Add new criteria
    public function addAdCriteria(Request $request)
    {
        $request->validate([
            'advertisement_criteria_name_en' => 'nullable|string|max:255|required_without:advertisement_criteria_name_si',
            'advertisement_criteria_name_si' => 'nullable|string|max:255|required_without:advertisement_criteria_name_en',
            'field_type' => 'required|in:dropdown,textarea,radio',
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

    /**
     * POST: Update an existing advertisement criteria by id.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Update criteria
    public function updateAdCriteria(Request $request, $id)
    {
        $request->validate([
            'advertisement_criteria_name_en' => 'nullable|string|max:255|required_without:advertisement_criteria_name_si',
            'advertisement_criteria_name_si' => 'nullable|string|max:255|required_without:advertisement_criteria_name_en',
            'field_type' => 'required|in:dropdown,textarea,radio',
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

    /**
     * GET: Show all criteria options and provide pre-formatted labels for use in the UI.
     *
     * @return \Illuminate\View\View
     */
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
            ->filter(fn($crit) => filled($crit->advertisement_criteria_name_en))
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
            ->filter(fn($crit) => filled($crit->advertisement_criteria_name_si))
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

    /**
     * POST: Add a new advertisement criteria option.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * POST: Update an existing advertisement criteria option.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * GET: Show all districts and return the districts.index view.
     *
     * @return \Illuminate\View\View
     */
    // GET: Show all districts
    public function getDistricts()
    {
        $districts = DB::table('districts')->get();
        return view('districts.index', compact('districts'));
    }

    /**
     * POST: Add a new district.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Add district
    public function addDistrict(Request $request)
    {
        $request->validate([
            'district_name' => 'nullable|string|max:255|required_without:district_name',
            'district_name' => 'nullable|string|max:255|required_without:district_name',
        ]);

        DB::table('districts')->insert([
            'district_name' => $request->district_name ?: null,
            'district_name' => $request->district_name ?: null,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'District added successfully!');
    }

    /**
     * POST: Update an existing district by id.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Update district
    public function updateDistrict(Request $request, $id)
    {
        $request->validate([
            'district_name' => 'nullable|string|max:255|required_without:district_name',
            'district_name' => 'nullable|string|max:255|required_without:district_name',
            'is_active' => 'required|boolean',
        ]);

        DB::table('districts')->where('id', $id)->update([
            'district_name' => $request->district_name ?: null,
            'district_name' => $request->district_name ?: null,
            'is_active' => $request->is_active,
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'District updated successfully!');
    }
    /**
     * GET: Show all cities joined with districts and prepare localized district lists.
     *
     * @return \Illuminate\View\View
     */
    // GET: Show all cities
    public function getCities()
    {
        $cities = DB::table('cities')
            ->join('districts', 'cities.district_id', '=', 'districts.id')
            ->select(
                'cities.*',
                DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name')
            )
            ->get();

        $districts = DB::table('districts')
            ->where('is_active', 1)
            ->get();

        $districtsEn = $districts
            ->filter(fn($dist) => filled($dist->district_name))
            ->values();

        $districtsSi = $districts
            ->filter(fn($dist) => filled($dist->district_name))
            ->values();

        return view('cities.index', compact('cities', 'districts', 'districtsEn', 'districtsSi'));
    }

    /**
     * POST: Add a new city associated with a district.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Add city
    public function addCity(Request $request)
    {
        $request->validate([
            'city_name' => 'nullable|string|max:255|required_without:city_name',
            'city_name' => 'nullable|string|max:255|required_without:city_name',
            'district_id' => 'required|exists:districts,id',
        ]);

        DB::table('cities')->insert([
            'city_name' => $request->city_name ?: null,
            'city_name' => $request->city_name ?: null,
            'district_id' => $request->district_id,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'City added successfully!');
    }

    /**
     * POST: Update an existing city by id.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Update city
    public function updateCity(Request $request, $id)
    {
        $request->validate([
            'city_name' => 'nullable|string|max:255|required_without:city_name',
            'city_name' => 'nullable|string|max:255|required_without:city_name',
            'district_id' => 'required|exists:districts,id',
            'is_active' => 'required|boolean',
        ]);

        DB::table('cities')
            ->where('id', $id)
            ->update([
                'city_name' => $request->city_name ?: null,
                'city_name' => $request->city_name ?: null,
                'district_id' => $request->district_id,
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'City updated successfully!');
    }


    /**
     * GET: Prepare data for the create advertisement form (categories, districts, cities, criterias, payment methods).
     *
     * @return \Illuminate\View\View
     */
    // GET: Create advertisement form
    public function createAdvertisement()
    {
        $categories = DB::table('categories')
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        $districts = DB::table('districts')
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        $cities = DB::table('cities')
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        $criterias = DB::table('advertisement_criterias')
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        $criteriaOptions = DB::table('advertisement_criteria_options')
            ->where('is_active', 1)
            ->get()
            ->groupBy('advertisement_criteria_id');

        $paymentMethods = DB::table('payment_methods')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        $publicationDeadlines = $this->fetchPublicationDeadlines();

        $generalSettings = $this->fetchGeneralSettings();

        $topAdSupported = Schema::hasColumn('advertisements', 'top_ad');

        return view('advertisements.create', compact('categories', 'districts', 'cities', 'criterias', 'criteriaOptions', 'paymentMethods', 'publicationDeadlines', 'generalSettings', 'topAdSupported'));
    }

    /**
     * Show general advertisement settings page for admins.
     *
     * @return \Illuminate\View\View
     */
    public function getGeneralSettings()
    {
        $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
        if (!in_array($currentRole, ['super admin', 'site admin'], true)) {
            abort(403);
        }

        $schemaMissing = !Schema::hasTable('general_settings');
        $settings = $this->fetchGeneralSettings();

        return view('general_settings.index', compact('settings', 'schemaMissing'));
    }

    /**
     * Update general advertisement settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGeneralSettings(Request $request)
    {
        if (!Schema::hasTable('general_settings')) {
            return redirect()->back()->with('error', 'General settings table is missing. Please run migrations first.');
        }

        $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
        if (!in_array($currentRole, ['super admin', 'site admin'], true)) {
            abort(403);
        }

        $request->validate([
            'max_words_en' => 'required|integer|min:0|max:100000',
            'max_words_si' => 'required|integer|min:0|max:100000',
            'additional_word_rate_en' => 'required|numeric|min:0|max:999999.99',
            'additional_word_rate_si' => 'required|numeric|min:0|max:999999.99',
            'free_word_limit_en' => 'required|integer|min:0|max:100000',
            'free_word_limit_si' => 'required|integer|min:0|max:100000',
            'top_ad_rate_en' => 'required|numeric|min:0|max:999999.99',
            'top_ad_rate_si' => 'required|numeric|min:0|max:999999.99',
        ]);

        $allValues = [
            'max_words_en' => (int) $request->input('max_words_en'),
            'max_words_si' => (int) $request->input('max_words_si'),
            'additional_word_rate_en' => (float) $request->input('additional_word_rate_en'),
            'additional_word_rate_si' => (float) $request->input('additional_word_rate_si'),
            'free_word_limit_en' => (int) $request->input('free_word_limit_en'),
            'free_word_limit_si' => (int) $request->input('free_word_limit_si'),
            'top_ad_rate_en' => (float) $request->input('top_ad_rate_en'),
            'top_ad_rate_si' => (float) $request->input('top_ad_rate_si'),
        ];

        $columnsToUpdate = collect(array_keys($allValues))
            ->filter(fn($column) => Schema::hasColumn('general_settings', $column))
            ->values();

        if ($columnsToUpdate->isEmpty()) {
            return redirect()->back()->with('error', 'No configurable columns were found in general_settings table.');
        }

        $data = $columnsToUpdate
            ->mapWithKeys(fn($column) => [$column => $allValues[$column]])
            ->all();

        $hasIdColumn = Schema::hasColumn('general_settings', 'id');
        $hasCreatedAtColumn = Schema::hasColumn('general_settings', 'created_at');
        $hasUpdatedAtColumn = Schema::hasColumn('general_settings', 'updated_at');

        DB::transaction(function () use ($data, $hasIdColumn, $hasCreatedAtColumn, $hasUpdatedAtColumn) {
            $query = DB::table('general_settings');
            if ($hasIdColumn) {
                $query->orderBy('id');
            }

            $existingRow = $query->first();

            if ($existingRow) {
                if ($hasUpdatedAtColumn) {
                    $data['updated_at'] = now();
                }

                if ($hasIdColumn && isset($existingRow->id)) {
                    DB::table('general_settings')
                        ->where('id', $existingRow->id)
                        ->update($data);
                    return;
                }

                DB::table('general_settings')->update($data);
                return;
            }

            if ($hasCreatedAtColumn) {
                $data['created_at'] = now();
            }

            if ($hasUpdatedAtColumn) {
                $data['updated_at'] = now();
            }

            DB::table('general_settings')->insert($data);
        });

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }


    /**
     * Show publication cutoff settings page for admins.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getPublicationDeadlines()
    {
        $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
        if (!in_array($currentRole, ['super admin', 'site admin'], true)) {
            abort(403);
        }

        $schemaMissing = !Schema::hasTable('publication_deadlines');
        $deadlines = $this->fetchPublicationDeadlines();
        $weekDays = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return view('publication_deadlines.index', compact('deadlines', 'weekDays', 'schemaMissing'));
    }

    /**
     * Update publication cutoff settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePublicationDeadlines(Request $request)
    {
        if (!Schema::hasTable('publication_deadlines')) {
            return redirect()->back()->with('error', 'Publication deadlines table is missing. Please run migrations first.');
        }

        $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
        if (!in_array($currentRole, ['super admin', 'site admin'], true)) {
            abort(403);
        }

        $request->validate([
            'hitad_cutoff_day_of_week' => 'required|integer|min:0|max:6',
            'hitad_cutoff_time' => 'required|date_format:H:i',
            'lahipita_cutoff_day_of_week' => 'required|integer|min:0|max:6',
            'lahipita_cutoff_time' => 'required|date_format:H:i',
        ]);

        DB::transaction(function () use ($request) {
            DB::table('publication_deadlines')->updateOrInsert(
                ['publication' => 'hitad_print'],
                [
                    'cutoff_day_of_week' => (int) $request->input('hitad_cutoff_day_of_week'),
                    'cutoff_time' => $request->input('hitad_cutoff_time') . ':00',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('publication_deadlines')->updateOrInsert(
                ['publication' => 'lahipita'],
                [
                    'cutoff_day_of_week' => (int) $request->input('lahipita_cutoff_day_of_week'),
                    'cutoff_time' => $request->input('lahipita_cutoff_time') . ':00',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });

        return redirect()->back()->with('success', 'Publication cutoffs updated successfully.');
    }

    /**
     * Fetch publication cutoff rules from DB, with defaults.
     *
     * @return array<string, array<string, mixed>>
     */
    private function fetchPublicationDeadlines(): array
    {
        $defaults = [
            'hitad_print' => [
                'publication' => 'hitad_print',
                'label' => 'HitAd',
                'cutoff_day_of_week' => 5,
                'cutoff_time' => '18:00:00',
            ],
            'lahipita' => [
                'publication' => 'lahipita',
                'label' => 'Lahipita',
                'cutoff_day_of_week' => 2,
                'cutoff_time' => '18:00:00',
            ],
        ];

        if (!Schema::hasTable('publication_deadlines')) {
            return $defaults;
        }

        $rows = DB::table('publication_deadlines')
            ->whereIn('publication', array_keys($defaults))
            ->get();

        foreach ($rows as $row) {
            $publication = (string) ($row->publication ?? '');
            if (!isset($defaults[$publication])) {
                continue;
            }

            $defaults[$publication]['cutoff_day_of_week'] = max(0, min(6, (int) ($row->cutoff_day_of_week ?? $defaults[$publication]['cutoff_day_of_week'])));
            $defaults[$publication]['cutoff_time'] = trim((string) ($row->cutoff_time ?? $defaults[$publication]['cutoff_time'])) !== ''
                ? (string) $row->cutoff_time
                : $defaults[$publication]['cutoff_time'];
        }

        return $defaults;
    }


    /**
     * Fetch general settings from DB, with defaults.
     *
     * @return array<string, int|float>
     */
    private function fetchGeneralSettings(): array
    {
        $defaults = [
            'max_words_en' => 65,
            'max_words_si' => 65,
            'additional_word_rate_en' => 20.00,
            'additional_word_rate_si' => 20.00,
            'free_word_limit_en' => 15,
            'free_word_limit_si' => 15,
            'top_ad_rate_en' => 100.00,
            'top_ad_rate_si' => 100.00,
        ];

        if (!Schema::hasTable('general_settings')) {
            return $defaults;
        }

        $query = DB::table('general_settings');
        if (Schema::hasColumn('general_settings', 'id')) {
            $query->orderBy('id');
        }

        $row = $query->first();
        if (!$row) {
            return $defaults;
        }

        foreach (array_keys($defaults) as $column) {
            if (!Schema::hasColumn('general_settings', $column)) {
                continue;
            }

            $value = data_get($row, $column);

            if ($value === null || $value === '') {
                continue;
            }

            $defaults[$column] = in_array($column, ['max_words_en', 'max_words_si', 'free_word_limit_en', 'free_word_limit_si'], true)
                ? (int) $value
                : (float) $value;
        }

        return $defaults;
    }

    /**
     * Validate that publish date is Sunday and before the configured publication cutoff.
     *
     * @param string $publication
     * @param string $value
     * @param callable $fail
     * @return void
     */
    private function validatePublicationPublishDate(string $publication, string $value, callable $fail): void
    {
        $publication = trim($publication);
        if (!in_array($publication, ['lahipita', 'hitad_print', 'hitad'], true)) {
            return;
        }

        $businessTimezone = 'Asia/Colombo';

        $publishDate = Carbon::parse($value, $businessTimezone)->startOfDay();
        if ($publishDate->dayOfWeek !== Carbon::SUNDAY) {
            $fail('The publish date must be a Sunday.');
            return;
        }

        $deadlines = $this->fetchPublicationDeadlines();
        $rule = $deadlines[$publication === 'hitad' ? 'hitad_print' : $publication] ?? null;
        if (!$rule) {
            return;
        }

        $cutoffDay = (int) ($rule['cutoff_day_of_week'] ?? 5);
        $daysBack = (Carbon::SUNDAY - $cutoffDay + 7) % 7;

        $cutoffDateTime = $publishDate
            ->copy()
            ->subDays($daysBack)
            ->setTimeFromTimeString((string) ($rule['cutoff_time'] ?? '18:00:00'));

        if (Carbon::now($businessTimezone)->greaterThanOrEqualTo($cutoffDateTime)) {
            $fail('The selected publish date is past the cutoff for ' . ($rule['label'] ?? $publication) . '.');
        }
    }

    /**
     * POST: Store a new advertisement along with customer, criteria values, images and optional payment record.
     * All writes are wrapped in a DB transaction. Uploaded images are stored to the configured disk.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    // POST: Store advertisement
    public function storeAdvertisement(Request $request)
    {
        $request->validate([
            'publication' => 'required|in:hitad_print,lahipita',
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'nic_passport' => 'required|string|max:255',
            'nic_front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'nic_back_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'email' => 'nullable|email|max:255',
            'confirm_email' => 'nullable|email|max:255|same:email',
            'advertisement_type_id' => 'required|exists:advertisement_types,id',
            'advertisement_size_id' => 'required|exists:advertisement_sizes,id',
            'advertisement_description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDescriptionWordLimit(
                        (string) $request->input('publication', 'hitad_print'),
                        (string) $value,
                        $fail
                    );
                },
            ],
            'category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'city_id' => 'nullable|exists:cities,id',
            'publish_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $this->validatePublicationPublishDate((string) request('publication'), (string) $value, $fail);
                },
            ],
            'web_combined_ad_hitadlk' => 'nullable|boolean',
            'top_ad' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'criteria' => 'nullable|array',
            'criteria_image' => 'nullable|array',
            'criteria_image.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'advertisement_tint_id' => 'nullable|integer|exists:advertisement_tints,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_amount'    => 'nullable|numeric|min:0',
            'payment_status'    => 'nullable|in:pending,completed,failed',
            'payment_date'      => 'nullable|date',
            'receipt_number'    => 'nullable|string|max:255',
            'payment_slip'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->filled('advertisement_tint_id')) {
            $isTintInCategory = DB::table('category_has_advertisement_tints')
                ->where('category_id', $request->category_id)
                ->where('advertisement_tint_id', $request->advertisement_tint_id)
                ->exists();

            if (!$isTintInCategory) {
                return redirect()->back()
                    ->withErrors(['advertisement_tint_id' => 'The selected tint is not valid for the selected category.'])
                    ->withInput();
            }

            if (Schema::hasColumn('advertisement_tints', 'advertisement_type_id')) {
                $isTintInType = DB::table('advertisement_tints')
                    ->where('id', $request->advertisement_tint_id)
                    ->where('advertisement_type_id', $request->advertisement_type_id)
                    ->exists();

                if (!$isTintInType) {
                    return redirect()->back()
                        ->withErrors(['advertisement_tint_id' => 'The selected tint is not valid for the selected type.'])
                        ->withInput();
                }
            }
        }

        DB::transaction(function () use ($request) {
            $customer = DB::table('customers')->where('nic_passport', $request->nic_passport)->first();

            $nicFrontImagePath = $customer->nic_front_img_url ?? null;
            if ($request->hasFile('nic_front_image')) {
                $storagePath = $request->file('nic_front_image')->storePublicly('customer-nic', 'oracle');
                $nicFrontImagePath = Storage::disk('oracle')->url($storagePath);
            }

            $nicBackImagePath = $customer->nic_back_img_url ?? null;
            if ($request->hasFile('nic_back_image')) {
                $storagePath = $request->file('nic_back_image')->storePublicly('customer-nic', 'oracle');
                $nicBackImagePath = Storage::disk('oracle')->url($storagePath);
            }

            $customerData = [
                'customer_name' => $request->customer_name,
                'address' => $request->address,
                'telephone' => $request->telephone,
                'nic_passport' => $request->nic_passport,
                'email' => $request->email,
                'nic_front_img_url' => $nicFrontImagePath,
                'nic_back_img_url' => $nicBackImagePath,
                'updated_at' => now(),
            ];

            if ($customer) {
                DB::table('customers')->where('id', $customer->id)->update($customerData);
                $customerId = $customer->id;
            } else {
                $customerId = DB::table('customers')->insertGetId($customerData + [
                    'email_verified' => !empty($request->email) ? 1 : 0,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $advertisementData = [
                'customer_id' => $customerId,
                'category_id' => $request->category_id,
                'district_id' => $request->district_id,
                'city_id' => $request->filled('city_id') ? $request->city_id : null,
                'advertisement_description' => $request->advertisement_description,
                'publish_date' => $request->publish_date,
                'publication' => $request->publication,
                'web_combined_ad_hitadlk' => $request->boolean('web_combined_ad_hitadlk'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('advertisements', 'advertisement_type_id')) {
                $advertisementData['advertisement_type_id'] = (int) $request->advertisement_type_id;
            }

            if (Schema::hasColumn('advertisements', 'advertisement_size_id')) {
                $advertisementData['advertisement_size_id'] = (int) $request->advertisement_size_id;
            }

            if (Schema::hasColumn('advertisements', 'advertisement_tint_id')) {
                $advertisementData['advertisement_tint_id'] = $request->advertisement_tint_id;
            }

            if (Schema::hasColumn('advertisements', 'top_ad')) {
                $advertisementData['top_ad'] = $request->boolean('top_ad');
            }

            $adId = DB::table('advertisements')->insertGetId($advertisementData);


            foreach ((array) $request->input('criteria', []) as $criteriaId => $criteriaValue) {
                if (is_array($criteriaValue)) {
                    $criteriaValue = implode(', ', array_filter($criteriaValue, static fn($item) => filled($item)));
                }

                $criteriaValue = is_string($criteriaValue) ? trim($criteriaValue) : $criteriaValue;

                if (!filled($criteriaValue)) {
                    continue;
                }

                DB::table('advertisement_criteria_values')->insert([
                    'advertisement_id' => $adId,
                    'advertisement_criteria_id' => $criteriaId,
                    'advertisement_criteria_option_value' => $criteriaValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ((array) $request->file('criteria_image', []) as $criteriaId => $criteriaImage) {
                if (!$criteriaImage) {
                    continue;
                }

                $criteriaImagePath = $criteriaImage->storePublicly('advertisement-criteria-images', 'oracle');

                DB::table('advertisement_criteria_values')->insert([
                    'advertisement_id' => $adId,
                    'advertisement_criteria_id' => $criteriaId,
                    'advertisement_criteria_option_value' => $criteriaImagePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if (!$image) {
                        continue;
                    }

                    $imagePath = $image->storePublicly('advertisements', 'oracle');

                    DB::table('advertisement_images')->insert([
                        'advertisement_id' => $adId,
                        'img_url' => $imagePath,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (
                $request->filled('payment_method_id')
                || $request->filled('payment_amount')
                || $request->filled('receipt_number')
                || $request->hasFile('payment_slip')
                || $request->filled('payment_status')
                || $request->filled('payment_date')
            ) {
                $priceBreakdown = $this->buildAdvertisementPriceBreakdown($request);
                $paymentAmount = $request->filled('payment_amount')
                    ? (float) $request->payment_amount
                    : (float) collect($priceBreakdown)->sum('amount');

                $paymentSlipPath = null;
                if ($request->hasFile('payment_slip')) {
                    $paymentSlipPath = $request->file('payment_slip')->storePublicly('payment_slips', 'oracle');
                }

                DB::table('payments')->insert([
                    'advertisement_id'      => $adId,
                    'payment_method_id'     => $request->payment_method_id ?: 1,
                    'amount'                => $paymentAmount,
                    'payment_status'        => $request->payment_status ?: 'pending',
                    'payment_date'          => $request->filled('payment_date') ? $request->payment_date : now()->toDateTimeString(),
                    'receipt_number'        => $request->receipt_number,
                    'payment_slip_file_path' => $paymentSlipPath,
                    'is_success'            => ($request->payment_status === 'completed') ? 'true' : 'false',
                    'session_id'            => '',
                    'success_indicator'     => '',
                    'result'                => '',
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }
        });

        return redirect('/advertisements')->with('success', 'Advertisement added successfully!');
    }

    /**
     * Build a payment breakdown for an advertisement using the selected type and size.
     * Labels follow the publication language when possible, and amounts come from the DB prices.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<int, array{label:string, amount:int|float}>
     */
    private function buildAdvertisementPriceBreakdown(Request $request): array
    {
        $publication = (string) $request->input('publication', 'hitad_print');
        $items = [];

        $typeId = $request->input('advertisement_type_id');
        if (!empty($typeId)) {
            $type = DB::table('advertisement_types')->where('id', $typeId)->first();

            if ($type) {
                $items[] = [
                    'label' => $this->resolveLocalizedAdvertisementLabel(
                        $type->advertisement_type_en ?? null,
                        $type->advertisement_type_si ?? null,
                        $publication
                    ),
                    'amount' => (float) ($type->price ?? 0),
                ];
            }
        }

        $sizeId = $request->input('advertisement_size_id');
        if (!empty($sizeId)) {
            $size = DB::table('advertisement_sizes')->where('id', $sizeId)->first();

            if ($size) {
                $items[] = [
                    'label' => $this->resolveLocalizedAdvertisementLabel(
                        $size->advertisement_size_en ?? null,
                        $size->advertisement_size_si ?? null,
                        $publication
                    ),
                    'amount' => (float) ($size->price ?? 0),
                ];
            }
        }

        $tintId = $request->input('advertisement_tint_id');
        if (!empty($tintId)) {
            $tint = DB::table('advertisement_tints')->where('id', $tintId)->first();

            if ($tint) {
                $tintLabel = $this->resolveLocalizedAdvertisementLabel(
                    $tint->advertisement_tint_en ?? null,
                    $tint->advertisement_tint_si ?? null,
                    $publication
                );
                $items[] = [
                    'label' => $tintLabel,
                    'amount' => (float) ($tint->price ?? 0),
                ];
            }
        }

        $description = (string) $request->input('advertisement_description', '');
        $pricingRules = $this->resolveDescriptionPricingSettings($publication);
        $wordCount = $this->countWords($description);
        $freeWords = max(0, (int) ($pricingRules['free_word_limit'] ?? 0));
        $additionalRate = max(0, (float) ($pricingRules['additional_word_rate'] ?? 0));
        $extraWords = max(0, $wordCount - $freeWords);

        if ($extraWords > 0 && $additionalRate > 0) {
            $items[] = [
                'label' => 'Additional words (' . $extraWords . ' ÃƒÆ’Ã‚Â¯Ãƒâ€šÃ‚Â¿Ãƒâ€šÃ‚Â½ ' . number_format($additionalRate, 2, '.', '') . ')',
                'amount' => round($extraWords * $additionalRate, 2),
            ];
        }

        if ($request->boolean('top_ad')) {
            $topAdRate = $this->resolveTopAdRate($publication);

            if ($topAdRate > 0) {
                $items[] = [
                    'label' => 'Top ad placement',
                    'amount' => $topAdRate,
                ];
            }
        }

        return $items;
    }

    /**
     * Choose an English or Sinhala label based on publication, falling back to whichever value exists.
     *
     * @param string|null $english
     * @param string|null $sinhala
     * @param string $publication
     * @return string
     */
    private function resolveLocalizedAdvertisementLabel(?string $english, ?string $sinhala, string $publication): string
    {
        $english = trim((string) $english);
        $sinhala = trim((string) $sinhala);

        if (trim($publication) === 'lahipita') {
            return $sinhala !== '' ? $sinhala : ($english !== '' ? $english : 'Advertisement item');
        }

        return $english !== '' ? $english : ($sinhala !== '' ? $sinhala : 'Advertisement item');
    }


    /**
     * Resolve general description pricing settings for a publication.
     *
     * @param string $publication
     * @return array{max_words:int, free_word_limit:int, additional_word_rate:float}
     */
    private function resolveDescriptionPricingSettings(string $publication): array
    {
        $settings = $this->fetchGeneralSettings();
        $isLahipita = trim($publication) === 'lahipita';
        $suffix = $isLahipita ? 'si' : 'en';

        return [
            'max_words' => (int) ($settings['max_words_' . $suffix] ?? 0),
            'free_word_limit' => (int) ($settings['free_word_limit_' . $suffix] ?? 0),
            'additional_word_rate' => (float) ($settings['additional_word_rate_' . $suffix] ?? 0),
        ];
    }

    /**
     * Resolve top-ad surcharge for a publication.
     *
     * @param string $publication
     * @return float
     */
    private function resolveTopAdRate(string $publication): float
    {
        $settings = $this->fetchGeneralSettings();
        $suffix = trim($publication) === 'lahipita' ? 'si' : 'en';

        return max(0, (float) ($settings['top_ad_rate_' . $suffix] ?? 0));
    }

    /**
     * Count words from free-form description text.
     *
     * @param string $text
     * @return int
     */
    private function countWords(string $text): int
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $text));
        if ($normalized === '') {
            return 0;
        }

        $parts = preg_split('/\s+/u', $normalized) ?: [];

        return count(array_filter($parts, static fn($part) => trim((string) $part) !== ''));
    }

    /**
     * Validate advertisement description word limit based on publication settings.
     *
     * @param string $publication
     * @param string $description
     * @param callable $fail
     * @return void
     */
    private function validateDescriptionWordLimit(string $publication, string $description, callable $fail): void
    {
        $rules = $this->resolveDescriptionPricingSettings($publication);
        $maxWords = (int) ($rules['max_words'] ?? 0);

        if ($maxWords <= 0) {
            return;
        }

        $wordCount = $this->countWords($description);
        if ($wordCount > $maxWords) {
            $fail('Description cannot exceed ' . $maxWords . ' words for the selected publication.');
        }
    }

    /**
     * POST: Calculate advertisement price based on form selections.
     * Returns breakdown items and total amount as JSON.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateAdvertisementPrice(Request $request)
    {
        $breakdown = $this->buildAdvertisementPriceBreakdown($request);
        $total = collect($breakdown)->sum('amount');

        return response()->json([
            'items' => $breakdown,
            'total' => round($total, 2),
        ]);
    }

    /**
     * GET: Show all advertisements with optional filters and pagination.
     * This aggregates customers, categories, districts, cities and payment info.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    // GET: Show all advertisements
    public function getAllPrintAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->leftJoin(
                'admins',
                'advertisements.approved_by_admin_id',
                '=',
                'admins.id'
            )
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('categories.is_active', 1)
            ->where('districts.is_active', 1)
            ->where('cities.is_active', 1)
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'admins.admin_name as approved_admin_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
                DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),
                'payments.payment_status',
                DB::raw("CASE
                    WHEN advertisements.publication = 'hitad_print' THEN 'Hitad Print'
                    WHEN advertisements.publication = 'lahipita' THEN 'Lahipita Print'
                    ELSE advertisements.publication
                END as publication_label")
            );

        // free-text search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
                    ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
            });
        }

        // advanced filters
        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function ($q) use ($cat) {
                $q->where('categories.category_name_en', 'LIKE', "%{$cat}%")
                    ->orWhere('categories.category_name_si', 'LIKE', "%{$cat}%");
            });
        }

        if ($request->filled('publish_date')) {
            $query->whereDate('advertisements.publish_date', $request->publish_date);
        }

        if ($request->filled('customer_name')) {
            $query->where('customers.customer_name', 'LIKE', "%{$request->customer_name}%");
        }

        if ($request->filled('phone')) {
            $query->where('customers.telephone', 'LIKE', "%{$request->phone}%");
        }

        if ($request->filled('email')) {
            $query->where('customers.email', 'LIKE', "%{$request->email}%");
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(30);
        $ads->appends($request->only(['search', 'category', 'publish_date', 'customer_name', 'phone', 'email']));

        return view('advertisements.all', compact('ads'));
    }

    /**
     * GET: List advertisements for 'hitad_print' publication with filters and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->leftJoin(
                'admins',
                'advertisements.approved_by_admin_id',
                '=',
                'admins.id'
            )
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ PAYMENTS
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('categories.is_active', 1)
            ->where('districts.is_active', 1)
            ->where('cities.is_active', 1)

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'admins.admin_name as approved_admin_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
                DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),

                'payments.payment_status',
            )

            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â¥ IMPORTANT FILTER (THIS IS WHAT YOU WANT)
            ->where('advertisements.publication', 'hitad_print');

        // free-text search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
                    ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
            });
        }

        // advanced filters
        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function ($q) use ($cat) {
                $q->where('categories.category_name_en', 'LIKE', "%{$cat}%")
                    ->orWhere('categories.category_name_si', 'LIKE', "%{$cat}%");
            });
        }

        if ($request->filled('publish_date')) {
            $query->whereDate('advertisements.publish_date', $request->publish_date);
        }

        if ($request->filled('customer_name')) {
            $query->where('customers.customer_name', 'LIKE', "%{$request->customer_name}%");
        }

        if ($request->filled('phone')) {
            $query->where('customers.telephone', 'LIKE', "%{$request->phone}%");
        }

        if ($request->filled('email')) {
            $query->where('customers.email', 'LIKE', "%{$request->email}%");
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(30);
        $ads->appends($request->only(['search', 'category', 'publish_date', 'customer_name', 'phone', 'email']));

        return view('advertisements.index', compact('ads'));
    }



    /**
     * GET: View a single advertisement by id including customer, location and payment details.
     * Also loads category-specific criterias and existing criteria values for display.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    // GET: View single advertisement
    public function viewAdvertisement($id, $approvedReadOnly = false)
{
    $ad = DB::table('advertisements')
        ->where('advertisements.id', $id)

        ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
        ->join('categories', 'advertisements.category_id', '=', 'categories.id')
        ->join('districts', 'advertisements.district_id', '=', 'districts.id')
        ->join('cities', 'advertisements.city_id', '=', 'cities.id')
        ->leftJoin('advertisement_tints', 'advertisements.advertisement_tint_id', '=', 'advertisement_tints.id')
        ->leftJoin('advertisement_types', 'advertisements.advertisement_type_id', '=', 'advertisement_types.id')
        ->leftJoin('advertisement_sizes', 'advertisements.advertisement_size_id', '=', 'advertisement_sizes.id')
         ->where('categories.is_active', 1)
        ->where('districts.is_active', 1)
        ->where('cities.is_active', 1)

        ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
        ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

        ->select(
            'advertisements.*',

            'customers.customer_name',
            'customers.address',
            'customers.telephone',
            'customers.email',
            'customers.nic_passport',
            'customers.nic_front_img_url',
            'customers.nic_back_img_url',

            DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
            DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
            DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),
            DB::raw("COALESCE(advertisement_tints.advertisement_tint_en, advertisement_tints.advertisement_tint_si) as advertisement_tint_name"),
            DB::raw('COALESCE(advertisement_types.advertisement_type_en, advertisement_types.advertisement_type_si) as advertisement_type_name'),
            DB::raw('COALESCE(advertisement_sizes.advertisement_size_en, advertisement_sizes.advertisement_size_si) as advertisement_size_name'),

            'payments.amount',
            'payments.payment_status',
            'payments.payment_date',

            'payment_methods.payment_method_name as payment_method'
        )
        ->first();

    if (!$ad) {
        abort(404);
    }

    $criterias = DB::table('advertisement_criterias')
        ->where('category_id', $ad->category_id)
        ->where('is_active', 1)
        ->get();

    $criteriaIdsFromCategory = $criterias->pluck('id')->filter()->values();
    $criteriaIdsFromValues = DB::table('advertisement_criteria_values')
        ->where('advertisement_id', $id)
        ->pluck('advertisement_criteria_id')
        ->filter()
        ->values();

    $criteriaIds = $criteriaIdsFromCategory->merge($criteriaIdsFromValues)->unique()->values();

    $criteriaOptions = DB::table('advertisement_criteria_options')
        ->whereIn('advertisement_criteria_id', $criteriaIds)
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

    $currentOptionLabelsByCriteriaId = [];
    $currentOptions = DB::table('advertisement_criteria_options')
        ->whereIn('advertisement_criteria_id', $criterias->pluck('id'))
        ->where('is_active', 1)
        ->get();

    foreach ($currentOptions as $option) {
        $label = trim((string) ($option->advertisement_criteria_option_name_en ?? $option->advertisement_criteria_option_name_si ?? ''));
        if ($label === '') {
            continue;
        }

        $currentOptionLabelsByCriteriaId[(int) $option->advertisement_criteria_id][strtolower($label)] = $label;
    }

    foreach ($criteriaValues as $criteriaId => $value) {
        $trimmedValue = trim((string) $value);
        if ($trimmedValue === '') {
            continue;
        }

        foreach ($criterias as $criteria) {
            if (isset($criteriaValues[$criteria->id]) && $criteriaValues[$criteria->id] !== null) {
                continue;
            }

            $normalized = strtolower($trimmedValue);
            if (isset($currentOptionLabelsByCriteriaId[(int) $criteria->id][$normalized])) {
                $criteriaValues[$criteria->id] = $currentOptionLabelsByCriteriaId[(int) $criteria->id][$normalized];
            }
        }
    }

    $images = DB::table('advertisement_images')
        ->where('advertisement_id', $id)
        ->where('is_active', 1)
        ->orderBy('id', 'asc')
        ->get()
        ->map(function ($image) {
            $image->display_url = $this->resolveAdvertisementImageDisplayUrl($image->img_url ?? null);
            return $image;
        });

    return view('advertisements.view', compact('ad','criterias','criteriaOptions','criteriaValues','images','approvedReadOnly'));
    }

    /**
     * List hitad_print advertisements that have completed payments (paid).
     * Supports search and filter parameters and returns a paginated view.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getPaidAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
             ->leftJoin(
                'admins',
                'advertisements.approved_by_admin_id',
                '=',
                'admins.id'
            )
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->join('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('categories.is_active', 1)
            ->where('districts.is_active', 1)
            ->where('cities.is_active', 1)

            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ONLY HITAD PRINT ADS
            ->where('advertisements.publication', 'hitad_print')

            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ONLY PAID
            ->where('payments.payment_status', 'completed')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'admins.admin_name as approved_admin_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
                DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payment_methods.payment_method_name as payment_method'
            );

        // apply filters
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
                    ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function ($q) use ($cat) {
                $q->where('categories.category_name_en', 'LIKE', "%{$cat}%")
                    ->orWhere('categories.category_name_si', 'LIKE', "%{$cat}%");
            });
        }

        if ($request->filled('publish_date')) {
            $query->whereDate('advertisements.publish_date', $request->publish_date);
        }

        if ($request->filled('customer_name')) {
            $query->where('customers.customer_name', 'LIKE', "%{$request->customer_name}%");
        }

        if ($request->filled('phone')) {
            $query->where('customers.telephone', 'LIKE', "%{$request->phone}%");
        }

        if ($request->filled('email')) {
            $query->where('customers.email', 'LIKE', "%{$request->email}%");
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(30);
        $ads->appends($request->only(['search', 'category', 'publish_date', 'customer_name', 'phone', 'email']));

        return view('advertisements.paid', compact('ads'));
    }
    /**
     * List hitad_print advertisements that are unpaid (no payment record or pending/failed).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    // public function getUnpaidAdvertisements(Request $request)
    // {
    //     $query = DB::table('advertisements')

    //         ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
    //         ->join('categories', 'advertisements.category_id', '=', 'categories.id')
    //         ->join('districts', 'advertisements.district_id', '=', 'districts.id')
    //         ->join('cities', 'advertisements.city_id', '=', 'cities.id')

    //         ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
    //         ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

    //         // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ONLY HITAD PRINT ADS
    //         ->where('advertisements.publication', 'hitad_print')

    //         // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ UNPAID LOGIC
    //         ->where(function ($q) {
    //             $q->whereNull('payments.id') // no payment
    //                 ->orWhere('payments.payment_status', 'pending') // pending
    //                 ->orWhere('payments.payment_status', 'failed'); // failed
    //         })

    //         ->select(
    //             'advertisements.*',
    //             'customers.customer_name',
    //             DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
    //             DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
    //             DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),

    //             'payments.amount',
    //             'payments.payment_date',
    //             'payments.payment_status',
    //             'payment_methods.payment_method_name as payment_method'
    //         );

    //     // apply filters
    //     if ($request->has('search') && !empty($request->search)) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
    //                 ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
    //         });
    //     }

    //     if ($request->filled('category')) {
    //         $cat = $request->category;
    //         $query->where(function ($q) use ($cat) {
    //             $q->where('categories.category_name_en', 'LIKE', "%{$cat}%")
    //                 ->orWhere('categories.category_name_si', 'LIKE', "%{$cat}%");
    //         });
    //     }

    //     if ($request->filled('publish_date')) {
    //         $query->whereDate('advertisements.publish_date', $request->publish_date);
    //     }

    //     if ($request->filled('customer_name')) {
    //         $query->where('customers.customer_name', 'LIKE', "%{$request->customer_name}%");
    //     }

    //     if ($request->filled('phone')) {
    //         $query->where('customers.telephone', 'LIKE', "%{$request->phone}%");
    //     }

    //     if ($request->filled('email')) {
    //         $query->where('customers.email', 'LIKE', "%{$request->email}%");
    //     }

    //     $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
    //     $ads->appends($request->only(['search', 'category', 'publish_date', 'customer_name', 'phone', 'email']));

    //     return view('advertisements.unpaid', compact('ads'));
    // }
    /**
     * List all 'lahipita' advertisements (Sinhala publication) with search and pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getLahipitaAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->leftJoin(
                'admins',
                'advertisements.approved_by_admin_id',
                '=',
                'admins.id'
            )
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->where('categories.is_active', 1)
            ->where('districts.is_active', 1)
            ->where('cities.is_active', 1)

            ->select(
                'advertisements.*',
                'customers.customer_name',
                'admins.admin_name as approved_admin_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                'districts.district_name as district_name',
                'cities.city_name as city_name',

                'payments.payment_status',
            )

            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ MAIN FILTER
            ->where('advertisements.publication', 'lahipita');

        // ÃƒÆ’Ã‚Â°Ãƒâ€¦Ã‚Â¸ÃƒÂ¢Ã¢â€šÂ¬Ã‚ÂÃƒâ€šÃ‚Â search (same as your existing)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
                    ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
            });
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(30);
        $ads->appends($request->only('search'));

        return view('advertisements.lahipita_all', compact('ads'))
            ->with('search', $request->search);
    }
    /**
     * List 'lahipita' advertisements with completed payments.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getLahipitaPaidAdvertisements(Request $request)
{
    $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->leftJoin(
                'admins',
                'advertisements.approved_by_admin_id',
                '=',
                'admins.id'
            )
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

        ->join('payments', 'advertisements.id', '=', 'payments.advertisement_id')

        ->where('categories.is_active', 1)
        ->where('districts.is_active', 1)
        ->where('cities.is_active', 1)

        // ONLY LAHIPITA ADS
        ->where('advertisements.publication', 'lahipita')

        // ONLY PAID
        ->where('payments.payment_status', 'completed')

        ->select(
            'advertisements.*',
            'customers.customer_name',
            'admins.admin_name as approved_admin_name',
            DB::raw(
                'COALESCE(categories.category_name_en, categories.category_name_si) as category_name'
            ),

            'districts.district_name as district_name',
            'cities.city_name as city_name',

            'payments.amount',
            'payments.payment_date',
            'payments.payment_status'
        );

    // SEARCH
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where(
                'advertisements.advertisement_description',
                'LIKE',
                "%{$search}%"
            )
            ->orWhere(
                'customers.customer_name',
                'LIKE',
                "%{$search}%"
            );
        });
    }

    // CATEGORY
    if ($request->filled('category')) {
        $cat = $request->category;

        $query->where(function ($q) use ($cat) {
            $q->where(
                'categories.category_name_en',
                'LIKE',
                "%{$cat}%"
            )
            ->orWhere(
                'categories.category_name_si',
                'LIKE',
                "%{$cat}%"
            );
        });
    }

    // PUBLISH DATE
    if ($request->filled('publish_date')) {
        $query->whereDate(
            'advertisements.publish_date',
            $request->publish_date
        );
    }

    // CUSTOMER
    if ($request->filled('customer_name')) {
        $query->where(
            'customers.customer_name',
            'LIKE',
            "%{$request->customer_name}%"
        );
    }

    // PHONE
    if ($request->filled('phone')) {
        $query->where(
            'customers.telephone',
            'LIKE',
            "%{$request->phone}%"
        );
    }

    // EMAIL
    if ($request->filled('email')) {
        $query->where(
            'customers.email',
            'LIKE',
            "%{$request->email}%"
        );
    }

    $ads = $query
        ->orderBy('advertisements.id', 'desc')
        ->paginate(30);

    $ads->appends(
        $request->only([
            'search',
            'category',
            'publish_date',
            'customer_name',
            'phone',
            'email'
        ])
    );

    return view('advertisements.lahipita_paid', compact('ads'));
}


    /**
     * List 'lahipita' advertisements that are unpaid (no payment or pending/failed).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    // public function getLahipitaUnpaidAdvertisements(Request $request)
    // {
    //     $query = DB::table('advertisements')

    //         ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
    //         ->join('categories', 'advertisements.category_id', '=', 'categories.id')
    //         ->join('districts', 'advertisements.district_id', '=', 'districts.id')
    //         ->join('cities', 'advertisements.city_id', '=', 'cities.id')

    //         ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
    //         ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

    //         // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ ONLY LAHIPITA ADS
    //         ->where('advertisements.publication', 'lahipita')

    //         // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ UNPAID LOGIC (IMPORTANT)
    //         ->where(function ($q) {
    //             $q->whereNull('payments.id')
    //                 ->orWhere('payments.payment_status', 'pending')
    //                 ->orWhere('payments.payment_status', 'failed');
    //         })

    //         ->select(
    //             'advertisements.*',
    //             'customers.customer_name',
    //             DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
    //             'districts.district_name as district_name',
    //             'cities.city_name as city_name',

    //             'payments.amount',
    //             'payments.payment_date',
    //             'payments.payment_status',
    //             'payment_methods.payment_method_name as payment_method'
    //         );

    //     // apply filters
    //     if ($request->has('search') && !empty($request->search)) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('advertisements.advertisement_description', 'LIKE', "%{$search}%")
    //                 ->orWhere('customers.customer_name', 'LIKE', "%{$search}%");
    //         });
    //     }

    //     if ($request->filled('category')) {
    //         $cat = $request->category;
    //         $query->where(function ($q) use ($cat) {
    //             $q->where('categories.category_name_en', 'LIKE', "%{$cat}%")
    //                 ->orWhere('categories.category_name_si', 'LIKE', "%{$cat}%");
    //         });
    //     }

    //     if ($request->filled('publish_date')) {
    //         $query->whereDate('advertisements.publish_date', $request->publish_date);
    //     }

    //     if ($request->filled('customer_name')) {
    //         $query->where('customers.customer_name', 'LIKE', "%{$request->customer_name}%");
    //     }

    //     if ($request->filled('phone')) {
    //         $query->where('customers.telephone', 'LIKE', "%{$request->phone}%");
    //     }

    //     if ($request->filled('email')) {
    //         $query->where('customers.email', 'LIKE', "%{$request->email}%");
    //     }

    //     $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
    //     $ads->appends($request->only(['search', 'category', 'publish_date', 'customer_name', 'phone', 'email']));

    //     return view('advertisements.lahipita_unpaid', compact('ads'));
    // }




    /**
 * Get approved Hitad advertisements.
 */
public function getHitadApprovedAdvertisements(Request $request)
{
    $ads = DB::table('advertisements')
        ->join(
            'customers',
            'advertisements.customer_id',
            '=',
            'customers.id'
        )
        ->join(
            'categories',
            'advertisements.category_id',
            '=',
            'categories.id'
        )
        ->leftJoin(
            'admins as viewer_admin',
            'advertisements.viewed_by_admin_id',
            '=',
            'viewer_admin.id'
        )

        // Only Hitad
        ->where('advertisements.publication', 'hitad_print')

        // Only approved advertisements
        ->whereNotNull('advertisements.approved_by_admin_id')
        ->whereNotNull('advertisements.approved_at')

        ->select(
            'advertisements.id',
            'advertisements.publish_date',
            'advertisements.publication',
            'advertisements.viewed_by_admin_id',
            'advertisements.viewed_at',

            'customers.customer_name',

            DB::raw(
                'COALESCE(categories.category_name_en, categories.category_name_si) as category_name'
            ),

            'viewer_admin.admin_name as viewed_admin_name'
        )
        ->orderByDesc('advertisements.id')
        ->paginate(30);

    $pageTitle = 'Hitad - Approved Advertisements';
    $publication = 'hitad_print';

    return view(
        'advertisements.approved',
        compact('ads', 'pageTitle', 'publication')
    );
}


/**
 * Get approved Lahipita advertisements.
 */
public function getLahipitaApprovedAdvertisements(Request $request)
{
    $ads = DB::table('advertisements')
        ->join(
            'customers',
            'advertisements.customer_id',
            '=',
            'customers.id'
        )
        ->join(
            'categories',
            'advertisements.category_id',
            '=',
            'categories.id'
        )
        ->leftJoin(
            'admins as viewer_admin',
            'advertisements.viewed_by_admin_id',
            '=',
            'viewer_admin.id'
        )

        // Only Lahipita
        ->where('advertisements.publication', 'lahipita')

        // Only approved advertisements
        ->whereNotNull('advertisements.approved_by_admin_id')
        ->whereNotNull('advertisements.approved_at')

        ->select(
            'advertisements.id',
            'advertisements.publish_date',
            'advertisements.publication',
            'advertisements.viewed_by_admin_id',
            'advertisements.viewed_at',

            'customers.customer_name',

            DB::raw(
                'COALESCE(categories.category_name_si, categories.category_name_en) as category_name'
            ),

            'viewer_admin.admin_name as viewed_admin_name'
        )
        ->orderByDesc('advertisements.id')
        ->paginate(30);

    $pageTitle = 'Lahipita - Approved Advertisements';
    $publication = 'lahipita';

    return view(
        'advertisements.approved',
        compact('ads', 'pageTitle', 'publication')
    );
}


/**
 * Hitad approved advertisement - view once.
 */
public function viewHitadApprovedAdvertisementOnce($id)
{
    return $this->viewApprovedAdvertisementOnce($id, 'hitad_print');
}


/**
 * Lahipita approved advertisement - view once.
 */
public function viewLahipitaApprovedAdvertisementOnce($id)
{
    return $this->viewApprovedAdvertisementOnce($id, 'lahipita');
}


/**
 * Save who viewed the approved advertisement.
 */
private function viewApprovedAdvertisementOnce($id, string $publication)
{
    $adminId = data_get(session('user'), 'id');

    if (!$adminId) {
        return redirect('/login')
            ->with('error', 'Please login first.');
    }

    /*
     * Important:
     * whereNull(viewed_by_admin_id) makes sure the advertisement
     * can only be marked as viewed ONE TIME.
     */
    $updated = DB::table('advertisements')
        ->where('id', $id)
        ->where('publication', $publication)
        ->whereNotNull('approved_by_admin_id')
        ->whereNotNull('approved_at')
        ->whereNull('viewed_by_admin_id')
        ->update([
            'viewed_by_admin_id' => $adminId,
            'viewed_at' => now(),
            'updated_at' => now(),
        ]);

    /*
     * If nothing was updated, either:
     * - advertisement does not exist
     * - advertisement is not approved
     * - advertisement was already viewed
     */
    if ($updated === 0) {

        $ad = DB::table('advertisements')
            ->where('id', $id)
            ->where('publication', $publication)
            ->first();

        if (!$ad) {
            abort(404);
        }

        if (
            empty($ad->approved_by_admin_id) ||
            empty($ad->approved_at)
        ) {
            return redirect()->back()
                ->with('error', 'This advertisement is not approved.');
        }

        if (!empty($ad->viewed_by_admin_id)) {
            return redirect()->back()
                ->with('error', 'This advertisement has already been viewed.');
        }

        return redirect()->back()
            ->with('error', 'Unable to view advertisement.');
    }

    /*
     * Show advertisement as read-only.
     */
    return $this->viewAdvertisement($id, true);
}






    /**
     * Reports page: build monthly report sections for both publications and payment groups.
     * Accepts `month` input in Y-m format.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function reports(Request $request)
    {
        $monthInput = $request->input('month', now()->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
            $monthInput = $month->format('Y-m');
        }

        $reportSections = [
            'hitad_paid' => $this->buildMonthlyAdvertisementReport('hitad_print', 'paid', $month),
            'hitad_unpaid' => $this->buildMonthlyAdvertisementReport('hitad_print', 'unpaid', $month),
            'lahipita_paid' => $this->buildMonthlyAdvertisementReport('lahipita', 'paid', $month),
            'lahipita_unpaid' => $this->buildMonthlyAdvertisementReport('lahipita', 'unpaid', $month),
        ];

        $webCombinedReportSections = [
            'hitad_paid' => $this->buildWebCombinedAdvertisementReport('hitad_print', 'paid', $month),
            'hitad_unpaid' => $this->buildWebCombinedAdvertisementReport('hitad_print', 'unpaid', $month),
            'lahipita_paid' => $this->buildWebCombinedAdvertisementReport('lahipita', 'paid', $month),
            'lahipita_unpaid' => $this->buildWebCombinedAdvertisementReport('lahipita', 'unpaid', $month),
        ];

        $monthLabel = $month->format('F Y');

        return view('reports', compact('monthInput', 'monthLabel', 'reportSections', 'webCombinedReportSections'));
    }

    /**
     * Download a monthly report PDF for the requested type (e.g. hitad-paid, lahipita-unpaid).
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadMonthlyReport(Request $request, string $type)
    {
        $config = $this->getMonthlyReportConfig($type);
        abort_unless($config, 404);

        $monthInput = $request->input('month', now()->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
            $monthInput = $month->format('Y-m');
        }

        $report = $this->buildMonthlyAdvertisementReport($config['publication'], $config['group'], $month);
        $filename = sprintf('%s_%s_report.pdf', $config['slug'], $month->format('Y_m'));

        return Pdf::loadView('reports.pdf', [
            'title' => $config['title'],
            'monthLabel' => $month->format('F Y'),
            'monthInput' => $monthInput,
            'report' => $report,
        ])->download($filename);
    }

    
    public function downloadWebCombinedReport(Request $request, string $type)
    {
        $config = $this->getWebCombinedReportConfig($type);
        abort_unless($config, 404);

        $monthInput = $request->input('month', now()->format('Y-m'));

        try {
            $month = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Throwable $e) {
            $month = now()->startOfMonth();
            $monthInput = $month->format('Y-m');
        }

        $report = $this->buildWebCombinedAdvertisementReport($config['publication'], $config['group'], $month);
        $filename = sprintf('web_combined_%s_%s_report.pdf', $config['slug'], $month->format('Y_m'));

        return Pdf::loadView('reports.pdf', [
            'title' => $config['title'],
            'monthLabel' => $month->format('F Y'),
            'monthInput' => $monthInput,
            'report' => $report,
        ])->download($filename);
    }

    /**
     * Map report type slug to internal report configuration (slug, title, publication, group).
     *
     * @param string $type
     * @return array|null
     */
    private function getMonthlyReportConfig(string $type): ?array
    {
        // normalize type (accept both hyphen and underscore forms)
        $type = str_replace('_', '-', $type);

        $configs = [
            'hitad-paid' => [
                'slug' => 'hitad_paid',
                'title' => 'Hitad Paid Report',
                'publication' => 'hitad_print',
                'group' => 'paid',
            ],
            'hitad-unpaid' => [
                'slug' => 'hitad_unpaid',
                'title' => 'Hitad Unpaid Report',
                'publication' => 'hitad_print',
                'group' => 'unpaid',
            ],
            'lahipita-paid' => [
                'slug' => 'lahipita_paid',
                'title' => 'Lahipita Paid Report',
                'publication' => 'lahipita',
                'group' => 'paid',
            ],
            'lahipita-unpaid' => [
                'slug' => 'lahipita_unpaid',
                'title' => 'Lahipita Unpaid Report',
                'publication' => 'lahipita',
                'group' => 'unpaid',
            ],
        ];

        return $configs[$type] ?? null;
    }

    private function getWebCombinedReportConfig(string $type): ?array
    {
        $type = str_replace('_', '-', $type);

        $configs = [
            'web-combined-hitad-paid' => [
                'slug' => 'web_combined_hitad_paid',
                'title' => 'Web Combined Hitad Paid Report',
                'publication' => 'hitad_print',
                'group' => 'paid',
            ],
            'web-combined-hitad-unpaid' => [
                'slug' => 'web_combined_hitad_unpaid',
                'title' => 'Web Combined Hitad Unpaid Report',
                'publication' => 'hitad_print',
                'group' => 'unpaid',
            ],
            'web-combined-lahipita-paid' => [
                'slug' => 'web_combined_lahipita_paid',
                'title' => 'Web Combined Lahipita Paid Report',
                'publication' => 'lahipita',
                'group' => 'paid',
            ],
            'web-combined-lahipita-unpaid' => [
                'slug' => 'web_combined_lahipita_unpaid',
                'title' => 'Web Combined Lahipita Unpaid Report',
                'publication' => 'lahipita',
                'group' => 'unpaid',
            ],
        ];

        return $configs[$type] ?? null;
    }

    /**
     * Build the monthly advertisement report for a given publication and payment group (paid/unpaid).
     * Returns an array with 'count' and 'ads' collection.
     *
     * @param string $publication
     * @param string $paymentGroup
     * @param \Illuminate\Support\Carbon $month
     * @return array
     */
    private function buildMonthlyAdvertisementReport(string $publication, string $paymentGroup, Carbon $month): array
    {
        $query = DB::table('advertisements')
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('categories.is_active', 1)
            ->where('districts.is_active', 1)
            ->where('cities.is_active', 1)
            ->where('advertisements.publication', $publication)
            ->whereBetween('advertisements.publish_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->select(
                'advertisements.id',
                'advertisements.advertisement_description',
                'advertisements.publish_date',
                'advertisements.publication',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
                DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),
                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payment_methods.payment_method_name as payment_method'
            );

        if ($paymentGroup === 'paid') {
            $query->where('payments.payment_status', 'completed');
        } else {
            $query->where(function ($q) {
                $q->whereNull('payments.id')
                    ->orWhere('payments.payment_status', 'pending')
                    ->orWhere('payments.payment_status', 'failed');
            });
        }

        $ads = $query->orderBy('advertisements.id', 'desc')->get();
        $totalAmount = (float) $ads->sum(function ($ad) {
            return is_numeric($ad->amount) ? (float) $ad->amount : 0.0;
        });

        return [
            'count' => $ads->count(),
            'total_amount' => $totalAmount,
            'ads' => $ads,
        ];
    }

    private function buildWebCombinedAdvertisementReport(string $publication, string $paymentGroup, Carbon $month): array
    {
        $query = DB::table('advertisements')
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->leftJoin('cities', 'advertisements.city_id', '=', 'cities.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('categories.is_active', 1)
            ->where('districts.is_active', 1)
            ->where(function ($q) {
                $q->whereNull('cities.id')->orWhere('cities.is_active', 1);
            })
            ->where('advertisements.publication', $publication)
            ->where('advertisements.web_combined_ad_hitadlk', 1)
            ->whereBetween('advertisements.publish_date', [
                $month->copy()->startOfMonth()->toDateString(),
                $month->copy()->endOfMonth()->toDateString(),
            ])
            ->select(
                'advertisements.id',
                'advertisements.publication',
                'advertisements.advertisement_description',
                'advertisements.publish_date',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name, districts.district_name) as district_name'),
                DB::raw('COALESCE(cities.city_name, cities.city_name) as city_name'),
                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payment_methods.payment_method_name as payment_method'
            )
            ->orderBy('advertisements.id', 'desc');

        if ($paymentGroup === 'paid') {
            $query->where('payments.payment_status', 'completed');
        } else {
            $query->where(function ($q) {
                $q->whereNull('payments.id')
                    ->orWhere('payments.payment_status', 'pending')
                    ->orWhere('payments.payment_status', 'failed');
            });
        }

        $ads = $query->get();
        $totalAmount = (float) $ads->sum(function ($ad) {
            return is_numeric($ad->amount) ? (float) $ad->amount : 0.0;
        });

        return [
            'count' => $ads->count(),
            'total_amount' => $totalAmount,
            'ads' => $ads,
        ];
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
    ->leftJoin('advertisement_types', 'advertisements.advertisement_type_id', '=', 'advertisement_types.id')
    ->leftJoin('advertisement_sizes', 'advertisements.advertisement_size_id', '=', 'advertisement_sizes.id')
    ->leftJoin('advertisement_tints', 'advertisements.advertisement_tint_id', '=', 'advertisement_tints.id')
    ->where('categories.is_active', 1)
    ->where('districts.is_active', 1)
    ->where('cities.is_active', 1)
    ->select(
        'advertisements.*',
        'customers.customer_name',
        'customers.address',
        'customers.telephone',
        'customers.email',
        'customers.nic_passport',
        DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
        'districts.district_name as district_name',
        'cities.city_name as city_name',
        'payments.amount',
        'payments.payment_status',
        'payments.payment_date',
        'payment_methods.payment_method_name as payment_method',
        DB::raw('COALESCE(advertisement_types.advertisement_type_en, advertisement_types.advertisement_type_si) as advertisement_type_name'),
        DB::raw('COALESCE(advertisement_sizes.advertisement_size_en, advertisement_sizes.advertisement_size_si) as advertisement_size_name'),
        DB::raw('COALESCE(advertisement_tints.advertisement_tint_en, advertisement_tints.advertisement_tint_si) as advertisement_tint_name')
    )
    ->first();

        if (!$ad) {
            abort(404);
        }

        $criterias = DB::table('advertisement_criterias')
            ->where('category_id', $ad->category_id)
            ->where('is_active', 1)
            ->get();

        $criteriaIdsFromCategory = $criterias->pluck('id')->filter()->values();
        $criteriaIdsFromValues = DB::table('advertisement_criteria_values')
            ->where('advertisement_id', $id)
            ->pluck('advertisement_criteria_id')
            ->filter()
            ->values();
        $criteriaIds = $criteriaIdsFromCategory->merge($criteriaIdsFromValues)->unique()->values();

        $criteriaValuesRaw = DB::table('advertisement_criteria_values')
            ->where('advertisement_id', $id)
            ->get();

        $criteriaValues = [];
        foreach ($criteriaValuesRaw as $cv) {
            $criteriaValues[$cv->advertisement_criteria_id] = $cv->advertisement_criteria_option_value;
        }

        $currentOptionLabelsByCriteriaId = [];
        $currentOptions = DB::table('advertisement_criteria_options')
            ->whereIn('advertisement_criteria_id', $criterias->pluck('id'))
            ->where('is_active', 1)
            ->get();

        foreach ($currentOptions as $option) {
            $label = trim((string) ($option->advertisement_criteria_option_name_en ?? $option->advertisement_criteria_option_name_si ?? ''));
            if ($label === '') {
                continue;
            }

            $currentOptionLabelsByCriteriaId[(int) $option->advertisement_criteria_id][strtolower($label)] = $label;
        }

        foreach ($criteriaValues as $criteriaId => $value) {
            $trimmedValue = trim((string) $value);
            if ($trimmedValue === '') {
                continue;
            }

            foreach ($criterias as $criteria) {
                if (isset($criteriaValues[$criteria->id]) && $criteriaValues[$criteria->id] !== null) {
                    continue;
                }

                $normalized = strtolower($trimmedValue);
                if (isset($currentOptionLabelsByCriteriaId[(int) $criteria->id][$normalized])) {
                    $criteriaValues[$criteria->id] = $currentOptionLabelsByCriteriaId[(int) $criteria->id][$normalized];
                }
            }
        }

        $images = DB::table('advertisement_images')
            ->where('advertisement_id', $id)
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        $html = view('advertisements.pdf', compact('ad', 'criterias', 'criteriaValues'))->render();

           $pdfBytes = Browsershot::html($html)
    ->setNodeBinary('/usr/bin/node')
    ->setNpmBinary('/usr/bin/npm')
    ->setChromePath('/var/www/betaprint_hitad_admin/storage/puppeteer-cache/chrome/linux-152.0.7977.42/chrome-linux64/chrome')
    ->noSandbox()
    ->format('A4')
    ->showBackground()
    ->waitUntilNetworkIdle()
    ->pdf();

        $zipName = "advertisement_{$id}.zip";

        return response()->streamDownload(function () use ($id, $pdfBytes, $images) {
            $zip = new ZipStream(
                sendHttpHeaders: false,
                outputName: "advertisement_{$id}.zip"
            );

            $zip->addFile(
                fileName: "advertisement_{$id}.pdf",
                data: $pdfBytes
            );

            foreach ($images as $index => $image) {
                $source = $this->resolveAdvertisementImageSource($image->img_url ?? null);

                if (!$source) {
                    continue;
                }

                $imageData = @file_get_contents($source);
                if ($imageData === false) {
                    continue;
                }

                $zip->addFile(
                    fileName: $this->buildAdvertisementImageZipName($image->img_url ?? null, $index + 1),
                    data: $imageData
                );
            }

            $zip->finish();
        }, $zipName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Resolve an advertisement image source to a readable filesystem path or URL suitable for inclusion in a ZIP.
     * Tries several fallbacks: direct URL, local filesystem paths under public/storage or storage/app/public, and configured storage disk.
     *
     * @param string|null $imgUrl
     * @return string|null
     */
    private function resolveAdvertisementImageSource(?string $imgUrl): ?string
    {
        if (!$imgUrl) {
            return null;
        }

        if (Str::startsWith($imgUrl, ['http://', 'https://'])) {
            return $imgUrl;
        }

        if (file_exists($imgUrl)) {
            return $imgUrl;
        }

        $normalized = ltrim($imgUrl, '/');

        if (Str::startsWith($normalized, 'storage/')) {
            $storageRelative = substr($normalized, strlen('storage/'));
            $publicStoragePath = public_path('storage/' . $storageRelative);

            if (file_exists($publicStoragePath)) {
                return $publicStoragePath;
            }

            $appPublicPath = storage_path('app/public/' . $storageRelative);
            if (file_exists($appPublicPath)) {
                return $appPublicPath;
            }
        }

        $publicPath = public_path($normalized);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        $appPublicPath = storage_path('app/public/' . $normalized);
        if (file_exists($appPublicPath)) {
            return $appPublicPath;
        }

        try {
            if (Storage::disk('oracle')->exists($imgUrl)) {
                return Storage::disk('oracle')->path($imgUrl);
            }
        } catch (\Throwable $e) {
            // Ignore storage lookup failures and fall back to URL-style handling below.
        }

        if (filter_var($imgUrl, FILTER_VALIDATE_URL)) {
            return $imgUrl;
        }

        return null;
    }

    private function resolveAdvertisementImageDisplayUrl(?string $imgUrl): ?string
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

    private function buildAdvertisementImageZipName(?string $imgUrl, int $index): string
    {
        $path = $imgUrl ? (parse_url($imgUrl, PHP_URL_PATH) ?: $imgUrl) : '';
        $basename = pathinfo($path, PATHINFO_BASENAME);
        $extension = pathinfo($basename, PATHINFO_EXTENSION);

        if (!$basename) {
            $basename = 'image_' . $index . ($extension ? '.' . $extension : '.jpg');
        }

        return 'images/' . sprintf('%02d_%s', $index, $basename);
    }

    /**
     * Send advertisement link via email to customer with payment details.
     * 
     * @param int $id Advertisement ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendLinkEmail($id)
    {
        $ad = DB::table('advertisements')
            ->where('advertisements.id', $id)
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->select(
                'advertisements.id',
                'advertisements.publication',
                'advertisements.advertisement_description',
                'customers.customer_name',
                'customers.email',
                'payments.amount',
                'payments.payment_status'
            )
            ->first();

        if (!$ad) {
            return redirect()->back()->with('error', 'Advertisement not found.');
        }

        if (!$ad->email) {
            return redirect()->back()->with('error', 'Customer email not found.');
        }

        try {
            $adViewUrl = url('/advertisements/' . $ad->id . '/view');
            $amount = $ad->amount ? 'Rs. ' . number_format($ad->amount, 2) : 'Not set';

            $adTitle = trim((string) ($ad->advertisement_description ?? ''));
            $adTitle = $adTitle !== '' ? e($adTitle) : 'N/A';
            $normalizedPaymentStatus = strtolower(trim((string) ($ad->payment_status ?? '')));
            $isPaid = in_array($normalizedPaymentStatus, ['completed', 'paid', 'success'], true);
            $paymentStatusLabel = $isPaid ? 'Completed' : 'Pending';
            $paymentStatusColor = $isPaid ? '#2e7d32' : '#d32f2f';
            $actionText = $isPaid ? 'View Advertisement' : 'View Advertisement & Pay Now';
            $orderId = 'ORD' . $ad->id;
            $formattedAmount = $ad->amount ? 'LKR ' . number_format($ad->amount, 2) : 'Not set';
            $isLahipita = strtolower((string) ($ad->publication ?? '')) === 'lahipita';
            $publicationLabel = $isLahipita ? 'Lahipita' : 'HitAd';

            $introText = $isPaid
                ? "Thank you for placing your print advertisement with <strong>{$publicationLabel}</strong>. We confirm that your payment has been successfully received."
                : "Thank you for placing your print advertisement with <strong>{$publicationLabel}</strong>. Please complete your payment to activate your advertisement.";

            $extraInstructions = $isPaid
                ? ""
                : "<tr><td colspan='2' style='padding: 16px 0 0;'>" .
                "<p style='margin:0 0 8px; color:#333; font-size:14px;'>Please complete the payment via bank deposit using the details below:</p>" .
                "<p style='margin:0; color:#333; font-size:14px;'><strong>Bank Name:</strong> Commercial Bank of Ceylon<br><strong>Account Number:</strong> 0123456789</p>" .
                "<p style='margin:8px 0 0; color:#333; font-size:14px;'>Once the payment is completed, please forward the payment slip for verification.</p>" .
                "</td></tr>";

            $bannerImagePath = $isLahipita
                ? public_path('assets/img/illustrations/lahipita-mail.jpg.jpeg')
                : public_path('assets/img/illustrations/hitad_logo.jpeg');

            $htmlBody = "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;'>
  <table width='100%' cellpadding='0' cellspacing='0' style='background-color:#f4f4f4; padding: 30px 0;'>
    <tr>
      <td align='center'>
        <table width='100%' cellpadding='0' cellspacing='0' style='max-width:680px; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>

          <!-- Banner -->
          <tr>
            <td style='padding:0;'>
              <img src='__BANNER_SRC__' alt='{$publicationLabel} Print Advertisement' style='width:100%; display:block;'>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style='padding: 32px 36px;'>

              <p style='margin:0 0 8px; font-size:16px; color:#222;'>Dear <strong>{$ad->customer_name}</strong>,</p>
              <p style='margin:0 0 24px; font-size:15px; color:#1565C0; line-height:1.6;'>{$introText}</p>

              <!-- Info Table -->
              <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse; margin-bottom:24px;'>
                <tr style='border-bottom:1px solid #e0e0e0;'>
                  <td style='padding:14px 0; font-size:14px; font-weight:bold; color:#222; width:50%;'>Order ID</td>
                  <td style='padding:14px 0; font-size:14px; color:#444; text-align:right;'>{$orderId}</td>
                </tr>
                <tr style='border-bottom:1px solid #e0e0e0;'>
                  <td style='padding:14px 0; font-size:14px; font-weight:bold; color:#222;'>Payment Status</td>
                  <td style='padding:14px 0; font-size:14px; color:{$paymentStatusColor}; text-align:right; font-weight:bold;'>{$paymentStatusLabel}</td>
                </tr>
                <tr style='border-bottom:1px solid #e0e0e0;'>
                  <td style='padding:14px 0; font-size:14px; font-weight:bold; color:#222;'>Total Amount</td>
                  <td style='padding:14px 0; font-size:14px; color:#444; text-align:right;'>{$formattedAmount}</td>
                </tr>
                {$extraInstructions}
              </table>

              <!-- Ad Description -->
              <p style='margin:0 0 8px; font-size:15px; font-weight:bold; color:#222;'>Advertisement Description</p>
              <p style='margin:0 0 24px; font-size:14px; color:#1565C0; line-height:1.6;'>{$adTitle}</p>

              <!-- CTA Button -->
              <p style='margin:0 0 24px;'>
                <a href='{$adViewUrl}' style='display:inline-block; padding:13px 28px; background-color:#1565C0; color:#ffffff; text-decoration:none; border-radius:5px; font-size:15px; font-weight:bold;'>{$actionText}</a>
              </p>

              <!-- Support -->
              <p style='margin:0 0 4px; font-size:14px; color:#555;'>If you require further assistance, please contact our support team:</p>
              <p style='margin:0 0 24px; font-size:14px; color:#555;'>
                +94 74 364 3560 (Technical Support)<br>
                +94 112 479 520 (Online Support)
              </p>

                            <p style='margin:0; font-size:14px; color:#333;'>Kind regards,<br><strong>{$publicationLabel} Team</strong></p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>";

            Mail::send([], [], function ($message) use ($ad, $htmlBody, $bannerImagePath, $isLahipita) {
                $bannerSrc = file_exists($bannerImagePath)
                    ? $message->embed($bannerImagePath)
                    : rtrim(config('app.url'), '/') . ($isLahipita
                        ? '/assets/img/illustrations/lahipita-mail.jpg.jpeg'
                        : '/assets/img/illustrations/hitad_logo.jpeg');
                $resolvedHtmlBody = str_replace('__BANNER_SRC__', $bannerSrc, $htmlBody);

                $message->to($ad->email)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Your Advertisement Link - Print Hitad')
                    ->html($resolvedHtmlBody);
            });

            // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ SAVE TO DATABASE
            // AdvertisementEmail::create([
            //     'advertisement_id' => $ad->id,
            //     'customer_email' => $ad->email,
            //     'customer_name' => $ad->customer_name,
            //     'amount' => $ad->amount,
            //     'status' => 'sent',
            // ]);

            return redirect()->back()->with('success', 'Advertisement link sent successfully to ' . $ad->email . '!');
        } 
        // catch (\Exception $e) {
        //     // ÃƒÆ’Ã‚Â¢Ãƒâ€¦Ã¢â‚¬Å“ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¦ SAVE FAILED EMAIL TO DATABASE
        //     AdvertisementEmail::create([
        //         'advertisement_id' => $ad->id,
        //         'customer_email' => $ad->email,
        //         'customer_name' => $ad->customer_name,
        //         'amount' => $ad->amount,
        //         'status' => 'failed',
        //         'error_message' => $e->getMessage(),
        //     ]);

        //     return redirect()->back()->with('error', 'Failed to send email: ' . $e->getMessage());
        // }

        catch (\Exception $e) {

    return redirect()->back()
        ->with('error', 'Failed to send email: ' . $e->getMessage());
}
    }

    /**
     * GET: Load advertisement for editing ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â join customer & payment info and prepare lookup lists (categories, districts, cities).
     * If the advertisement publication is 'lahipita', override English labels with Sinhala where available to keep the edit UI consistent.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editAdvertisement($id)
    {
        $ad = DB::table('advertisements')
            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'customers.address',
                'customers.telephone',
                'customers.email',
                'customers.nic_passport',
                'customers.nic_front_img_url',
                'customers.nic_back_img_url',
                'payments.id as payment_id',
                'payments.amount',
                'payments.payment_status',
                'payments.payment_date',
                'payments.receipt_number',
                'payments.payment_slip_file_path'
            )
            ->where('advertisements.id', $id)
            ->first();

        $categories = DB::table('categories')->where('is_active', 1)->get();
        $districts = DB::table('districts')->where('is_active', 1)->get();
        $cities = DB::table('cities')->where('is_active', 1)->get();
        $tints = DB::table('advertisement_tints')
            ->join('category_has_advertisement_tints', 'advertisement_tints.id', '=', 'category_has_advertisement_tints.advertisement_tint_id')
            ->where('advertisement_tints.is_active', 1)
            ->where('category_has_advertisement_tints.category_id', $ad->category_id)
            ->select(
                'advertisement_tints.id',
                'advertisement_tints.advertisement_tint_en',
                'advertisement_tints.advertisement_tint_si'
            )
            ->orderBy('advertisement_tints.advertisement_tint_en')
            ->orderBy('advertisement_tints.advertisement_tint_si')
            ->get();

        // If this advertisement is for Lahipita, load Sinhala names into the fields
        // the view expects (which currently use *_en properties). This keeps the
        // view unchanged while showing Sinhala labels for Lahipita edits.
        if (!empty($ad) && ($ad->publication ?? '') === 'lahipita') {
            foreach ($categories as $cat) {
                // override the English label with Sinhala when available
                $cat->category_name_en = trim($cat->category_name_si ?? '') !== '' ? $cat->category_name_si : ($cat->category_name_en ?? '');
            }

            foreach ($districts as $d) {
                $d->district_name = trim($d->district_name ?? '') !== '' ? $d->district_name : ($d->district_name ?? '');
            }

            foreach ($cities as $c) {
                $c->city_name = trim($c->city_name ?? '') !== '' ? $c->city_name : ($c->city_name ?? '');
            }
        }

        // Load category-specific criterias and options
        $criterias = DB::table('advertisement_criterias')
            ->where('category_id', $ad->category_id)
            ->where('is_active', 1)
            ->get();

        $criteriaIdsFromCategory = $criterias->pluck('id')->filter()->values();
        $criteriaIdsFromValues = DB::table('advertisement_criteria_values')
            ->where('advertisement_id', $id)
            ->pluck('advertisement_criteria_id')
            ->filter()
            ->values();
        $criteriaIds = $criteriaIdsFromCategory->merge($criteriaIdsFromValues)->unique()->values();

        $criteriaOptions = DB::table('advertisement_criteria_options')
            ->whereIn('advertisement_criteria_id', $criteriaIds)
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

        $currentOptionLabelsByCriteriaId = [];
        $currentOptions = DB::table('advertisement_criteria_options')
            ->whereIn('advertisement_criteria_id', $criterias->pluck('id'))
            ->where('is_active', 1)
            ->get();

        foreach ($currentOptions as $option) {
            $label = trim((string) ($option->advertisement_criteria_option_name_en ?? $option->advertisement_criteria_option_name_si ?? ''));
            if ($label === '') {
                continue;
            }

            $currentOptionLabelsByCriteriaId[(int) $option->advertisement_criteria_id][strtolower($label)] = $label;
        }

        foreach ($criteriaValues as $criteriaId => $value) {
            $trimmedValue = trim((string) $value);
            if ($trimmedValue === '') {
                continue;
            }

            foreach ($criterias as $criteria) {
                if (isset($criteriaValues[$criteria->id]) && $criteriaValues[$criteria->id] !== null) {
                    continue;
                }

                $normalized = strtolower($trimmedValue);
                if (isset($currentOptionLabelsByCriteriaId[(int) $criteria->id][$normalized])) {
                    $criteriaValues[$criteria->id] = $currentOptionLabelsByCriteriaId[(int) $criteria->id][$normalized];
                }
            }
        }

        if (!$ad) {
            abort(404);
        }
        $generalSettings = $this->fetchGeneralSettings();
        $topAdSupported = Schema::hasColumn('advertisements', 'top_ad');
        return view('advertisements.edit', compact('ad', 'categories', 'districts', 'cities', 'criterias', 'criteriaOptions', 'criteriaValues', 'tints', 'generalSettings', 'topAdSupported'));
    }

    /**
     * POST: Update advertisement, customer and payment records and process criteria values.
     * All writes are performed inside a DB transaction.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdvertisement(Request $request, $id)
    {
        $currentRole = strtolower(trim((string) data_get(session('user'), 'role', '')));
        $canEditPaymentFields = $currentRole === 'super admin';
        $topAdSupported = Schema::hasColumn('advertisements', 'top_ad');
        $this->ensureRetypedAdvertisementDescriptionColumnExists();
        $this->ensureRetypedAdvertisementDescriptionDoneColumnExists();
        $this->ensureReferenceNumberColumnExists();

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
            'nic_passport' => 'required|string|max:255',
            'nic_front_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'nic_back_image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'email' => 'nullable|email|max:255',
            'reference_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('advertisements', 'reference_number')->ignore($id)
            ],
            'retyped_advertisement_description' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($id) {
                    $ad = DB::table('advertisements')->where('id', $id)->first();
                    if (!$ad) {
                        return;
                    }

                    $this->validateDescriptionWordLimit(
                        (string) ($ad->publication ?? 'hitad_print'),
                        (string) $value,
                        $fail
                    );
                },
            ],
            'retyped_advertisement_description_done' => 'nullable|boolean',
            'category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'city_id' => 'nullable|exists:cities,id',
            'publish_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($id) {
                    $ad = DB::table('advertisements')->where('id', $id)->first();

                    if (!$ad) {
                        return;
                    }

                    $existingDate = !empty($ad->publish_date)
                        ? Carbon::parse($ad->publish_date)->toDateString()
                        : null;

                    $incomingDate = Carbon::parse($value)->toDateString();
                    if ($existingDate && $incomingDate === $existingDate) {
                        return;
                    }

                    $this->validatePublicationPublishDate((string) ($ad->publication ?? ''), (string) $value, $fail);
                },
            ],
            'advertisement_tint_id' => 'nullable|integer|exists:advertisement_tints,id',
            'web_combined_ad_hitadlk' => 'required|boolean',
            'top_ad' => $topAdSupported ? ['nullable', 'boolean'] : ['prohibited'],
            'payment_status' => $canEditPaymentFields
                ? ['nullable', 'in:pending,completed,failed']
                : ['prohibited'],
            'payment_date' => $canEditPaymentFields
                ? ['nullable', 'date_format:Y-m-d\TH:i']
                : ['prohibited'],
            'receipt_number' => $canEditPaymentFields
                ? ['nullable', 'string', 'max:255']
                : ['prohibited'],
            'payment_slip' => $canEditPaymentFields
                ? ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']
                : ['prohibited'],
            'criteria_image' => 'nullable|array',
            'criteria_image.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
       ], [
    'reference_number.unique' => 'This reference number is already used by another advertisement.',
]);

        if ($request->filled('advertisement_tint_id')) {
            $isTintInCategory = DB::table('category_has_advertisement_tints')
                ->where('category_id', $request->category_id)
                ->where('advertisement_tint_id', $request->advertisement_tint_id)
                ->exists();

            if (!$isTintInCategory) {
                return redirect()->back()
                    ->withErrors(['advertisement_tint_id' => 'The selected tint is not valid for the selected category.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request, $id, $canEditPaymentFields, $topAdSupported) {
            $ad = DB::table('advertisements')->where('id', $id)->first();
            $payment = DB::table('payments')->where('advertisement_id', $id)->first();

            if (!$ad) {
                abort(404);
            }

            $customerRecord = DB::table('customers')->where('id', $ad->customer_id)->first();
            $nicFrontImagePath = $customerRecord->nic_front_img_url ?? null;
            if ($request->hasFile('nic_front_image')) {
                $storagePath = $request->file('nic_front_image')->storePublicly('customer-nic', 'oracle');
                $nicFrontImagePath = Storage::disk('oracle')->url($storagePath);
            }

            $nicBackImagePath = $customerRecord->nic_back_img_url ?? null;
            if ($request->hasFile('nic_back_image')) {
                $storagePath = $request->file('nic_back_image')->storePublicly('customer-nic', 'oracle');
                $nicBackImagePath = Storage::disk('oracle')->url($storagePath);
            }

            DB::table('customers')
                ->where('id', $ad->customer_id)
                ->update([
                    'customer_name' => mb_strtoupper(trim($request->customer_name), 'UTF-8'),
                    'address' => mb_strtoupper(trim($request->address), 'UTF-8'),
                    'telephone' => $request->telephone,
                    'nic_passport' => $request->nic_passport,
                    'email' => $request->email,
                    'nic_front_img_url' => $nicFrontImagePath,
                    'nic_back_img_url' => $nicBackImagePath,
                    'updated_at' => now(),
                ]);

            $existingRetypedDone = (bool) ($ad->retyped_advertisement_description_done ?? false);
            $isRetypedDone = $existingRetypedDone ? true : $request->boolean('retyped_advertisement_description_done');

            $advertisementData = [
                'retyped_advertisement_description' => $existingRetypedDone
                    ? $ad->retyped_advertisement_description
                    : $request->retyped_advertisement_description,
                'retyped_advertisement_description_done' => $isRetypedDone,
                'advertisement_tint_id' => $request->advertisement_tint_id,
                'district_id' => $request->district_id,
                'city_id' => $request->filled('city_id') ? $request->city_id : null,
                'publish_date' => $request->publish_date,
                'web_combined_ad_hitadlk' => $request->boolean('web_combined_ad_hitadlk'),
                'updated_at' => now(),
            ];

                if ($request->input('action') === 'approve') {

                    $adminId = data_get(session('user'), 'id');

                    if ($adminId) {
                    $advertisementData['approved_by_admin_id'] = $adminId;
                    $advertisementData['approved_at'] = now();
                    }
                }

            if (Schema::hasColumn('advertisements', 'reference_number')) {
                $advertisementData['reference_number'] = $request->reference_number;
            } elseif (Schema::hasColumn('advertisements', 'order_ref')) {
                $advertisementData['order_ref'] = $request->reference_number;
            }

            if ($topAdSupported) {
                $advertisementData['top_ad'] = $request->boolean('top_ad');
            }

            DB::table('advertisements')->where('id', $id)->update($advertisementData);


            if ($canEditPaymentFields && $request->filled('payment_status')) {
                $paymentStatus = $request->payment_status;
                $isSuccess = $paymentStatus === 'completed' ? 'true' : 'false';

                if ($request->filled('payment_date')) {
                    $paymentDate = \Illuminate\Support\Carbon::createFromFormat('Y-m-d\TH:i', $request->payment_date)->format('Y-m-d H:i:s');
                } elseif ($paymentStatus === 'completed') {
                    // if status is completed and no date provided, set now (or keep existing)
                    $paymentDate = $payment && !empty($payment->payment_date) ? $payment->payment_date : now()->format('Y-m-d H:i:s');
                } else {
                    $paymentDate = $payment ? $payment->payment_date : null;
                }

                // Handle payment slip file upload
                $paymentSlipPath = null;
                if ($request->hasFile('payment_slip')) {
                    $file = $request->file('payment_slip');
                    // Store the file in OCI bucket
                    if ($request->hasFile('payment_slip')) {
                        $file = $request->file('payment_slip');
                        try {
                            $paymentSlipPath = $file->storePublicly('payment_slips', 'oracle');
                            if (!$paymentSlipPath) {
                                throw new \Exception('Storage returned empty path');
                            }
                            $paymentSlipPath = Storage::disk('oracle')->url($paymentSlipPath);
                        } catch (\Throwable $e) {
                            $paymentSlipPath = $file->store('payment_slips', 'public');
                            \Log::error('Oracle storage failed for payment slip: ' . $e->getMessage());
                        }
                    }
                }

                $data = array_filter([
                    'payment_status' => $paymentStatus,
                    'is_success' => $isSuccess,
                    'payment_date' => $paymentDate,
                    'receipt_number' => $request->receipt_number,
                    'payment_slip_file_path' => $paymentSlipPath,
                    'updated_at' => now(),
                ], static fn($value) => $value !== null);

                if ($payment) {
                    DB::table('payments')->where('id', $payment->id)->update($data);
                } else {
                    // create a minimal payment record when none exists
                    $insert = [
                        'advertisement_id' => $id,
                        'payment_status' => $paymentStatus,
                        'is_success' => $isSuccess,
                        'payment_date' => $paymentDate,
                        'receipt_number' => $request->receipt_number,
                        'payment_slip_file_path' => $paymentSlipPath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::table('payments')->insert($insert);
                }
            } elseif ($canEditPaymentFields && ($request->filled('receipt_number') || $request->hasFile('payment_slip'))) {
                // Handle receipt_number and payment_slip updates even if payment_status is not being changed
                if ($payment) {
                    $paymentSlipPath = null;
                    if ($request->hasFile('payment_slip')) {
                        $file = $request->file('payment_slip');
                        if ($request->hasFile('payment_slip')) {
                            $file = $request->file('payment_slip');
                            try {
                                $paymentSlipPath = $file->storePublicly('payment_slips', 'oracle');
                                if (!$paymentSlipPath) {
                                    throw new \Exception('Storage returned empty path');
                                }
                                $paymentSlipPath = Storage::disk('oracle')->url($paymentSlipPath);
                            } catch (\Throwable $e) {
                                $paymentSlipPath = $file->store('payment_slips', 'public');
                                \Log::error('Oracle storage failed for payment slip: ' . $e->getMessage());
                            }
                        }
                    }

                    $updateData = [];
                    if ($request->filled('receipt_number')) {
                        $updateData['receipt_number'] = $request->receipt_number;
                    }
                    if ($paymentSlipPath) {
                        $updateData['payment_slip_file_path'] = $paymentSlipPath;
                    }
                    $updateData['updated_at'] = now();

                    if (!empty($updateData)) {
                        DB::table('payments')->where('id', $payment->id)->update($updateData);
                    }
                }
            }

            // Process advertisement criterias (if any)
            $criteriaInput = $request->input('criteria', []);
            if (is_array($criteriaInput) && count($criteriaInput) > 0) {
                foreach ($criteriaInput as $criteriaId => $criteriaValue) {
                    // normalize scalar values
                    if (is_array($criteriaValue)) {
                        $value = implode(', ', array_filter($criteriaValue, static fn($item) => filled($item)));
                    } else {
                        $value = $criteriaValue;
                    }

                    if (is_string($value)) {
                        $value = trim($value);
                    }

                    // Skip empty values so we do not violate the NOT NULL column constraint.
                    // If an existing value was previously saved, remove it to keep the data clean.
                    if (!filled($value)) {
                        DB::table('advertisement_criteria_values')
                            ->where('advertisement_id', $id)
                            ->where('advertisement_criteria_id', $criteriaId)
                            ->delete();

                        continue;
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

            // Process image-based criteria updates (replace existing path when a new image is uploaded)
            $criteriaImageInput = $request->file('criteria_image', []);
            if (is_array($criteriaImageInput) && count($criteriaImageInput) > 0) {
                foreach ($criteriaImageInput as $criteriaId => $criteriaImage) {
                    if (!$criteriaImage) {
                        continue;
                    }

                    $imagePath = $criteriaImage->storePublicly('advertisement-criteria-images', 'oracle');

                    $existing = DB::table('advertisement_criteria_values')
                        ->where('advertisement_id', $id)
                        ->where('advertisement_criteria_id', $criteriaId)
                        ->first();

                    if ($existing) {
                        DB::table('advertisement_criteria_values')
                            ->where('id', $existing->id)
                            ->update([
                                'advertisement_criteria_option_value' => $imagePath,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('advertisement_criteria_values')->insert([
                            'advertisement_id' => $id,
                            'advertisement_criteria_id' => $criteriaId,
                            'advertisement_criteria_option_value' => $imagePath,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        if ($request->input('action') === 'approve') {
        return redirect('/advertisements')
        ->with('success', 'Advertisement approved successfully!');
        }

        return redirect('/advertisements')
        ->with('success', 'Advertisement updated successfully!');
    }

public function getHitadPrintUnpaidAdvertisements(Request $request)
{
    $ads = Advertisement::with([
            'customer',
            'category',
            'latestPayment'
        ])
        ->where('publication', 'hitad_print')
        ->whereHas('payments', function ($q) {
            $q->where('is_success', 'false');
        })
        ->orderByDesc('id')
        ->paginate(30);

    return view('advertisements.hitadprint-unpaid', compact('ads'));
}

    public function getLahipitaUnpaidAdvertisements(Request $request)
    {
        $ads = Advertisement::with(['customer', 'category', 'latestPayment'])
            ->where('publication', 'lahipita')
            ->whereHas('payments', function ($q) {
            $q->where('is_success', 'false');
            })
            ->orderByDesc('id')
            ->paginate(30);

        return view('advertisements.lahipita-unpaid', compact('ads'));
    }
    
    private function ensureRetypedAdvertisementDescriptionColumnExists(): void
    {
        if (Schema::hasColumn('advertisements', 'retyped_advertisement_description')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->text('retyped_advertisement_description')->nullable()->after('advertisement_description');
        });
    }

    private function ensureRetypedAdvertisementDescriptionDoneColumnExists(): void
    {
        if (Schema::hasColumn('advertisements', 'retyped_advertisement_description_done')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->boolean('retyped_advertisement_description_done')->default(false)->after('retyped_advertisement_description');
        });
    }
    
    private function ensureReferenceNumberColumnExists(): void
    {
        if (Schema::hasColumn('advertisements', 'reference_number') || Schema::hasColumn('advertisements', 'order_ref')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('retyped_advertisement_description_done');
        });
    }
}