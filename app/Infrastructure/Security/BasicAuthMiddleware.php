<?php
declare(strict_types=1);

namespace App\Infrastructure\Security;

use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

use function Tempest\Database\query;
use function Tempest\get;

/**
 * Session-based authentication middleware backed by the `users` table.
 *
 * This is intentionally simple for the MVP, but keeps authentication inside
 * the application so audit logs can later resolve the authenticated user to a
 * person via `users.person_id`.
 */
final readonly class BasicAuthMiddleware implements HttpMiddleware
{
    private const string SESSION_USER_ID = 'auth_user_id';

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        CurrentUser::clear();

        if (str_starts_with($request->path, '/login') || str_starts_with($request->path, '/setup')) {
            return $next($request);
        }

        // First-run UX: if no users exist yet, route protected pages to setup.
        if (query('users')->count()->execute() === 0) {
            return new Redirect('/setup');
        }

        $sessionUserId = $request->getSessionValue(self::SESSION_USER_ID);
        if ($sessionUserId === null) {
            return new Redirect('/login');
        }

        $user = query('users')
            ->select()
            ->whereField('id', (int) $sessionUserId)
            ->whereField('is_active', true)
            ->first();

        if ($user === null) {
            get(Session::class)->remove(self::SESSION_USER_ID);

            return new Redirect('/login');
        }

        $roleIds = array_map(
            static fn (array $row): int => (int) $row['role_id'],
            query('user_roles')->select()->whereField('user_id', (int) $user['id'])->all(),
        );

        $roles = $roleIds === []
            ? []
            : array_map(
                static fn (array $row): string => (string) $row['name'],
                query('roles')->select()->whereIn('id', $roleIds)->all(),
            );

        $person = $user['person_id'] !== null
            ? query('people')->select()->whereField('id', (int) $user['person_id'])->first()
            : null;

        $displayName = $person !== null
            ? (string) $person['name']
            : 'Account #' . (string) $user['id'];

        CurrentUser::authenticate(
            (int) $user['id'],
            $user['person_id'] !== null ? (int) $user['person_id'] : null,
            (string) ($user['preferred_locale'] ?? 'en'),
            $roles,
            $displayName,
        );

        return $next($request);
    }
}
