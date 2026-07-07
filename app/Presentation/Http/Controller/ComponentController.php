<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Component\ComponentService;
use App\Application\Component\CreateComponentCommand;
use App\Application\Component\UpdateComponentCommand;
use App\Domain\Component\ComponentRepository;
use App\Domain\Component\ComponentSearchCriteria;
use App\Domain\Person\PersonRepository;
use App\Infrastructure\Persistence\LookupRepository;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use DateTimeImmutable;
use RuntimeException;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Http\Responses\Redirect;
use Tempest\Http\Status;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Tempest\Router\WithMiddleware;
use Traversable;

use function Tempest\view;

/**
 * HTTP entry points for managing components.
 *
 * This controller intentionally contains no business logic: it only
 * translates HTTP request data into {@see ComponentService} calls and
 * renders views, following the project's architecture rules.
 */
#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class ComponentController {
    public function __construct(
        private ComponentRepository $components,
        private ComponentService $componentService,
        private PersonRepository $people,
        private LookupRepository $lookups,
    ) {}

    #[Get('/components')]
    public function index(Request $request): Response {
        $criteria = new ComponentSearchCriteria(
            query: $this->stringOrNull($request->get('q')),
            componentTypeId: $this->intOrNull($request->get('component_type_id')),
            statusId: $this->intOrNull($request->get('status_id')),
            criticalityId: $this->intOrNull($request->get('criticality_id')),
            ownerId: $this->intOrNull($request->get('owner_id')),
            ownerTeamId: $this->intOrNull($request->get('owner_team_id')),
            environmentId: $this->intOrNull($request->get('environment_id')),
            isExternal: $this->boolOrNull($request->get('is_external')),
        );

        return new Ok(view(
            '../../View/components/index.view.php',
            components: $this->components->search($criteria),
            criteria: $criteria,
            componentTypes: $this->lookups->componentTypes(),
            statuses: $this->lookups->componentStatuses(),
            criticalityLevels: $this->lookups->criticalityLevels(),
            environments: $this->lookups->environments(),
            people: $this->people->allActive(),
            teams: $this->lookups->teams(),
        ));
    }

    #[Get('/components/create')]
    public function create(): Response {
        return new Ok(view(
            '../../View/components/create.view.php',
            componentTypes: $this->lookups->componentTypes(),
            statuses: $this->lookups->componentStatuses(),
            criticalityLevels: $this->lookups->criticalityLevels(),
            environments: $this->lookups->environments(),
            deploymentLocations: $this->lookups->deploymentLocations(),
            people: $this->people->allActive(),
            teams: $this->lookups->teams(),
            availableComponents: $this->components->all(),
        ));
    }

    #[Post('/components')]
    public function store(Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect('/components/create')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $name = trim((string) $request->get('name', ''));

        if ($name === '') {
            return new Redirect('/components/create')->flash('error', Translator::translate('flash.error.name_required'));
        }

        $documentationUrl = $this->urlOrNull($request->get('documentation_url'));
        $repositoryUrl = $this->urlOrNull($request->get('repository_url'));

        if ($documentationUrl === false || $repositoryUrl === false) {
            return new Redirect('/components/create')->flash('error', Translator::translate('flash.error.valid_urls_required'));
        }

        $component = $this->componentService->create(new CreateComponentCommand(
            name: $name,
            shortName: $this->stringOrNull($request->get('short_name')),
            componentTypeId: (int) $request->get('component_type_id'),
            statusId: (int) $request->get('status_id'),
            criticalityId: $this->intOrNull($request->get('criticality_id')),
            businessOwnerId: $this->intOrNull($request->get('business_owner_id')),
            businessOwnerTeamId: $this->intOrNull($request->get('business_owner_team_id')),
            technicalOwnerId: $this->intOrNull($request->get('technical_owner_id')),
            technicalOwnerTeamId: $this->intOrNull($request->get('technical_owner_team_id')),
            deploymentLocationId: $this->intOrNull($request->get('deployment_location_id')),
            environmentId: $this->intOrNull($request->get('environment_id')),
            projectName: $this->stringOrNull($request->get('project_name')),
            startedOn: $this->dateOrNull($request->get('started_on')),
            purpose: $this->stringOrNull($request->get('purpose')),
            description: $this->stringOrNull($request->get('description')),
            documentationUrl: $documentationUrl,
            repositoryUrl: $repositoryUrl,
            vendor: $this->stringOrNull($request->get('vendor')),
            lifecycleNotes: $this->stringOrNull($request->get('lifecycle_notes')),
            isExternal: $this->boolOrNull($request->get('is_external')) ?? false,
            parentComponentId: $this->intOrNull($request->get('parent_component_id')),
            childComponentIds: $this->intList($request->get('child_component_ids')),
        ));

        return new Redirect("/components/{$component->id()}")->flash('success', Translator::translate('flash.success.component_created'));
    }

    #[Get('/components/{id}')]
    public function show(int $id): Response {
        try {
            $detail = $this->componentService->detail($id);
        } catch (RuntimeException) {
            return new Ok(view('../../View/components/show.view.php', component: null))
                ->setStatus(Status::NOT_FOUND);
        }

        $people = $this->people->all();

        return new Ok(view(
            '../../View/components/show.view.php',
            component: $detail->component,
            detail: $detail,
            businessOwnerName: $this->personName($people, $detail->component->businessOwnerId()),
            businessOwnerTeamName: $this->lookupName($this->lookups->teams(), $detail->component->businessOwnerTeamId()),
            technicalOwnerName: $this->personName($people, $detail->component->technicalOwnerId()),
            technicalOwnerTeamName: $this->lookupName($this->lookups->teams(), $detail->component->technicalOwnerTeamId()),
        ));
    }

    #[Get('/components/{id}/edit')]
    public function edit(int $id): Response {
        $component = $this->components->findById($id);

        if ($component === null) {
            return new Redirect('/components')->flash('error', Translator::translate('flash.error.component_not_found'));
        }

        return new Ok(view(
            '../../View/components/edit.view.php',
            component: $component,
            componentTypes: $this->lookups->componentTypes(),
            statuses: $this->lookups->componentStatuses(),
            criticalityLevels: $this->lookups->criticalityLevels(),
            environments: $this->lookups->environments(),
            deploymentLocations: $this->lookups->deploymentLocations(),
            people: $this->people->allActive(),
            teams: $this->lookups->teams(),
            availableComponents: $this->components->all(),
        ));
    }

    #[Post('/components/{id}')]
    public function update(int $id, Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect("/components/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $name = trim((string) $request->get('name', ''));

        if ($name === '') {
            return new Redirect("/components/{$id}/edit")->flash('error', Translator::translate('flash.error.name_required'));
        }

        $documentationUrl = $this->urlOrNull($request->get('documentation_url'));
        $repositoryUrl = $this->urlOrNull($request->get('repository_url'));

        if ($documentationUrl === false || $repositoryUrl === false) {
            return new Redirect("/components/{$id}/edit")->flash('error', Translator::translate('flash.error.valid_urls_required'));
        }

        $this->componentService->update(new UpdateComponentCommand(
            id: $id,
            name: $name,
            shortName: $this->stringOrNull($request->get('short_name')),
            componentTypeId: (int) $request->get('component_type_id'),
            statusId: (int) $request->get('status_id'),
            criticalityId: $this->intOrNull($request->get('criticality_id')),
            businessOwnerId: $this->intOrNull($request->get('business_owner_id')),
            businessOwnerTeamId: $this->intOrNull($request->get('business_owner_team_id')),
            technicalOwnerId: $this->intOrNull($request->get('technical_owner_id')),
            technicalOwnerTeamId: $this->intOrNull($request->get('technical_owner_team_id')),
            deploymentLocationId: $this->intOrNull($request->get('deployment_location_id')),
            environmentId: $this->intOrNull($request->get('environment_id')),
            projectName: $this->stringOrNull($request->get('project_name')),
            startedOn: $this->dateOrNull($request->get('started_on')),
            purpose: $this->stringOrNull($request->get('purpose')),
            description: $this->stringOrNull($request->get('description')),
            documentationUrl: $documentationUrl,
            repositoryUrl: $repositoryUrl,
            vendor: $this->stringOrNull($request->get('vendor')),
            lifecycleNotes: $this->stringOrNull($request->get('lifecycle_notes')),
            isExternal: $this->boolOrNull($request->get('is_external')) ?? false,
            parentComponentId: $this->intOrNull($request->get('parent_component_id')) === $id
                ? null
                : $this->intOrNull($request->get('parent_component_id')),
            childComponentIds: $this->intList($request->get('child_component_ids'), $id),
        ));

        return new Redirect("/components/{$id}")->flash('success', Translator::translate('flash.success.component_updated'));
    }

    // Delete is intentionally POST-only (never GET).
    #[Post('/components/{id}/delete')]
    public function delete(int $id, Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect("/components/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $this->componentService->delete($id);

        return new Redirect('/components')->flash('success', Translator::translate('flash.success.component_deleted'));
    }

    #[Get('/components/{id}/diagram')]
    public function diagram(int $id): Response {
        $detail = $this->componentService->detail($id);

        return new Ok(view(
            '../../View/components/diagram.view.php',
            component: $detail->component,
            mermaid: $detail->mermaidDiagram,
        ));
    }

    #[Get('/components/{id}/governance')]
    public function governance(int $id): Response {
        $detail = $this->componentService->detail($id);

        return new Ok(view(
            '../../View/components/governance.view.php',
            component: $detail->component,
            review: $detail->governanceReview,
        ));
    }

    private function stringOrNull(mixed $value): ?string {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /** @param \App\Domain\Person\Person[] $people */
    private function personName(array $people, ?int $id): string {
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
    private function lookupName(array $rows, ?int $id): string {
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

    private function intOrNull(mixed $value): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return int[]
     */
    private function intList(mixed $value, ?int $excludingId = null, ?int $maxCount = null): array {
        if ($value === null || $value === '') {
            return [];
        }

        $values = $value instanceof Traversable ? iterator_to_array($value) : (is_array($value) ? $value : [$value]);
        $ids = [];

        foreach ($values as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            $id = (int) $item;

            if ($id <= 0 || $id === $excludingId) {
                continue;
            }

            $ids[$id] = $id;
        }

        $result = array_values($ids);

        if ($maxCount !== null) {
            return array_slice($result, 0, $maxCount);
        }

        return $result;
    }

    private function boolOrNull(mixed $value): ?bool {
        if ($value === null || $value === '') {
            return null;
        }

        return in_array($value, ['1', 1, true, 'true', 'on'], true);
    }

    private function dateOrNull(mixed $value): ?DateTimeImmutable {
        $string = $this->stringOrNull($value);

        if ($string === null) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('Y-m-d', $string) ?: null;
    }

    /** @return string|false|null */
    private function urlOrNull(mixed $value): string|false|null {
        $url = $this->stringOrNull($value);

        if ($url === null) {
            return null;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }
}
