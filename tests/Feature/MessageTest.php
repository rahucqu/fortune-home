<?php

declare(strict_types=1);

use App\Mail\MessageReceived;
use App\Mail\MessageReply;
use App\Models\Message;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    // Create a basic property type to avoid foreign key constraint issues
    PropertyType::create([
        'name' => 'House',
        'category' => 'residential',
        'slug' => 'house',
        'description' => 'Single family house',
        'is_active' => true,
        'sort_order' => 1,
    ]);
});

describe('Message Management', function () {
    it('can send a message to an agent from property page', function () {
        Mail::fake();

        // Create agent and property
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        $property = Property::factory()->create([
            'agent_id' => $agent->id,
        ]);

        // Send message as guest
        $response = $this->post(route('messages.store', ['morph_type' => 'property', 'morph_id' => $property->id]), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Inquiry about property',
            'message' => 'I am interested in this property.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify message was created
        $this->assertDatabaseHas('messages', [
            'to_user_id' => $agent->id,
            'subject' => 'Inquiry about property',
            'message' => 'I am interested in this property.',
            'messageable_type' => Property::class,
            'messageable_id' => $property->id,
        ]);

        // Verify email was queued
        Mail::assertQueued(MessageReceived::class, function ($mail) use ($agent) {
            return $mail->hasTo($agent->email);
        });
    });

    it('can send a message to an agent directly', function () {
        Mail::fake();

        // Create agent
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        // Send message as guest
        $response = $this->post(route('messages.store', ['morph_type' => 'agent', 'morph_id' => $agent->id]), [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'General inquiry',
            'message' => 'I would like to know more about your services.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify message was created
        $this->assertDatabaseHas('messages', [
            'to_user_id' => $agent->id,
            'subject' => 'General inquiry',
            'message' => 'I would like to know more about your services.',
            'messageable_type' => User::class,
            'messageable_id' => $agent->id,
        ]);

        // Verify email was queued
        Mail::assertQueued(MessageReceived::class);
    });

    it('authenticated users can send messages without providing name and email', function () {
        Mail::fake();

        // Create users
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $recipient->assignRole('agent');

        // Login as sender
        $this->actingAs($sender);

        // Send message
        $response = $this->post(route('messages.store', ['morph_type' => 'agent', 'morph_id' => $recipient->id]), [
            'subject' => 'Authenticated user message',
            'message' => 'This is from an authenticated user.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify message was created with authenticated user
        $this->assertDatabaseHas('messages', [
            'from_user_id' => $sender->id,
            'to_user_id' => $recipient->id,
            'subject' => 'Authenticated user message',
            'message' => 'This is from an authenticated user.',
        ]);

        Mail::assertQueued(MessageReceived::class);
    });

    it('validates required fields when sending messages', function () {
        $agent = User::factory()->create();
        $agent->assignRole('agent');

        // Test without message (required field)
        $response = $this->post(route('messages.store', ['morph_type' => 'agent', 'morph_id' => $agent->id]), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            // missing message
        ]);

        $response->assertSessionHasErrors(['message']);

        // Test guest without name and email
        $response = $this->post(route('messages.store', ['morph_type' => 'agent', 'morph_id' => $agent->id]), [
            'message' => 'Test message without name and email',
        ]);

        $response->assertSessionHasErrors(['name', 'email']);
    });
});

describe('Message Conversations', function () {
    it('can view message inbox for admin users', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);

        $response = $this->get(route('admin.messages.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($assert) => $assert->component('admin/messages/index')
            ->has('messages')
            ->has('conversations')
        );
    });

    it('shows conversations grouped by users', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $partner = User::factory()->create();

        // Create multiple messages between users
        Message::factory()->count(3)->create([
            'from_user_id' => $admin->id,
            'to_user_id' => $partner->id,
        ]);

        Message::factory()->count(2)->create([
            'from_user_id' => $partner->id,
            'to_user_id' => $admin->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.messages.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($assert) => $assert->component('admin/messages/index')
            ->has('conversations', 1)
            ->where('conversations.0.total_messages', 5)
            ->where('conversations.0.partner.id', $partner->id)
        );
    });

    it('can view individual conversation', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $partner = User::factory()->create();

        // Create messages
        Message::factory()->count(5)->create([
            'from_user_id' => $admin->id,
            'to_user_id' => $partner->id,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.messages.conversation', ['user' => $partner->id]));

        $response->assertSuccessful();
        $response->assertInertia(fn ($assert) => $assert->component('admin/messages/conversation')
            ->has('messages')
            ->where('conversation_partner.id', $partner->id)
        );
    });

    it('marks messages as read when viewing conversation', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $partner = User::factory()->create();

        // Create unread messages from partner to admin
        $messages = Message::factory()->count(3)->create([
            'from_user_id' => $partner->id,
            'to_user_id' => $admin->id,
            'is_read' => false,
        ]);

        $this->actingAs($admin);

        // View conversation
        $response = $this->get(route('admin.messages.conversation', ['user' => $partner->id]));

        $response->assertSuccessful();

        // Verify messages are now marked as read
        foreach ($messages as $message) {
            $message->refresh();
            expect($message->is_read)->toBeTrue();
            expect($message->read_at)->not()->toBeNull();
        }
    });

    it('can reply to messages', function () {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $partner = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->post(route('admin.messages.reply'), [
            'to_user_id' => $partner->id,
            'subject' => 'Reply subject',
            'message' => 'This is a reply message.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify reply was created
        $this->assertDatabaseHas('messages', [
            'from_user_id' => $admin->id,
            'to_user_id' => $partner->id,
            'subject' => 'Reply subject',
            'message' => 'This is a reply message.',
        ]);

        // Verify email was queued
        Mail::assertQueued(MessageReply::class, function ($mail) use ($partner) {
            return $mail->hasTo($partner->email);
        });
    });

    it('can mark individual messages as read/unread', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $message = Message::factory()->create([
            'to_user_id' => $admin->id,
            'is_read' => false,
        ]);

        $this->actingAs($admin);

        // Mark as read
        $response = $this->patch(route('admin.messages.mark-read', ['message' => $message->id]));

        $response->assertSuccessful();
        $message->refresh();
        expect($message->is_read)->toBeTrue();

        // Mark as unread
        $response = $this->patch(route('admin.messages.mark-unread', ['message' => $message->id]));

        $response->assertSuccessful();
        $message->refresh();
        expect($message->is_read)->toBeFalse();
    });

    it('can mark all messages as read', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Create unread messages
        Message::factory()->count(5)->create([
            'to_user_id' => $admin->id,
            'is_read' => false,
        ]);

        $this->actingAs($admin);

        $response = $this->patch(route('admin.messages.mark-all-read'));

        $response->assertSuccessful();

        // Verify all messages are marked as read
        $unreadCount = Message::where('to_user_id', $admin->id)->unread()->count();
        expect($unreadCount)->toBe(0);
    });

    it('can delete own messages', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $message = Message::factory()->create([
            'from_user_id' => $admin->id,
        ]);

        $this->actingAs($admin);

        $response = $this->delete(route('admin.messages.destroy', ['message' => $message->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    });

    it('requires authentication for message management routes', function () {
        // Test message inbox
        $response = $this->get(route('admin.messages.index'));
        $response->assertRedirect(route('login'));

        // Test conversation
        $response = $this->get(route('admin.messages.conversation', ['user' => 1]));
        $response->assertRedirect(route('login'));

        // Test reply
        $response = $this->post(route('admin.messages.reply'), []);
        $response->assertRedirect(route('login'));

        // Test mark as read
        $response = $this->patch(route('admin.messages.mark-read', ['message' => 1]));
        $response->assertRedirect(route('login'));
    });
});
