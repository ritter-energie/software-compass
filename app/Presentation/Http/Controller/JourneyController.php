<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Diagram\DiagramService;
use App\Application\Journey\CreateJourneyCommand;
use App\Application\Journey\JourneyService;
use App\Application\Journey\UpdateJourneyCommand;
use App\Domain\Component\ComponentRepository;
use App\Domain\Journey\JourneyRepository;
use App\Domain\Journey\JourneyStepComponent;
use App\Domain\Person\PersonRepository;
use App\Infrastructure\Persistence\LookupRepository;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Presentation\ViewModel\JourneyListItemViewModel;
use App\Presentation\ViewModel\JourneyStepAssignmentViewModel;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use RuntimeException;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\WithMiddleware;

use function Tempest\view;

#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class JourneyController
{
    public function __construct(
        private JourneyService $journeyService,
        private JourneyRepository $journeys,
        private PersonRepository $people,
        private LookupRepository $lookups,
        private ComponentRepository $components,
        private DiagramService $diagrams,
    ) {}

    #[Get('/journeys')]
    public function index(): Response
    {
        return new Ok(view(
            '../../View/journeys/index.view.php',
            journeys: $this->journeyListItems($this->journeyService->all(), $this->people->all()),
        ));
    }

    #[Get('/journeys/create')]
    public function create(): Response
    {
        return new Ok($this->formView('../../View/journeys/create.view.php'));
    }

    #[Post('/journeys')]
    public function store(Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect('/journeys/create')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return new Redirect('/journeys/create')->flash('error', Translator::translate('flash.error.name_required'));
        }

        $journey = $this->journeyService->create(new CreateJourneyCommand(
            name: $name,
            description: $this->stringOrNull($request->get('description')),
            ownerId: $this->intOrNull($request->get('owner_id')),
            ownerTeamId: $this->intOrNull($request->get('owner_team_id')),
            statusId: (int) $request->get('status_id'),
            sortOrder: (int) ($request->get('sort_order') ?? 0),
        ));

        return new Redirect('/journeys/' . $journey->id())->flash('success', Translator::translate('flash.success.journey_created'));
    }

    #[Get('/journeys/{id}')]
    public function show(int $id): Response
    {
        try {
            $journey = $this->journeyService->detail($id);
        } catch (RuntimeException) {
            return new Redirect('/journeys')->flash('error', Translator::translate('flash.error.journey_not_found'));
        }

        $steps = $this->journeyService->stepsForJourney($id);
        $components = $this->components->all();
        $assignments = [];
        foreach ($steps as $step) {
            $stepId = (int) $step->id();
            $assignments[$stepId] = $this->assignmentListItems($this->journeyService->componentsForStep($stepId), $components);
        }

        return new Ok(view(
            '../../View/journeys/show.view.php',
            journey: $journey,
            steps: $steps,
            assignments: $assignments,
            ownerName: $this->personName($this->people->all(), $journey->ownerId()),
            ownerTeamName: $this->lookupName($this->lookups->teams(), $journey->ownerTeamId()),
            components: $components,
            roles: JourneyStepComponent::validRoles(),
            mermaid: $this->diagrams->journeyDiagram($id),
        ));
    }

    #[Get('/journeys/{id}/edit')]
    public function edit(int $id): Response
    {
        $journey = $this->journeys->findById($id);
        if ($journey === null) {
            return new Redirect('/journeys')->flash('error', Translator::translate('flash.error.journey_not_found'));
        }

        return new Ok($this->formView('../../View/journeys/edit.view.php', $journey));
    }

    #[Post('/journeys/{id}')]
    public function update(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/journeys/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return new Redirect("/journeys/{$id}/edit")->flash('error', Translator::translate('flash.error.name_required'));
        }

        $this->journeyService->update(new UpdateJourneyCommand(
            id: $id,
            name: $name,
            description: $this->stringOrNull($request->get('description')),
            ownerId: $this->intOrNull($request->get('owner_id')),
            ownerTeamId: $this->intOrNull($request->get('owner_team_id')),
            statusId: (int) $request->get('status_id'),
            sortOrder: (int) ($request->get('sort_order') ?? 0),
        ));

        return new Redirect("/journeys/{$id}")->flash('success', Translator::translate('flash.success.journey_updated'));
    }

    #[Post('/journeys/{id}/delete')]
    public function delete(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/journeys/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $this->journeyService->delete($id);

        return new Redirect('/journeys')->flash('success', Translator::translate('flash.success.journey_deleted'));
    }

    private function formView(string $view, mixed $journey = null): mixed
    {
        return view($view, journey: $journey, statuses: $this->lookups->componentStatuses(), people: $this->people->allActive(), teams: $this->lookups->teams());
    }

    /**
     * @param \App\Domain\Journey\Journey[] $journeys
     * @param \App\Domain\Person\Person[] $people
     * @return JourneyListItemViewModel[]
     */
    private function journeyListItems(array $journeys, array $people): array
    {
        return array_map(
            fn ($journey): JourneyListItemViewModel => new JourneyListItemViewModel(
                id: (int) $journey->id(),
                name: $journey->name(),
                ownerName: $this->personName($people, $journey->ownerId()),
                ownerTeamName: $this->lookupName($this->lookups->teams(), $journey->ownerTeamId()),
                statusId: $journey->statusId(),
                sortOrder: $journey->sortOrder(),
            ),
            $journeys,
        );
    }

    /**
     * @param \App\Domain\Journey\JourneyStepComponent[] $assignments
     * @param \App\Domain\Component\Component[] $components
     * @return JourneyStepAssignmentViewModel[]
     */
    private function assignmentListItems(array $assignments, array $components): array
    {
        return array_map(
            fn ($assignment): JourneyStepAssignmentViewModel => new JourneyStepAssignmentViewModel(
                assignment: $assignment,
                componentName: $this->componentName($components, $assignment->componentId()),
            ),
            $assignments,
        );
    }

    /** @param \App\Domain\Component\Component[] $components */
    private function componentName(array $components, int $id): string
    {
        foreach ($components as $component) {
            if ($component->id() === $id) {
                return $component->name();
            }
        }

        return 'C' . $id;
    }

    /** @param \App\Domain\Person\Person[] $people */
    private function personName(array $people, ?int $id): string
    {
        if ($id === null) {
            return '—';
        }

        foreach ($people as $person) {
            if ($person->id() === $id) {
                return $person->name();
            }
        }

        return '—';
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function lookupName(array $rows, ?int $id): string
    {
        if ($id === null) {
            return '—';
        }

        foreach ($rows as $row) {
            if ((int) $row['id'] === $id) {
                return (string) $row['name'];
            }
        }

        return '—';
    }

    private function stringOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));
        return $trimmed === '' ? null : $trimmed;
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }
}
