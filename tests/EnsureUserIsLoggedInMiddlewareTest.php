<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsureUserIsLoggedIn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
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

    public function test_advertising_role_is_redirected_from_admin_configuration_pages(): void
    {
        Session::put('user', ['role' => 'advertising admin']);
        $request = Request::create('/categories', 'GET');

        $response = $this->middleware->handle($request, fn () => response('ok', 200));

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/dashboard', $response->getTargetUrl());
    }
}
