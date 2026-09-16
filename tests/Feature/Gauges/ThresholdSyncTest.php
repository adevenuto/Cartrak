<?php

declare(strict_types=1);

/*
 * The "due soon" threshold exists in two languages.
 *
 * config('vehicles.gauges.soon') is authoritative: it decides the status
 * every gauge in the app renders, and the status the reminder emails are sent
 * against. The calibrate screen needs its own copy because it previews a status
 * for an answer that has not been saved yet, so there is no server-computed
 * status to read.
 *
 * If the two drift, the preview tells the user one thing and the saved gauge
 * shows another — with every check passing. Hence this test.
 */

test('the calibrate preview threshold matches the configured one', function () {
    $ts = file_get_contents(resource_path('js/lib/gauge.ts'));

    expect($ts)->toContain('SOON_THRESHOLD');

    preg_match('/SOON_THRESHOLD\s*=\s*([0-9.]+)/', $ts, $matches);

    expect($matches[1] ?? null)->not->toBeNull(
        'Could not read SOON_THRESHOLD from resources/js/lib/gauge.ts.',
    );

    expect((float) $matches[1])->toBe(
        (float) config('vehicles.gauges.soon'),
        'resources/js/lib/gauge.ts SOON_THRESHOLD must equal '
        .'config(\'vehicles.gauges.soon\'), or the calibrate preview '
        .'disagrees with the status the server computes on save.',
    );
});
