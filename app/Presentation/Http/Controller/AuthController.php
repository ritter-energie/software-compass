<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

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

final readonly class AuthController
{
    private const string SESSION_USER_ID = 'auth_user_id';

    #[Get('/login')]
    public function loginForm(Request $request): Response
    {
        if (query('users')->count()->execute() === 0) {
            return new Redirect('/setup');
        }


        return new Ok(view('../../View/auth/login.view.php'));
    }

    #[Post('/login')]
    public function login(Request $request): Response
    {
        if (query('users')->count()->execute() === 0) {
            return new Redirect('/setup');
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/login')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $username = trim((string) $request->get('username', ''));
        $password = (string) $request->get('password', '');

        if ($username === '' || $password === '') {
            return new Redirect('/login')->flash('error', Translator::translate('auth.error.required_fields'));
        }

        $user = query('users')->select()->whereField('username', $username)->whereField('is_active', true)->first();
        if ($user === null || ! password_verify($password, (string) $user['password_hash'])) {
            return new Redirect('/login')->flash('error', Translator::translate('auth.error.invalid_credentials'));
        }

        get(Session::class)->set(self::SESSION_USER_ID, (int) $user['id']);

        $redirectTo = (string) ($request->get('redirect_to') ?? '/dashboard');
        if (! str_starts_with($redirectTo, '/')) {
            $redirectTo = '/dashboard';
        }

        return new Redirect($redirectTo)->flash('success', Translator::translate('auth.success.logged_in'));
    }

    #[Post('/logout')]
    public function logout(Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect('/dashboard')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        get(Session::class)->remove(self::SESSION_USER_ID);

        return new Redirect('/login')->flash('success', Translator::translate('auth.success.logged_out'));
    }
}

