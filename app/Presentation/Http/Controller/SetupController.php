<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Setup\SetupService;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;

use function Tempest\view;

/** Public first-run setup flow for creating the first admin and network name. */
final readonly class SetupController
{
    public function __construct(
        private SetupService $setup,
    ) {}

    #[Get('/setup')]
    public function index(): Response
    {
        if (! $this->setup->needsSetup()) {
            return new Redirect('/dashboard');
        }

        return new Ok(view('../../View/setup/index.view.php'));
    }

    #[Post('/setup')]
    public function store(Request $request): Response
    {
        if (! $this->setup->needsSetup()) {
            return new Redirect('/dashboard');
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/setup')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $networkName = trim((string) $request->get('network_name', ''));
        $adminName = trim((string) $request->get('admin_name', ''));
        $adminEmail = $this->stringOrNull($request->get('admin_email'));
        $username = trim((string) $request->get('username', ''));
        $password = (string) $request->get('password', '');
        $passwordConfirmation = (string) $request->get('password_confirmation', '');
        $locale = (string) ($request->get('default_locale') ?? $request->get('preferred_locale') ?? 'en');

        if ($networkName === '' || $adminName === '' || $username === '' || $password === '') {
            return new Redirect('/setup')->flash('error', Translator::translate('setup.error.required_fields'));
        }

        if ($password !== $passwordConfirmation) {
            return new Redirect('/setup')->flash('error', Translator::translate('setup.error.password_mismatch'));
        }

        if (strlen($password) < 8) {
            return new Redirect('/setup')->flash('error', Translator::translate('setup.error.password_too_short'));
        }

        $this->setup->initialize(
            networkName: $networkName,
            adminName: $adminName,
            adminEmail: $adminEmail,
            username: $username,
            password: $password,
            locale: $locale,
        );

        return new Redirect('/login')->flash('success', Translator::translate('setup.success.completed'));
    }

    private function stringOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed === '' ? null : $trimmed;
    }
}
