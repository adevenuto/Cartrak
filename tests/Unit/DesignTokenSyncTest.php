<?php

declare(strict_types=1);

/*
 * Guards the one design-token failure that nothing else catches.
 *
 * app.css registers custom font sizes (text-metric, text-readout …) in its
 * @theme block. tailwind-merge only knows Tailwind's stock keys, so any key it
 * has not been told about is read as a text COLOUR: cn('text-metric
 * text-[var(--status-overdue)]') is then treated as two conflicting colours and
 * the real one is dropped.
 *
 * That fails silently. Pint, PHPStan, vp check and vue-tsc are all green while
 * the colour simply never applies. Hence a test.
 */

/** Unit tests do not bootstrap the container, so resource_path() is unavailable. */
function designResourcePath(string $relative): string
{
    return dirname(__DIR__, 2).'/resources/'.$relative;
}

/** Tailwind ships these; registering them would be wrong, not merely redundant. */
const STOCK_FONT_SIZES = [
    'xs', 'sm', 'base', 'lg', 'xl',
    '2xl', '3xl', '4xl', '5xl', '6xl', '7xl', '8xl', '9xl',
];

/**
 * @return list<string>
 */
function fontSizeKeysDeclaredInCss(): array
{
    $css = file_get_contents(designResourcePath('css/app.css'));

    // --text-body: 15px  → "body"; skips --text-body--line-height modifiers.
    preg_match_all('/^\s*--text-([a-z0-9-]+):/m', $css, $matches);

    $keys = array_filter(
        $matches[1],
        static fn (string $key): bool => ! str_contains($key, '--'),
    );

    return array_values(array_unique($keys));
}

/**
 * @return list<string>
 */
function fontSizeKeysRegisteredInJs(): array
{
    $ts = file_get_contents(designResourcePath('js/lib/utils.ts'));

    expect($ts)->toContain('DS_FONT_SIZES');

    $body = str($ts)->after('DS_FONT_SIZES = [')->before(']')->toString();

    preg_match_all("/'([a-z0-9-]+)'/", $body, $matches);

    return $matches[1];
}

it('registers every custom font size with tailwind-merge', function (): void {
    $declared = fontSizeKeysDeclaredInCss();
    $custom = array_values(array_diff($declared, STOCK_FONT_SIZES));
    $registered = fontSizeKeysRegisteredInJs();

    sort($custom);
    sort($registered);

    expect($custom)->not->toBeEmpty()
        ->and($registered)->toBe(
            $custom,
            'resources/js/lib/utils.ts DS_FONT_SIZES must list exactly the '
            .'non-stock --text-* keys in resources/css/app.css. Mismatch means '
            .'tailwind-merge will silently drop a colour beside that size.',
        );
});

it('does not register stock Tailwind font sizes', function (): void {
    expect(array_intersect(fontSizeKeysRegisteredInJs(), STOCK_FONT_SIZES))
        ->toBeEmpty('Stock keys are already known to tailwind-merge.');
});
