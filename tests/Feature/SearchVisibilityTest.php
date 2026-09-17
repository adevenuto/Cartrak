<?php

/*
 * The site is pre-launch and should stay out of search results.
 *
 * Two mechanisms, because they do different jobs: robots.txt asks well-behaved
 * crawlers not to fetch pages, while the meta tag asks them not to index what
 * they already fetched — a URL discovered from a link elsewhere can be indexed
 * despite robots.txt.
 *
 * Both are removed at launch, and these tests are what will fail to remind
 * whoever does it that there are two places, not one.
 */

test('every page asks search engines not to index it', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
});

test('robots.txt disallows crawling', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('User-agent: *')
        ->and($robots)->toContain('Disallow: /');
});
