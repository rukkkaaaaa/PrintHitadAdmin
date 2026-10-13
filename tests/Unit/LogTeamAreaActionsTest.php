<?php

namespace Tests\Unit;

use App\Http\Middleware\LogTeamAreaActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

class LogTeamAreaActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Session::flush();
    }

    public function test_successful_area_page_views_are_logged_with_the_area_and_actor(): void
    {
        Session::put('user', [
            'id' => 12,
            'name' => 'Team Nalaka',
            'role' => 'team nalaka',
        ]);
        Schema::shouldReceive('hasTable')->once()->with('team_area_activity_logs')->andReturn(true);

        $insertQuery = Mockery::mock();
        $insertQuery->shouldReceive('insert')
            ->once()
            ->with(Mockery::on(function (array $event): bool {
                $this->assertSame(12, $event['admin_id']);
                $this->assertSame('Team Nalaka', $event['admin_name']);
                $this->assertSame('team nalaka', $event['admin_role']);
                $this->assertSame('translate', $event['area_key']);
                $this->assertSame('View', $event['action']);
                $this->assertNull($event['advertisement_id']);

                return true;
            }))
            ->andReturn(true);
        DB::shouldReceive('table')
            ->once()
            ->with('team_area_activity_logs')
            ->andReturn($insertQuery);

        $response = (new LogTeamAreaActions())->handle(
            Request::create('/advertisements/lahipita/translate', 'GET'),
            fn () => response('ok', 200)
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_redirected_requests_are_not_logged_as_views(): void
    {
        Session::put('user', [
            'id' => 12,
            'name' => 'Team Nalaka',
            'role' => 'team nalaka',
        ]);

        $response = (new LogTeamAreaActions())->handle(
            Request::create('/advertisements/lahipita/approved', 'GET'),
            fn () => redirect('/team-home')
        );

        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_weekly_activity_report_route_has_been_removed(): void
    {
        $this->get('/team-area-activity')->assertNotFound();
    }
}
