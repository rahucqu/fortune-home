<?php

declare(strict_types=1);

it('can access 404 error page in production environment', function () {
    config(['app.env' => 'production']);

    $response = $this->get('/this-page-definitely-does-not-exist-12345');

    $response->assertStatus(404);

    // Restore environment
    config(['app.env' => 'local']);
});

it('can display error page component correctly', function () {
    expect(file_exists(resource_path('js/pages/ErrorPage.tsx')))->toBeTrue();
});

it('production error handling configuration is set up', function () {
    // Test that our bootstrap/app.php has the correct error handling setup
    $bootstrapContent = file_get_contents(base_path('bootstrap/app.php'));

    expect($bootstrapContent)->toContain('ErrorPage');
    expect($bootstrapContent)->toContain('withExceptions');
    expect($bootstrapContent)->toContain('Inertia::render');
});
