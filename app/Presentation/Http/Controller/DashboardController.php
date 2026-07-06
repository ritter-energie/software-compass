<?php
declare(strict_types=1);
namespace App\Presentation\Http\Controller;
use App\Application\Dashboard\DashboardService;
use App\Infrastructure\Security\BasicAuthMiddleware;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\WithMiddleware;
use function Tempest\view;
#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class DashboardController
{
    public function __construct(private DashboardService $dashboard) {}
    #[Get('/')]
    #[Get('/dashboard')]
    public function index(): Response
    {
        return new Ok(view('../../View/dashboard/index.view.php', dashboard: $this->dashboard->buildDashboard()));
    }
}
