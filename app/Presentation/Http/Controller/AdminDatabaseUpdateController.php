<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Infrastructure\Security\BasicAuthMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\WithMiddleware;

#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class AdminDatabaseUpdateController
{
    #[Get('/admin/database')]
    public function index(): Response
    {
        return new Redirect('/admin/settings');
    }

    #[Post('/admin/database/update')]
    public function update(Request $request): Response
    {
        return new Redirect('/admin/settings');
    }
}
