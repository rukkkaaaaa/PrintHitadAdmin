<?php

namespace App\Http\Controllers;

use App\Support\TeamAreaActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TranslationController extends Controller
{
    private function authorizeQueueViewer(): void
    {
        $role = strtolower(trim((string) data_get(session('user'), 'role', '')));
        abort_unless(in_array($role, [
            'team nalaka',
            'team chandana',
            'administrative level',
            'super admin',
            'advertising admin',
        ], true), 403);
    }

    private function descriptionWordLimitRule(string $publication): \Closure
    {
        return function ($attribute, $value, $fail) use ($publication) {
            $settingsColumn = trim($publication) === 'lahipita' ? 'max_words_si' : 'max_words_en';
            $maxWords = 65;

            if (Schema::hasTable('general_settings') && Schema::hasColumn('general_settings', $settingsColumn)) {
                $configuredLimit = DB::table('general_settings')->value($settingsColumn);
                if ($configuredLimit !== null && $configuredLimit !== '') {
                    $maxWords = (int) $configuredLimit;
                }
            }

            if ($maxWords <= 0) {
                return;
            }

            $normalized = trim(preg_replace('/\\s+/u', ' ', (string) $value));
            $words = $normalized === '' ? [] : (preg_split('/\\s+/u', $normalized) ?: []);
            if (count($words) > $maxWords) {
                $fail('Description cannot exceed ' . $maxWords . ' words for the selected publication.');
            }
        };
    }

    public function index()
    {
        $this->authorizeQueueViewer();

        $advertisements = DB::table('advertisements')
            ->leftJoin('categories', 'advertisements.category_id', '=', 'categories.id')
            ->leftJoin('admins as translation_approver', 'advertisements.approved_by_admin_id', '=', 'translation_approver.id')
            ->whereNotNull('advertisements.translation_requested_at')
            ->where('advertisements.publication', 'lahipita')
            ->orderBy('advertisements.translation_requested_at')
            ->select(
                'advertisements.id',
                'advertisements.publication',
                'advertisements.translation_requested_at',
                'advertisements.translated_advertisement_description_done',
                'advertisements.approved_at',
                'translation_approver.admin_name as translated_approved_by_name',
                'categories.category_name_en',
                'categories.category_name_si'
            )
            ->paginate(30)
            ->withQueryString();

        return view('translations.index', compact('advertisements'));
    }

    public function show($id)
    {
        $this->authorizeQueueViewer();

        $advertisement = DB::table('advertisements')
            ->where('id', $id)
            ->whereNotNull('translation_requested_at')
            ->where('publication', 'lahipita')
            ->first([
                'id',
                'publication',
                'retyped_advertisement_description',
                'translated_advertisement_description',
                'translated_reference_number',
                'translation_requested_at',
            ]);

        abort_unless($advertisement, 404);

        return app(GeneralController::class)->editAdvertisement($id, true);
    }

    public function complete(Request $request, $id)
    {
        $this->authorizeQueueViewer();

        $advertisement = DB::table('advertisements')
            ->where('id', $id)
            ->whereNotNull('translation_requested_at')
            ->where('publication', 'lahipita')
            ->where('translated_advertisement_description_done', false)
            ->first(['id']);

        abort_unless($advertisement, 404);

        $publication = DB::table('advertisements')->where('id', $id)->value('publication') ?? 'hitad_print';

        $validated = $request->validate([
            'translated_advertisement_description' => ['required', 'string', $this->descriptionWordLimitRule((string) $publication)],
            'translated_reference_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('advertisements', 'translated_reference_number')->ignore($id),
            ],
        ]);

        $adminId = data_get(session('user'), 'id');
        abort_unless($adminId, 403);
        $completedAt = now();

        DB::table('advertisements')
            ->where('id', $id)
            ->whereNotNull('translation_requested_at')
            ->where('translated_advertisement_description_done', false)
            ->update([
                'translated_advertisement_description' => $validated['translated_advertisement_description'],
                'translated_reference_number' => $validated['translated_reference_number'],
                'translated_advertisement_description_done' => true,
                'approved_by_admin_id' => $adminId,
                'approved_at' => $completedAt,
                'updated_at' => $completedAt,
            ]);

        TeamAreaActivityLog::record('translate', 'Translated', (int) $id);
        TeamAreaActivityLog::record('lahipita_approved_ads', 'Approve', (int) $id);

        $role = strtolower(trim((string) data_get(session('user'), 'role', '')));
        $redirectTo = $role === 'team nalaka'
            ? '/advertisements/lahipita/translate/'
            : '/advertisements/lahipita/approved';

        return redirect($redirectTo)
            ->with('success', 'Translation completed and advertisement approved.');
    }

    public function sendToTranslation(Request $request, $id)
    {
        $role = strtolower(trim((string) data_get(session('user'), 'role', '')));
        abort_unless(in_array($role, ['administrative level', 'super admin', 'advertising admin'], true), 403);

        $sourceAd = DB::table('advertisements')->where('id', $id)->first(['id', 'publication']);
        abort_unless($sourceAd, 404);
        abort_unless(trim((string) ($sourceAd->publication ?? '')) === 'lahipita', 404);

        $validated = $request->validate([
            'retyped_advertisement_description' => [
                'required',
                'string',
                $this->descriptionWordLimitRule((string) ($sourceAd->publication ?? 'hitad_print')),
            ],
            'retyped_description_confirmed' => ['required', 'accepted'],
            'reference_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('advertisements', 'reference_number')->ignore($id),
            ],
            'retyped_reference_number_2' => [
                'required',
                'string',
                'max:255',
                'different:reference_number',
                Rule::unique('advertisements', 'retyped_reference_number_2')->ignore($id),
            ],
        ], [
            'reference_number.required' => 'Retyped reference number 1 is required before sending for translation.',
            'retyped_reference_number_2.required' => 'Retyped reference number 2 is required before sending for translation.',
            'retyped_reference_number_2.different' => 'Retyped reference numbers 1 and 2 must be different.',
        ]);

        $sentAt = DB::transaction(function () use ($id, $validated) {
            $advertisement = DB::table('advertisements')
                ->where('id', $id)
                ->lockForUpdate()
                ->first([
                    'id',
                    'retyped_advertisement_description',
                    'retyped_advertisement_description_done',
                    'translation_requested_at',
                ]);

            abort_unless($advertisement, 404);

            if (!empty($advertisement->translation_requested_at)) {
                return $advertisement->translation_requested_at;
            }

            $sentAt = now();
            $update = [
                'translation_requested_at' => $sentAt,
                'translation_requested_by_admin_id' => data_get(session('user'), 'id'),
                'reference_number' => $validated['reference_number'],
                'retyped_reference_number_2' => $validated['retyped_reference_number_2'],
                'updated_at' => $sentAt,
            ];

            if (!(bool) $advertisement->retyped_advertisement_description_done) {
                $update['retyped_advertisement_description'] = $validated['retyped_advertisement_description'];
                $update['retyped_advertisement_description_done'] = true;
            }

            DB::table('advertisements')->where('id', $id)->update($update);

            return $sentAt;
        });

        return response()->json([
            'success' => true,
            'message' => 'Advertisement sent to Team Nalaka for translation.',
            'sent_at' => $sentAt,
        ]);
    }
}
