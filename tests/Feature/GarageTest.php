<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('garage'));

    $response->assertRedirect(route('login'));
});

test('unverified users are redirected to the verification notice', function () {
    $this->actingAs(User::factory()->unverified()->create());

    $response = $this->get(route('garage'));

    $response->assertRedirect(route('verification.notice'));
});

test('verified users can visit the garage', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('garage'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('Garage'));
});
