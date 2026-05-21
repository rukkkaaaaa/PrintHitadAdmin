<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipStream\ZipStream;

class GeneralController extends Controller
{
    public function getMembers()
    {
        $members = DB::table('customers')
            ->orderBy('id', 'desc')
            ->get();

        return view('members.index', compact('members'));
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
            ->whereNotNull('advertisement_type_en')
            ->where('advertisement_type_en', '!=', '')
            ->orderBy('advertisement_type_en')
            ->get()
            // remove duplicates by English label (keep first)
            ->unique('advertisement_type_en')
            ->values();

        $adTypesSi = DB::table('advertisement_types')
            ->where('is_active', 1)
            ->whereNotNull('advertisement_type_si')
            ->where('advertisement_type_si', '!=', '')
            ->orderBy('advertisement_type_si')
            ->get()
            // remove duplicates by Sinhala label (keep first)
            ->unique('advertisement_type_si')
            ->values();

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
                    'category_id' => $t->category_id,
                ];
            });

        return response()->json($types);
    }

    /**
     * AJAX: Return advertisement sizes for a given advertisement type.
     * Returns localized label, word count and max images as JSON.
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
                    'id'            => $s->id,
                    'label'         => $label,
                    'ad_word_count' => (int) ($s->ad_word_count ?? 0),
                    'max_images'    => (int) ($s->max_images ?? 0),
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
            'ad_word_count' => 'required|integer|min:1',
            'max_images' => 'required|integer|min:0',
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
            'ad_word_count' => $request->ad_word_count,
            'max_images' => $request->max_images,
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
            'ad_word_count' => 'required|integer|min:1',
            'max_images' => 'required|integer|min:0',
            'category_id' => 'required|integer|exists:categories,id',
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

        /**
         * GET: Show ad rates — reads `ad_rates` table structure (columns) and rows for display.
         * Returns columns metadata and existing ad rates with human-readable labels for category/type/size.
         *
         * @return \Illuminate\View\View
         */
        // GET: Show ad rates (read table structure and rows)
        public function getAdRates()
        {
            // get columns from the ad_rates table
            $columns = DB::select('SHOW COLUMNS FROM ad_rates');

            // get existing rates with readable labels for related IDs
            $adRates = DB::table('ad_rates as ar')
                ->leftJoin('categories as c', 'ar.category_id', '=', 'c.id')
                ->leftJoin('advertisement_types as at', 'ar.advertisement_type_id', '=', 'at.id')
                ->leftJoin('advertisement_sizes as asz', 'ar.advertisement_size_id', '=', 'asz.id')
                ->select(
                    'ar.*',
                    DB::raw("COALESCE(c.category_name_en, c.category_name_si) as category_name"),
                    DB::raw("COALESCE(at.advertisement_type_en, at.advertisement_type_si) as advertisement_type_name"),
                    DB::raw("COALESCE(asz.advertisement_size_en, asz.advertisement_size_si) as advertisement_size_name")
                )
                ->orderBy('ar.id', 'asc')
                ->get();

            // fetch lookup data for dropdowns
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

            return view('adrates.index', compact('columns', 'adRates', 'categoriesEn', 'categoriesSi'));
        }

        /**
         * POST: Add new ad rate row. Uses SHOW COLUMNS to determine which inputs to accept and fills defaults where available.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\RedirectResponse
         */
        // POST: Add new ad rate
        public function addAdRate(Request $request)
        {
            // Basic validation for the dropdowns we added
            $request->validate([
                'category_id' => 'required|integer|exists:categories,id',
                'advertisement_type_id' => 'required|integer|exists:advertisement_types,id',
                'advertisement_size_id' => 'required|integer|exists:advertisement_sizes,id',
                'publication' => 'required|in:hitad_print,lahipita',
            ]);

            // get column list so we know which inputs to accept
            $columns = DB::select('SHOW COLUMNS FROM ad_rates');

            $data = [];

            foreach ($columns as $col) {
                $field = $col->Field;

                if (in_array($field, ['id', 'created_at', 'updated_at'])) {
                    // skip auto fields
                    continue;
                }

                // use request value when present, otherwise default
                if ($request->has($field)) {
                    $data[$field] = $request->input($field);
                } elseif (!is_null($col->Default)) {
                    $data[$field] = $col->Default;
                } else {
                    $data[$field] = null;
                }
            }

            DB::table('ad_rates')->insert($data + ['created_at' => now(), 'updated_at' => now()]);

            return redirect()->back()->with('success', 'Ad rate added successfully!');
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

        return view('advertisements.create', compact('categories', 'districts', 'cities', 'criterias', 'criteriaOptions', 'paymentMethods'));
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
            'email' => 'nullable|email|max:255',
            'confirm_email' => 'nullable|email|max:255|same:email',
            'advertisement_type_id' => 'required|exists:advertisement_types,id',
            'advertisement_size_id' => 'required|exists:advertisement_sizes,id',
            'advertisement_description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'district_id' => 'required|exists:districts,id',
            'city_id' => 'required|exists:cities,id',
            'publish_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (in_array(request('publication'), ['lahipita', 'hitad_print', 'hitad']) && \Illuminate\Support\Carbon::parse($value)->dayOfWeek !== \Illuminate\Support\Carbon::SUNDAY) {
                        $fail('The publish date must be a Sunday.');
                    }
                },
            ],
            'web_combined_ad' => 'nullable|boolean',
            'status' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:4096',
            'criteria' => 'nullable|array',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'payment_amount'    => 'nullable|numeric|min:0',
            'payment_status'    => 'nullable|in:pending,completed,failed',
            'payment_date'      => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $customer = DB::table('customers')->where('nic_passport', $request->nic_passport)->first();

            $customerData = [
                'customer_name' => $request->customer_name,
                'address' => $request->address,
                'telephone' => $request->telephone,
                'nic_passport' => $request->nic_passport,
                'email' => $request->email,
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

            $adId = DB::table('advertisements')->insertGetId([
                'customer_id' => $customerId,
                'category_id' => $request->category_id,
                'advertisement_type_id' => $request->advertisement_type_id,
                'advertisement_size_id' => $request->advertisement_size_id,
                'district_id' => $request->district_id,
                'city_id' => $request->city_id,
                'advertisement_description' => $request->advertisement_description,
                'publish_date' => $request->publish_date,
                'publication' => $request->publication,
                'web_combined_ad' => $request->boolean('web_combined_ad'),
                'status' => $request->boolean('status', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ((array) $request->input('criteria', []) as $criteriaId => $criteriaValue) {
                if (is_array($criteriaValue)) {
                    $criteriaValue = implode(', ', array_filter($criteriaValue, static fn ($item) => filled($item)));
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

            if ($request->filled('payment_method_id') || $request->filled('payment_amount')) {
                DB::table('payments')->insert([
                    'advertisement_id'  => $adId,
                    'payment_method_id' => $request->payment_method_id ?: 1,
                    'amount'            => $request->payment_amount ?: 0,
                    'payment_status'    => $request->payment_status ?: 'pending',
                    'payment_date'      => $request->filled('payment_date') ? $request->payment_date : now()->toDateTimeString(),
                    'is_success'        => ($request->payment_status === 'completed') ? 'true' : 'false',
                    'session_id'        => '',
                    'success_indicator' => '',
                    'result'            => '',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        });

        return redirect('/advertisements')->with('success', 'Advertisement added successfully!');
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
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name'),
                DB::raw('COALESCE(cities.city_name_en, cities.city_name_si) as city_name'),
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
            $query->where(function($q) use ($cat) {
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

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only(['search','category','publish_date','customer_name','phone','email']));

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
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            // ✅ PAYMENTS
            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name'),
                DB::raw('COALESCE(cities.city_name_en, cities.city_name_si) as city_name'),

                'payments.payment_status',
            )

            // ✅ 🔥 IMPORTANT FILTER (THIS IS WHAT YOU WANT)
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
            $query->where(function($q) use ($cat) {
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

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only(['search','category','publish_date','customer_name','phone','email']));

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

                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name'),
                DB::raw('COALESCE(cities.city_name_en, cities.city_name_si) as city_name'),

                // ✅ Payment fields
                'payments.amount',
                'payments.payment_status',
                'payments.payment_date',

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
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->join('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY HITAD PRINT ADS
            ->where('advertisements.publication', 'hitad_print')

            // ✅ ONLY PAID
            ->where('payments.payment_status', 'completed')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name'),
                DB::raw('COALESCE(cities.city_name_en, cities.city_name_si) as city_name'),

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
            $query->where(function($q) use ($cat) {
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

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only(['search','category','publish_date','customer_name','phone','email']));

        return view('advertisements.paid', compact('ads'));
    }
    /**
     * List hitad_print advertisements that are unpaid (no payment record or pending/failed).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getUnpaidAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY HITAD PRINT ADS
            ->where('advertisements.publication', 'hitad_print')

            // ✅ UNPAID LOGIC
            ->where(function ($q) {
                $q->whereNull('payments.id') // no payment
                    ->orWhere('payments.payment_status', 'pending') // pending
                    ->orWhere('payments.payment_status', 'failed'); // failed
            })

            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name'),
                DB::raw('COALESCE(cities.city_name_en, cities.city_name_si) as city_name'),

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payment_methods.payment_method_name as payment_method'
            )

            ;

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
            $query->where(function($q) use ($cat) {
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

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only(['search','category','publish_date','customer_name','phone','email']));

        return view('advertisements.unpaid', compact('ads'));
    }
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
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                'districts.district_name_si as district_name',
                'cities.city_name_si as city_name',

                'payments.payment_status',
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
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->join('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->join('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY LAHIPITA ADS
            ->where('advertisements.publication', 'lahipita')

            // ✅ ONLY PAID
            ->where('payments.payment_status', 'completed')

            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                'districts.district_name_si as district_name',
                'cities.city_name_si as city_name',

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
            $query->where(function($q) use ($cat) {
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

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only(['search','category','publish_date','customer_name','phone','email']));

        return view('advertisements.lahipita_paid', compact('ads'));
    }
    /**
     * List 'lahipita' advertisements that are unpaid (no payment or pending/failed).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function getLahipitaUnpaidAdvertisements(Request $request)
    {
        $query = DB::table('advertisements')

            ->join('customers', 'advertisements.customer_id', '=', 'customers.id')
            ->join('categories', 'advertisements.category_id', '=', 'categories.id')
            ->join('districts', 'advertisements.district_id', '=', 'districts.id')
            ->join('cities', 'advertisements.city_id', '=', 'cities.id')

            ->leftJoin('payments', 'advertisements.id', '=', 'payments.advertisement_id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')

            // ✅ ONLY LAHIPITA ADS
            ->where('advertisements.publication', 'lahipita')

            // ✅ UNPAID LOGIC (IMPORTANT)
            ->where(function ($q) {
                $q->whereNull('payments.id')
                    ->orWhere('payments.payment_status', 'pending')
                    ->orWhere('payments.payment_status', 'failed');
            })

            ->select(
                'advertisements.*',
                'customers.customer_name',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
                'districts.district_name_si as district_name',
                'cities.city_name_si as city_name',

                'payments.amount',
                'payments.payment_date',
                'payments.payment_status',
                'payment_methods.payment_method_name as payment_method'
            )

            ;

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
            $query->where(function($q) use ($cat) {
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

        $ads = $query->orderBy('advertisements.id', 'desc')->paginate(10);
        $ads->appends($request->only(['search','category','publish_date','customer_name','phone','email']));

        return view('advertisements.lahipita_unpaid', compact('ads'));
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

        $monthLabel = $month->format('F Y');

        return view('reports', compact('monthInput', 'monthLabel', 'reportSections'));
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
                DB::raw('COALESCE(districts.district_name_en, districts.district_name_si) as district_name'),
                DB::raw('COALESCE(cities.city_name_en, cities.city_name_si) as city_name'),
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

        return [
            'count' => $ads->count(),
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
            ->select(
                'advertisements.*',
                'customers.customer_name',
                'customers.address',
                'customers.telephone',
                'customers.email',
                'customers.nic_passport',
                DB::raw('COALESCE(categories.category_name_en, categories.category_name_si) as category_name'),
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

        $criterias = DB::table('advertisement_criterias')
            ->where('category_id', $ad->category_id)
            ->where('is_active', 1)
            ->get();

        $criteriaValuesRaw = DB::table('advertisement_criteria_values')
            ->where('advertisement_id', $id)
            ->get();

        $criteriaValues = [];
        foreach ($criteriaValuesRaw as $cv) {
            $criteriaValues[$cv->advertisement_criteria_id] = $cv->advertisement_criteria_option_value;
        }

        $images = DB::table('advertisement_images')
            ->where('advertisement_id', $id)
            ->where('is_active', 1)
            ->orderBy('id', 'asc')
            ->get();

        $pdfBytes = Pdf::loadView('advertisements.pdf', compact('ad', 'criterias', 'criteriaValues'))->output();
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
     * GET: Load advertisement for editing — join customer & payment info and prepare lookup lists (categories, districts, cities).
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
                'payments.id as payment_id',
                'payments.amount',
                'payments.payment_status',
                'payments.payment_date',
            )
            ->where('advertisements.id', $id)
            ->first();

        $categories = DB::table('categories')->where('is_active', 1)->get();
        $districts = DB::table('districts')->where('is_active', 1)->get();
        $cities = DB::table('cities')->where('is_active', 1)->get();

        // If this advertisement is for Lahipita, load Sinhala names into the fields
        // the view expects (which currently use *_en properties). This keeps the
        // view unchanged while showing Sinhala labels for Lahipita edits.
        if (!empty($ad) && ($ad->publication ?? '') === 'lahipita') {
            foreach ($categories as $cat) {
                // override the English label with Sinhala when available
                $cat->category_name_en = trim($cat->category_name_si ?? '') !== '' ? $cat->category_name_si : ($cat->category_name_en ?? '');
            }

            foreach ($districts as $d) {
                $d->district_name_en = trim($d->district_name_si ?? '') !== '' ? $d->district_name_si : ($d->district_name_en ?? '');
            }

            foreach ($cities as $c) {
                $c->city_name_en = trim($c->city_name_si ?? '') !== '' ? $c->city_name_si : ($c->city_name_en ?? '');
            }
        }

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
            'publish_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($id) {
                    $ad = DB::table('advertisements')->where('id', $id)->first();
                    if ($ad && in_array($ad->publication ?? '', ['lahipita', 'hitad_print', 'hitad'])) {
                        if (\Illuminate\Support\Carbon::parse($value)->dayOfWeek !== \Illuminate\Support\Carbon::SUNDAY) {
                            $fail('The publish date must be a Sunday.');
                        }
                    }
                },
            ],
            'web_combined_ad' => 'required|boolean',
            'status' => 'required|boolean',
            'payment_status' => 'nullable|in:pending,completed,failed',
            'payment_date' => 'nullable|date_format:Y-m-d\TH:i',
        ]);

        DB::transaction(function () use ($request, $id) {
            $ad = DB::table('advertisements')->where('id', $id)->first();
            $payment = DB::table('payments')->where('advertisement_id', $id)->first();

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

            if ($request->filled('payment_status')) {
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

                $data = array_filter([
                    'payment_status' => $paymentStatus,
                    'is_success' => $isSuccess,
                    'payment_date' => $paymentDate,
                    'updated_at' => now(),
                ], static fn ($value) => $value !== null);

                if ($payment) {
                    DB::table('payments')->where('id', $payment->id)->update($data);
                } else {
                    // create a minimal payment record when none exists
                    $insert = [
                        'advertisement_id' => $id,
                        'payment_status' => $paymentStatus,
                        'is_success' => $isSuccess,
                        'payment_date' => $paymentDate,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    DB::table('payments')->insert($insert);
                }
            }

            // Process advertisement criterias (if any)
            $criteriaInput = $request->input('criteria', []);
            if (is_array($criteriaInput) && count($criteriaInput) > 0) {
                foreach ($criteriaInput as $criteriaId => $criteriaValue) {
                    // normalize scalar values
                    if (is_array($criteriaValue)) {
                        $value = implode(', ', array_filter($criteriaValue, static fn ($item) => filled($item)));
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
        });

        return redirect('/advertisements')->with('success', 'Advertisement updated successfully!');
    }
}
