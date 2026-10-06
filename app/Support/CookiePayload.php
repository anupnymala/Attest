<?php

namespace App\Support;

/**
 * Normalization for cookie payloads that arrive from the outside world.
 *
 * The canonical stored form is snake_case with whole-second expires and a
 * same_site of "Strict" | "Lax" | "None". Real-world cookie JSON comes in
 * other shapes that Playwright itself happily accepts:
 *
 *  - Playwright storageState / context.cookies(): camelCase keys
 *    (sameSite, httpOnly) and a float `expires` (epoch seconds with
 *    sub-second precision).
 *  - Chrome DevTools / EditThisCookie exports: lowercase sameSite spellings
 *    ("lax", "strict", "no_restriction", "unspecified") and `expirationDate`
 *    instead of `expires`.
 *
 * normalize() maps all of these onto the canonical form before validation
 * runs, so a cookie snapshot pasted into the suite settings (or uploaded
 * through the MCP tool) never bounces on shape alone.
 */
class CookiePayload
{
    /**
     * SameSite spellings seen in the wild, mapped onto the values
     * Playwright accepts. "unspecified" (Chrome's marker for a cookie
     * without a SameSite attribute) maps to null — no attribute.
     */
    private const SAME_SITE_ALIASES = [
        'strict' => 'Strict',
        'lax' => 'Lax',
        'none' => 'None',
        'no_restriction' => 'None',
        'unspecified' => null,
    ];

    /**
     * Normalize a whole cookies array. Non-array entries are left in place
     * so validation reports them instead of silently dropping them.
     *
     * @param  array<int, mixed>  $cookies
     * @return array<int, mixed>
     */
    public static function normalize(array $cookies): array
    {
        return array_map(
            fn ($cookie) => is_array($cookie) ? self::normalizeCookie($cookie) : $cookie,
            array_values($cookies),
        );
    }

    /**
     * Map a single cookie onto the canonical snake_case form.
     *
     * @param  array<string, mixed>  $cookie
     * @return array<string, mixed>
     */
    public static function normalizeCookie(array $cookie): array
    {
        // camelCase aliases (Playwright shape) — only when the snake_case
        // key is absent, so an explicit snake_case value always wins.
        if (! array_key_exists('http_only', $cookie) && array_key_exists('httpOnly', $cookie)) {
            $cookie['http_only'] = $cookie['httpOnly'];
        }
        if (! array_key_exists('same_site', $cookie) && array_key_exists('sameSite', $cookie)) {
            $cookie['same_site'] = $cookie['sameSite'];
        }

        // Chrome DevTools / EditThisCookie spell the epoch `expirationDate`.
        if (! array_key_exists('expires', $cookie) && array_key_exists('expirationDate', $cookie)) {
            $cookie['expires'] = $cookie['expirationDate'];
        }

        if (array_key_exists('same_site', $cookie)) {
            $cookie['same_site'] = self::normalizeSameSite($cookie['same_site']);
        }

        // Playwright emits expires as a float; the column stores whole
        // seconds. Cast before the integer validation rule sees it.
        if (isset($cookie['expires']) && is_numeric($cookie['expires'])) {
            $cookie['expires'] = (int) $cookie['expires'];
        }

        return $cookie;
    }

    /**
     * Map a sameSite value onto "Strict" | "Lax" | "None" | null. Values
     * that are already canonical (or unknown — let validation reject those
     * with a visible message) are returned unchanged.
     */
    private static function normalizeSameSite(mixed $value): mixed
    {
        if ($value === null || ! is_string($value)) {
            return $value;
        }

        $key = strtolower(trim($value));

        // array_key_exists, not ?? — "unspecified" legitimately maps to null.
        if (! array_key_exists($key, self::SAME_SITE_ALIASES)) {
            return $value;
        }

        return self::SAME_SITE_ALIASES[$key];
    }
}
