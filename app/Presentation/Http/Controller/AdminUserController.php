<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\User\AdminUserService;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Infrastructure\Security\CurrentUser;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use InvalidArgumentException;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\WithMiddleware;

use function Tempest\view;

#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class AdminUserController
{
    public function __construct(
        private AdminUserService $users,
    ) {}

    #[Get('/admin/users')]
    public function index(): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        return new Ok(view('../../View/admin/users/index.view.php', users: $this->users->users()));
    }

    #[Get('/admin/users/create')]
    public function create(): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        return new Ok(view(
            '../../View/admin/users/create.view.php',
            roles: $this->users->availableRoles(),
            defaultLocale: $this->users->defaultLocale(),
        ));
    }

    #[Post('/admin/users')]
    public function store(Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/admin/users/create')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $name = trim((string) $request->get('name', ''));
        $email = $this->stringOrNull($request->get('email'));
        $username = trim((string) $request->get('username', ''));
        $password = (string) $request->get('password', '');
        $passwordConfirmation = (string) $request->get('password_confirmation', '');
        $role = (string) ($request->get('role') ?? 'viewer');
        $locale = (string) ($request->get('preferred_locale') ?? $this->users->defaultLocale());

        if ($name === '' || $username === '' || $password === '') {
            return new Redirect('/admin/users/create')->flash('error', Translator::translate('users.error.required_fields'));
        }

        if ($password !== $passwordConfirmation) {
            return new Redirect('/admin/users/create')->flash('error', Translator::translate('setup.error.password_mismatch'));
        }

        if (strlen($password) < 8) {
            return new Redirect('/admin/users/create')->flash('error', Translator::translate('setup.error.password_too_short'));
        }

        try {
            $this->users->createUser(
                name: $name,
                email: $email,
                username: $username,
                password: $password,
                locale: $locale,
                role: $role,
            );
        } catch (InvalidArgumentException $exception) {
            return new Redirect('/admin/users/create')->flash('error', $exception->getMessage());
        }

        return new Redirect('/admin/users')->flash('success', Translator::translate('users.success.created'));
    }

    #[Get('/admin/users/{id}/edit')]
    public function edit(int $id): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        $user = $this->users->userById($id);
        if ($user === null) {
            return new Redirect('/admin/users')->flash('error', Translator::translate('flash.error.user_not_found'));
        }

        return new Ok(view(
            '../../View/admin/users/edit.view.php',
            user: $user,
            roles: $this->users->availableRoles(),
        ));
    }

    #[Post('/admin/users/{id}')]
    public function update(int $id, Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect("/admin/users/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $name = trim((string) $request->get('name', ''));
        $email = $this->stringOrNull($request->get('email'));
        $username = trim((string) $request->get('username', ''));
        $password = $this->stringOrNull($request->get('password'));
        $passwordConfirmation = (string) $request->get('password_confirmation', '');
        $role = (string) ($request->get('role') ?? 'viewer');
        $locale = (string) ($request->get('preferred_locale') ?? 'en');

        if ($name === '' || $username === '') {
            return new Redirect("/admin/users/{$id}/edit")->flash('error', Translator::translate('users.error.required_fields'));
        }

        if ($password !== null && $password !== '' && $password !== $passwordConfirmation) {
            return new Redirect("/admin/users/{$id}/edit")->flash('error', Translator::translate('setup.error.password_mismatch'));
        }

        if ($password !== null && $password !== '' && strlen($password) < 8) {
            return new Redirect("/admin/users/{$id}/edit")->flash('error', Translator::translate('setup.error.password_too_short'));
        }

        try {
            $this->users->updateUser(
                id: $id,
                name: $name,
                email: $email,
                username: $username,
                password: $password,
                locale: $locale,
                role: $role,
            );
        } catch (InvalidArgumentException $exception) {
            return new Redirect("/admin/users/{$id}/edit")->flash('error', $exception->getMessage());
        }

        return new Redirect('/admin/users')->flash('success', Translator::translate('users.success.updated'));
    }

    #[Post('/admin/users/{id}/toggle-active')]
    public function toggleActive(int $id, Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/admin/users')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $this->users->toggleActive($id);

        return new Redirect('/admin/users')->flash('success', Translator::translate('users.success.status_updated'));
    }

    private function stringOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed === '' ? null : $trimmed;
    }
}
