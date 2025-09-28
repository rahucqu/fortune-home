<?php

declare(strict_types=1);

use App\Enums\ContactInquiryStatus;

test('guest can view contact form', function () {
    $response = $this->get(route('contact'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/contact'));
});

test('guest can submit contact form', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '123-456-7890',
        'type' => 'general',
        'message' => 'This is a test message from the contact form.',
    ];

    $response = $this->post(route('contact.store'), $data);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_inquiries', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '123-456-7890',
        'type' => 'general',
        'status' => ContactInquiryStatus::NEW->value,
        'message' => 'This is a test message from the contact form.',
    ]);
});

test('contact form validation rejects empty fields', function () {
    $data = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone' => '',
        'message' => '',
    ];

    $response = $this->post(route('contact.store'), $data);

    $response->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone', 'message']);
});

test('contact form validation rejects invalid email', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'not-an-email',
        'phone' => '123-456-7890',
        'type' => 'general',
        'message' => 'This is a test message.',
    ];

    $response = $this->post(route('contact.store'), $data);

    $response->assertSessionHasErrors(['email']);
});

test('contact form is rate limited', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '123-456-7890',
        'type' => 'general',
        'message' => 'This is a test message.',
    ];

    // Submit multiple times to trigger rate limiting
    $this->post(route('contact.store'), $data);
    $this->post(route('contact.store'), $data);
    $this->post(route('contact.store'), $data);
    $response = $this->post(route('contact.store'), $data);

    // The 4th submission should be rate limited (3 per minute allowed)
    $response->assertStatus(429); // Too Many Requests
});
