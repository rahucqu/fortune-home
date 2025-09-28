<?php

declare(strict_types=1);

it('validates API v1 response structure format', function () {
    // Test the response macro structure - parameters: ($data, $message, $code, ...$additionalData)
    $response = response()->success(['test' => 'data'], 'Test message', 200);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys([
        'success', 'message', 'data', 'code', 'timestamp',
    ]);
    expect($responseData['success'])->toBe(true);
    expect($responseData['message'])->toBe('Test message');
    expect($responseData['data'])->toBe(['test' => 'data']);
    expect($responseData['code'])->toBe(200);
});

it('validates API v1 error response structure', function () {
    // Test error macro - parameters: ($errors, $message, $code, ...$additionalData)
    $response = response()->error(['field' => 'required'], 'Validation failed', 422);

    $responseData = $response->getData(true);

    expect($responseData)->toHaveKeys([
        'success', 'message', 'errors', 'code', 'timestamp',
    ]);
    expect($responseData['success'])->toBe(false);
    expect($responseData['message'])->toBe('Validation failed');
    expect($responseData['errors'])->toBe(['field' => 'required']);
    expect($responseData['code'])->toBe(422);
});

it('ensures timestamp format is ISO 8601', function () {
    $response = response()->success(null, 'Test message');
    $responseData = $response->getData(true);

    expect($responseData['timestamp'])->toMatch('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/');
});
