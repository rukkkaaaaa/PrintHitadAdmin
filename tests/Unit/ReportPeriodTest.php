<?php

namespace Tests\Unit;

use App\Http\Controllers\GeneralController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class ReportPeriodTest extends TestCase
{
    public function test_custom_report_period_uses_the_selected_inclusive_dates(): void
    {
        $period = $this->resolvePeriod([
            'period' => 'custom',
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-13',
        ]);

        $this->assertSame('custom', $period['type']);
        $this->assertSame('2026-10-01', $period['start']->toDateString());
        $this->assertSame('2026-10-13', $period['end']->toDateString());
        $this->assertSame('2026-10-01 to 2026-10-13', $period['input']);
        $this->assertSame('custom_2026_10_01_to_2026_10_13', $period['filename']);
    }

    public function test_custom_report_period_rejects_an_end_date_before_the_start_date(): void
    {
        $this->expectException(ValidationException::class);

        $this->resolvePeriod([
            'period' => 'custom',
            'start_date' => '2026-10-13',
            'end_date' => '2026-10-01',
        ]);
    }

    private function resolvePeriod(array $query): array
    {
        $controller = new GeneralController();
        $method = new ReflectionMethod(GeneralController::class, 'resolveReportPeriod');
        $method->setAccessible(true);

        return $method->invoke($controller, Request::create('/reports', 'GET', $query));
    }
}
