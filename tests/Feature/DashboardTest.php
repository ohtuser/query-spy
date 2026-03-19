<?php

namespace QuerySpy\Tests\Feature;

use Orchestra\Testbench\TestCase;
use QuerySpy\QuerySpyServiceProvider;

class DashboardTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [QuerySpyServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('queryspy.enabled', true);
        $app['config']->set('queryspy.environments', ['testing']);
        $app['config']->set('queryspy.password', null);
        $app['config']->set('queryspy.dashboard_url', '/queryspy');
        $app['config']->set('queryspy.storage_path', sys_get_temp_dir() . '/queryspy_test_' . uniqid());
    }

    public function test_dashboard_returns_200(): void
    {
        $response = $this->get('/queryspy');
        $response->assertStatus(200);
    }

    public function test_api_returns_json(): void
    {
        $response = $this->get('/queryspy/api');
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'stats', 'meta']);
    }

    public function test_dashboard_requires_password_when_set(): void
    {
        config(['queryspy.password' => 'secret']);

        $response = $this->get('/queryspy');
        $response->assertStatus(200);
        $response->assertSee('Password'); // auth view
    }

    public function test_dashboard_accessible_with_correct_password(): void
    {
        config(['queryspy.password' => 'secret']);

        $response = $this->get('/queryspy?password=secret');
        $response->assertStatus(200);
        $response->assertSee('QuerySpy');
    }

    public function test_clear_wipes_logs(): void
    {
        $response = $this->post('/queryspy/clear');
        $response->assertRedirect();
    }

    public function test_csv_export_returns_file(): void
    {
        $response = $this->get('/queryspy/export/csv');
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
