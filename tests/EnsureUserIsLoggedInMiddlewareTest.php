<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserIsLoggedIn;
use App\Http\Controllers\TeamAccessController;
use App\Support\TeamAreaPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Mockery;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EnsureUserIsLoggedInMiddlewareTest extends TestCase
{
    private EnsureUserIsLoggedIn $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new EnsureUserIsLoggedIn();
        Session::flush();
    }

    public function test_redirects_to_login_when_user_session_is_missing(): void
    {
        $request = Request::create('/dashboard', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/login', $response->getTargetUrl());
    }

    public function test_team_user_visiting_home_is_redirected_to_dashboard(): void
    {
        $response = $this->withSession([
            'user' => ['id' => 8, 'role' => 'team nalaka'],
        ])->get('/');

        $response->assertRedirect('/dashboard');
    }

    public function test_super_admin_has_full_access(): void
    {
        Session::put('user', ['role' => 'super admin']);
        $request = Request::create('/categories', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }

    public function test_reporting_role_can_access_reports(): void
    {
        Session::put('user', ['role' => 'report admin']);
        $request = Request::create('/reports', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_reporting_role_is_redirected_when_accessing_non_report_pages(): void
    {
        Session::put('user', ['role' => 'report admin']);
        $request = Request::create('/advertisements', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/reports', $response->getTargetUrl());
    }

    public function test_site_admin_can_access_configuration_pages(): void
    {
        Session::put('user', ['role' => 'site admin']);
        $request = Request::create('/categories', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_site_admin_is_redirected_from_advertisement_pages(): void
    {
        Session::put('user', ['role' => 'site admin']);
        $request = Request::create('/advertisements', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/dashboard', $response->getTargetUrl());
    }

    public function test_advertising_role_can_access_advertisement_pages(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/advertisements/create', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_advertising_role_can_access_web_combined_ads(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/web-combined-ads', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_advertising_role_can_access_lahipita_web_combined_ads(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/advertisements/lahipita/web-combined-ads', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_advertising_role_can_open_translation_queue(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/advertisements/lahipita/translate', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_advertising_role_can_open_lahipita_approved_ads(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/advertisements/lahipita/approved', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_advertising_role_can_submit_translation_completion(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/advertisements/lahipita/translate/123/complete', 'POST');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_advertising_role_is_redirected_from_admin_configuration_pages(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/categories', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/dashboard', $response->getTargetUrl());
    }

    public function test_team_nalaka_can_access_translation_queue(): void
    {
        Session::put('user', ['id' => 8, 'role' => 'team nalaka']);
        $this->mockTeamAreaPermission('team nalaka', 'translate', true);
        $request = Request::create('/advertisements/lahipita/translate', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_team_nalaka_can_open_a_translation_assignment(): void
    {
        Session::put('user', ['id' => 8, 'role' => 'team nalaka']);
        $this->mockTeamAreaPermission('team nalaka', 'translate', true);
        $request = Request::create('/advertisements/lahipita/translate/123', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_team_nalaka_can_submit_only_translation_completion_route(): void
    {
        Session::put('user', ['id' => 8, 'role' => 'team nalaka']);
        $this->mockTeamAreaPermission('team nalaka', 'translate', true);
        $request = Request::create('/advertisements/lahipita/translate/123/complete', 'POST');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_team_nalaka_is_redirected_from_approved_advertisements(): void
    {
        Session::put('user', ['id' => 8, 'role' => 'team nalaka']);
        $this->mockTeamAreaPermission('team nalaka', 'hitad_approved_ads', false);
        $request = Request::create('/advertisements/hitad/approved', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/team-home', $response->getTargetUrl());
    }

    public function test_team_chandana_access_is_denied_until_area_is_granted(): void
    {
        Session::put('user', ['id' => 9, 'role' => 'team chandana']);
        $this->mockTeamAreaPermission('team chandana', 'lahipita_approved_ads', false);
        $request = Request::create('/advertisements/lahipita/approved', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/team-home', $response->getTargetUrl());
    }

    public function test_team_chandana_can_access_an_area_after_it_is_granted(): void
    {
        Session::put('user', ['id' => 9, 'role' => 'team chandana']);
        $this->mockTeamAreaPermission('team chandana', 'lahipita_approved_ads', true);
        $request = Request::create('/advertisements/lahipita/approved', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_team_nalaka_can_access_dashboard(): void
    {
        Session::put('user', ['id' => 8, 'role' => 'team nalaka']);
        $request = Request::create('/dashboard', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }

    public function test_requested_work_area_routes_are_mapped_to_their_permissions(): void
    {
        $cases = [
            ['GET', '/advertisements/lahipita/web-combined-ads', 'lahipita_web_combined_ads'],
            ['GET', '/advertisements/lahipita/translate', 'translate'],
            ['GET', '/advertisements/lahipita/translate/123', 'translate'],
            ['POST', '/advertisements/lahipita/translate/123/complete', 'translate'],
            ['GET', '/advertisements/lahipita/approved', 'lahipita_approved_ads'],
            ['POST', '/advertisements/lahipita/approved/123/view', 'lahipita_approved_ads'],
            ['GET', '/advertisements/lahipita/print-on-paper', 'print_on_hitad_paper_ads'],
            ['POST', '/advertisements/lahipita/print-on-paper/123/view', 'print_on_hitad_paper_ads'],
            ['GET', '/advertisements/hitad/approved', 'hitad_approved_ads'],
            ['POST', '/advertisements/hitad/approved/123/view', 'hitad_approved_ads'],
            ['GET', '/web-combined-ads', 'hitad_web_combined_ads'],
        ];

        foreach ($cases as [$method, $path, $expectedArea]) {
            $this->assertSame(
                $expectedArea,
                TeamAreaPermissions::areaForRequest(Request::create($path, $method)),
                $method . ' ' . $path
            );
        }
    }

    public function test_team_access_is_not_shared_with_another_account_of_the_same_role(): void
    {
        Session::put('user', ['id' => 10, 'role' => 'team chandana']);
        $this->mockTeamAreaPermission('team chandana', 'lahipita_approved_ads', false);
        $request = Request::create('/advertisements/lahipita/approved', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/team-home', $response->getTargetUrl());
    }

    public function test_team_account_cannot_open_the_access_management_ui(): void
    {
        Session::put('user', ['id' => 8, 'role' => 'team nalaka']);
        try {
            (new TeamAccessController())->index();
            $this->fail('Team accounts must not be able to manage access.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_team_account_cannot_submit_access_changes(): void
    {
        Session::put('user', ['id' => 9, 'role' => 'team chandana']);
        try {
            (new TeamAccessController())->update(new Request());
            $this->fail('Team accounts must not be able to update access.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    private function mockTeamAreaPermission(string $role, string $area, bool $granted): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('admin_area_permissions')
            ->andReturn(true);

        $query = Mockery::mock(Builder::class);
        $adminId = (int) data_get(session('user'), 'id');
        $query->shouldReceive('where')->once()->with('admin_id', $adminId)->andReturnSelf();
        $query->shouldReceive('where')->once()->with('area_key', $area)->andReturnSelf();
        $query->shouldReceive('exists')->once()->andReturn($granted);

        DB::shouldReceive('table')
            ->once()
            ->with('admin_area_permissions')
            ->andReturn($query);
    }
}
