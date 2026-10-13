<?php

namespace Tests\Unit;

use App\Http\Middleware\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LogActivityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Session::flush();
    }

    public function test_successful_page_views_are_saved_with_a_description(): void
    {
        Session::put('user', ['id' => 12]);
        $middleware = $this->recordingMiddleware();
        $request = Request::create('/members', 'GET');
        $request->setRouteResolver(fn () => new class {
            public function getActionMethod(): string
            {
                return 'getMembers';
            }

            public function parameters(): array
            {
                return [];
            }
        });

        $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame([[12, 'Get Members']], $middleware->activities);
    }

    public function test_successful_changes_include_the_action_and_relevant_record_id(): void
    {
        Session::put('user', ['id' => 12]);
        $middleware = $this->recordingMiddleware();
        $request = Request::create('/advertisements/123/update', 'POST');
        $request->setRouteResolver(fn () => new class {
            public function getActionMethod(): string
            {
                return 'updateAdvertisement';
            }

            public function parameters(): array
            {
                return ['id' => 123];
            }
        });

        $middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame([[12, 'Update Advertisement (123)']], $middleware->activities);
    }

    public function test_failed_requests_are_not_recorded(): void
    {
        Session::put('user', ['id' => 12]);
        $middleware = $this->recordingMiddleware();

        $middleware->handle(
            Request::create('/members', 'GET'),
            fn () => response('failed', 500)
        );

        $this->assertSame([], $middleware->activities);
    }

    public function test_unauthenticated_requests_are_not_recorded(): void
    {
        $middleware = $this->recordingMiddleware();

        $middleware->handle(
            Request::create('/members', 'GET'),
            fn () => response('ok', 200)
        );

        $this->assertSame([], $middleware->activities);
    }

    private function recordingMiddleware(): LogActivity
    {
        return new class extends LogActivity {
            public array $activities = [];

            protected function writeActivity(int $adminId, string $description): void
            {
                $this->activities[] = [$adminId, $description];
            }
        };
    }
}
