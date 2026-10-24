<?php

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

test('guests cannot store a message', function () {
    $response = $this->postJson(route('api.v1.messages.store'), [
        'body' => 'Hello world',
    ]);

    $response->assertUnauthorized();

    expect(Message::count())->toBe(0);
});

test('authenticated users can store a message', function () {
    Event::fake([MessageSent::class]);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson(route('api.v1.messages.store'), [
        'body' => 'Hello world',
        'user_id' => $otherUser->id,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('body', 'Hello world')
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('user.id', $user->id);

    $message = Message::sole();

    expect($message->user_id)->toBe($user->id)
        ->and($message->body)->toBe('Hello world');

    Event::assertDispatched(MessageSent::class, fn (MessageSent $event) => $event->message->is($message));
});

test('message body is required', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson(route('api.v1.messages.store'), []);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['body' => 'The body field is required.']);

    expect(Message::count())->toBe(0);
});

test('message body must not exceed 5000 characters', function () {
    Sanctum::actingAs(User::factory()->create());

    $response = $this->postJson(route('api.v1.messages.store'), [
        'body' => str_repeat('a', 5001),
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['body' => 'The body field must not be greater than 5000 characters.']);

    expect(Message::count())->toBe(0);
});
