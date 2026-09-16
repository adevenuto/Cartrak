<?php

use Inertia\Testing\AssertableInertia as Assert;

/*
 * The landing page is the only screen a logged-out visitor sees, and its whole
 * job is to get them to the sign-up. These guard the things that would be
 * quietly wrong rather than obviously broken.
 */

test('the landing page renders for guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('the landing page is reachable without verifying an email', function () {
    // It sits outside the auth middleware entirely; a redirect here would mean
    // a visitor could never read the marketing page at all.
    $this->get(route('home'))->assertOk()->assertSee('Ignition Index', false);
});

test('the sign-up and sign-in routes the page links to exist', function () {
    $this->get(route('register'))->assertOk();
    $this->get(route('login'))->assertOk();
});
