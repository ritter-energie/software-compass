<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Setup\DatabaseUpdateService;
use App\Infrastructure\Persistence\AppSettingsRepository;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Infrastructure\Security\CurrentUser;
use App\Shared\Support\Csrf;
use App\Shared\Support\LocaleSupport;
use App\Shared\Support\Translator;
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
final readonly class AdminSettingsController
{
    public function __construct(
        private DatabaseUpdateService $databaseUpdates,
        private AppSettingsRepository $settings,
    ) {}

    #[Get('/admin/settings')]
    public function index(): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        return new Ok(view(
            '../../View/admin/settings/index.view.php',
            status: $this->databaseUpdates->status(validate: true),
            defaultLocale: $this->settings->defaultLocale(),
        ));
    }

    #[Post('/admin/settings/database-update')]
    public function updateDatabase(Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/admin/settings')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $result = $this->databaseUpdates->runPendingMigrations();

        if (! $result->successful()) {
            $message = $result->validationErrors !== []
                ? Translator::translate('database_update.error.validation_failed')
                : Translator::translate('database_update.error.update_failed');

            if ($result->errorMessage !== null && $result->errorMessage !== '') {
                $message .= ' ' . $result->errorMessage;
            }

            return new Redirect('/admin/settings')->flash('error', $message);
        }

        if ($result->migratedMigrations === []) {
            return new Redirect('/admin/settings')->flash('success', Translator::translate('database_update.success.none_needed'));
        }

        $message = str_replace(
            '{count}',
            (string) count($result->migratedMigrations),
            Translator::translate('database_update.success.applied'),
        );

        return new Redirect('/admin/settings')->flash('success', $message);
    }

    #[Post('/admin/settings/default-locale')]
    public function updateDefaultLocale(Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/admin/settings')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $locale = (string) ($request->get('default_locale') ?? 'en');
        if (! LocaleSupport::isSupported($locale)) {
            return new Redirect('/admin/settings')->flash('error', Translator::translate('language.unsupported'));
        }

        $this->settings->setDefaultLocale($locale);

        return new Redirect('/admin/settings')->flash('success', Translator::translate('app_settings.success.default_locale_updated'));
    }
}
