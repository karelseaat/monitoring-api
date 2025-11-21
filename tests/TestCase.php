<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase; // Import the trait

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase; // Use the trait

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate'); // Run migrations before each test
    }

    /**
     * Create an organization.
     *
     * @param array $attributes
     * @return \App\Models\Organization
     */
    protected function createOrganization(array $attributes = [])
    {
        return \App\Models\Organization::factory()->create($attributes);
    }

    /**
     * Create a user for a given organization.
     *
     * @param \App\Models\Organization $organization
     * @param array $attributes
     * @return \App\Models\User
     */
    protected function createUser(\App\Models\Organization $organization, array $attributes = [])
    {
        return \App\Models\User::factory()->for($organization)->create($attributes);
    }

    /**
     * Act as a given user for API tests.
     *
     * @param \App\Models\User $user
     * @return $this
     */
    protected function actingAsUser(\App\Models\User $user)
    {
        $this->actingAs($user);
        return $this;
    }
}
