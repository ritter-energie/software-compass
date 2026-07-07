<?php

declare(strict_types=1);

namespace App\Shared\Support;

use Tempest\Http\Request;
use Tempest\Http\Session\Session;

use function Tempest\get;

/** Small view/controller helper around Tempest's session CSRF token. */
final readonly class Csrf {
    public const string FIELD = Session::CSRF_TOKEN_KEY;

    public static function token(): string {
        return get(Session::class)->token;
    }

    public static function input(): string {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD,
            htmlspecialchars(self::token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        );
    }

    public static function isValid(Request $request): bool {
        $submitted = (string) ($request->get(self::FIELD) ?? '');

        return $submitted !== '' && hash_equals(self::token(), $submitted);
    }
}
