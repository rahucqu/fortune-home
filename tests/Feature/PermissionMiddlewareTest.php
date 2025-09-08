<?php

declare(strict_types=1);

use App\Http\Middleware\PermissionMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'BlogRolePermissionSeeder']);
});

it('allows access when user has required permission', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');
    
    $middleware = new PermissionMiddleware();
    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);
    
    $response = $middleware->handle($request, fn () => response('OK'), 'view posts');
    
    expect($response->getContent())->toBe('OK');
});

it('denies access when user lacks required permission', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');
    
    $middleware = new PermissionMiddleware();
    $request = Request::create('/test');
    $request->setUserResolver(fn () => $user);
    
    try {
        $middleware->handle($request, fn () => response('OK'), 'publish posts');
        $this->fail('Expected 403 abort');
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        expect($e->getStatusCode())->toBe(403);
    }
});

it('redirects to login when user is not authenticated', function () {
    $middleware = new PermissionMiddleware();
    $request = Request::create('/test');
    $request->setUserResolver(fn () => null);
    
    $response = $middleware->handle($request, fn () => response('OK'), 'view posts');
    
    expect($response->getStatusCode())->toBe(302);
    expect($response->getTargetUrl())->toContain('login');
});
