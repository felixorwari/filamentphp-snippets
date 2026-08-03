<?php

declare(strict_types=1);

use Illuminate\Support\Str;

/**
 * Normalizes a raw search string into lowercase, deduplicated tokens for
 * multi-term "AND" matching.
 *
 * Strips everything outside \pL (Unicode letters) and \pN (Unicode digits)
 * before splitting on whitespace. This has the useful side effect of
 * stripping flag emoji (Unicode regional-indicator symbols are category
 * "So", not \pL/\pN) — so a query built from a full "🇰🇪 Kenya"-style label
 * still tokenizes down to just ["kenya"].
 *
 * Pure normalization only — consumers decide how tokens are applied
 * (SQL LIKE chaining, in-memory str_contains, etc.), since that differs
 * by context.
 */
final class SearchTokenizer
{
    /**
     * @return array<int, string>
     */
    public static function tokenize(string $value): array
    {
        $normalized = Str::of($value)
            ->lower()
            ->replaceMatches('/[^\pL\pN]+/u', ' ')
            ->trim();

        return collect(
            preg_split('/\s+/', (string) $normalized, -1, PREG_SPLIT_NO_EMPTY)
        )->unique()->values()->all();
    }
}
