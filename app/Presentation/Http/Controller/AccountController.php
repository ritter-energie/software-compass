<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Infrastructure\Security\CurrentUser;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\WithMiddleware;

use function Tempest\Database\query;
use function Tempest\view;

#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class AccountController {
    #[Get('/account')]
    public function index(): Response {
        $userId = CurrentUser::userId();
        if ($userId === null) {
            return new Redirect('/login');
        }

        $user = query('users')->select()->whereField('id', $userId)->first();
        if ($user === null) {
            return new Redirect('/login');
        }

        $person = null;
        if (($user['person_id'] ?? null) !== null) {
            $person = query('people')->select()->whereField('id', (int) $user['person_id'])->first();
        }

        return new Ok(view(
            '../../View/account/index.view.php',
            user: [
                'id' => (int) $user['id'],
                'preferred_locale' => (string) ($user['preferred_locale'] ?? 'en'),
                'person_name' => $person['name'] ?? null,
                'email' => $person['email'] ?? null,
                'roles' => CurrentUser::roles(),
            ],
        ));
    }
}
