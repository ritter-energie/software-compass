<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Setup\DatabaseUpdateService;
use App\Application\Setup\SetupService;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Session\Session;
use Tempest\Router\Get;
use Tempest\Router\Post;

use function Tempest\Database\query;
use function Tempest\get;
use function Tempest\view;

final readonly class AuthController {
    private const string SESSION_USER_ID = 'auth_user_id';

    public function __construct(
        private SetupService $setup,
        private DatabaseUpdateService $databaseUpdates,
    ) {}

    #[Get('/login')]
    public function loginForm(Request $request): Response {
        if ($this->setup->needsSetup()) {
            return new Redirect('/setup');
        }

        return new Ok(view('../../View/auth/login.view.php'));
    }

    #[Post('/login')]
    public function login(Request $request): Response {
        if ($this->setup->needsSetup()) {
            return new Redirect('/setup');
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/login')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $email = strtolower(trim((string) $request->get('email', '')));
        $password = (string) $request->get('password', '');

        if ($email === '' || $password === '') {
            return new Redirect('/login')->flash('error', Translator::translate('auth.error.required_fields'));
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new Redirect('/login')->flash('error', Translator::translate('auth.error.invalid_credentials'));
        }

        $user = $this->findActiveUserByEmail($email);
        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            return new Redirect('/login')->flash('error', Translator::translate('auth.error.invalid_credentials'));
        }

        $userId = (int) $user['id'];
        get(Session::class)->set(self::SESSION_USER_ID, $userId);

        $redirectTo = (string) ($request->get('redirect_to') ?? '/dashboard');
        if (! str_starts_with($redirectTo, '/')) {
            $redirectTo = '/dashboard';
        }

        $response = new Redirect($redirectTo);
        $response->flash('success', Translator::translate('auth.success.logged_in'));

        if ($this->shouldShowDatabaseUpdateNotice($userId)) {
            $response->flash('warning', Translator::translate('database_update.login_notice'));
        }

        return $response;
    }

    #[Post('/logout')]
    public function logout(Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect('/dashboard')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        get(Session::class)->remove(self::SESSION_USER_ID);

        return new Redirect('/login')->flash('success', Translator::translate('auth.success.logged_out'));
    }

    /** @return array<string, mixed>|null */
    private function findActiveUserByEmail(string $email): ?array {
        $people = query('people')->select()->whereField('email', $email)->all();
        if ($people === []) {
            return null;
        }

        $personIds = array_map(static fn (array $person): int => (int) $person['id'], $people);

        $users = query('users')
            ->select()
            ->whereIn('person_id', $personIds)
            ->whereField('is_active', true)
            ->all();

        return count($users) === 1 ? $users[0] : null;
    }

    private function shouldShowDatabaseUpdateNotice(int $userId): bool {
        if (! $this->isAdmin($userId)) {
            return false;
        }

        return $this->databaseUpdates->status()->hasPendingMigrations();
    }

    private function isAdmin(int $userId): bool {
        $roleIds = array_map(
            static fn (array $row): int => (int) $row['role_id'],
            query('user_roles')->select()->whereField('user_id', $userId)->all(),
        );

        if ($roleIds === []) {
            return false;
        }

        return query('roles')
            ->select()
            ->whereIn('id', $roleIds)
            ->whereField('name', 'admin')
            ->first() !== null;
    }
}
