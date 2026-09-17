<?php

/*
 * Every static file the page head links to must actually exist.
 *
 * This is here because the head referenced /favicon.svg for a while after the
 * file was replaced by a generated icon set — a silent 404 on every page load,
 * invisible in tests and in the browser unless you open the network tab.
 */

test('every static asset linked in the head exists', function () {
    $blade = file_get_contents(resource_path('views/app.blade.php'));

    preg_match_all('/(?:href|content)="(\/[^"{}]+)"/', $blade, $matches);

    $paths = array_values(array_unique($matches[1]));

    expect($paths)->not->toBeEmpty('Expected the head to link at least the favicon.');

    foreach ($paths as $path) {
        expect(file_exists(public_path(ltrim($path, '/'))))
            ->toBeTrue("resources/views/app.blade.php links {$path}, which does not exist in public/.");
    }
});

test('the web manifest is valid and branded', function () {
    $manifest = json_decode(file_get_contents(public_path('site.webmanifest')), true);

    expect($manifest)->toBeArray()
        ->and($manifest['name'] ?? '')->toBe('Ignition Index')
        ->and($manifest['icons'] ?? [])->not->toBeEmpty();

    // A manifest colour that disagrees with the page flashes white on launch.
    expect($manifest['background_color'] ?? null)->toBe('#f2f2f3')
        ->and($manifest['theme_color'] ?? null)->toBe('#f2f2f3');

    foreach ($manifest['icons'] as $icon) {
        expect(file_exists(public_path(ltrim($icon['src'], '/'))))
            ->toBeTrue("site.webmanifest lists {$icon['src']}, which does not exist in public/.");
    }
});
