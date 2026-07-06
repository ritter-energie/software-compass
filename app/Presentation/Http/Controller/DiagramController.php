<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Diagram\ComponentDiagramFilter;
use App\Application\Diagram\DiagramService;
use App\Infrastructure\Security\BasicAuthMiddleware;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\WithMiddleware;

use function Tempest\view;

#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class DiagramController
{
    public function __construct(
        private DiagramService $diagrams,
    ) {}

    #[Get('/diagrams/components')]
    public function components(Request $request): Response
    {
        return new Ok(view('../../View/diagrams/components.view.php', mermaid: $this->diagrams->componentOverview($this->filter($request))));
    }

    #[Get('/diagrams/components/mermaid')]
    public function componentsMermaid(Request $request): Response
    {
        return new Ok($this->diagrams->componentOverview($this->filter($request)));
    }

    #[Get('/diagrams/journeys/{id}')]
    public function journey(int $id): Response
    {
        return new Ok(view('../../View/diagrams/journey.view.php', journeyId: $id, mermaid: $this->diagrams->journeyDiagram($id)));
    }

    #[Get('/diagrams/journeys/{id}/mermaid')]
    public function journeyMermaid(int $id): Response
    {
        return new Ok($this->diagrams->journeyDiagram($id));
    }

    private function filter(Request $request): ComponentDiagramFilter
    {
        return new ComponentDiagramFilter(
            componentId: $this->intOrNull($request->get('component_id')),
            statusId: $this->intOrNull($request->get('status_id')),
            criticalityId: $this->intOrNull($request->get('criticality_id')),
            componentTypeId: $this->intOrNull($request->get('component_type_id')),
            ownerId: $this->intOrNull($request->get('owner_id')),
            maxNodes: (int) ($request->get('max_nodes') ?? 80),
        );
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
