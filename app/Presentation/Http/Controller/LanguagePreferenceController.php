<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Infrastructure\Security\CurrentUser;
use App\Shared\Support\Csrf;
use App\Shared\Support\LocaleSupport;
use App\Shared\Support\Translator;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Post;
use Tempest\Router\WithMiddleware;

use function Tempest\Database\query;

/** Stores the authenticated user's preferred UI language. */
#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class LanguagePreferenceController
{
    #[Post('/preferences/language')]
    public function update(Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect('/dashboard')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $locale = (string) ($request->get('locale') ?? 'en');
        if (! LocaleSupport::isSupported($locale)) {
            return new Redirect('/dashboard')->flash('error', Translator::translate('language.unsupported'));
        }

        $userId = CurrentUser::userId();
        if ($userId === null) {
            return new Redirect('/dashboard')->flash('error', Translator::translate('flash.error.no_authenticated_user'));
        }

        query('users')->update(preferred_locale: $locale)->whereField('id', $userId)->execute();
        CurrentUser::authenticate($userId, CurrentUser::personId(), $locale, CurrentUser::roles());

        return new Redirect((string) ($request->get('redirect_to') ?: '/dashboard'))->flash('success', Translator::translate('language.updated'));
    }
}
