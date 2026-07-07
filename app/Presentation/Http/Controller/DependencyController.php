<?php
declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Dependency\CreateDependencyCommand;
use App\Application\Dependency\DependencyService;
use App\Application\Dependency\UpdateDependencyCommand;
use App\Domain\Component\ComponentRepository;
use App\Domain\Dependency\DependencyRepository;
use App\Domain\Dependency\DependencySearchCriteria;
use App\Domain\Person\PersonRepository;
use App\Infrastructure\Persistence\LookupRepository;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Presentation\ViewModel\DependencyDetailViewModel;
use App\Presentation\ViewModel\DependencyListItemViewModel;
use App\Shared\Support\Csrf;
use App\Shared\Support\Translator;
use InvalidArgumentException;
use RuntimeException;
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
final readonly class DependencyController {
    public function __construct(
        private DependencyRepository $dependencies,
        private DependencyService $dependencyService,
        private ComponentRepository $components,
        private PersonRepository $people,
        private LookupRepository $lookups,
    ) {}

    #[Get('/dependencies')]
    public function index(Request $request): Response {
        $criteria = new DependencySearchCriteria(
            query: $this->stringOrNull($request->get('q')),
            sourceComponentId: $this->intOrNull($request->get('source_component_id')),
            targetComponentId: $this->intOrNull($request->get('target_component_id')),
            dependencyTypeId: $this->intOrNull($request->get('dependency_type_id')),
            protocolId: $this->intOrNull($request->get('protocol_id')),
            statusId: $this->intOrNull($request->get('status_id')),
            criticalityId: $this->intOrNull($request->get('criticality_id')),
            ownerId: $this->intOrNull($request->get('owner_id')),
            ownerTeamId: $this->intOrNull($request->get('owner_team_id')),
            dataObjectId: $this->intOrNull($request->get('data_object_id')),
        );

        $components = $this->components->all();

        return new Ok(view(
            '../../View/dependencies/index.view.php',
            dependencies: $this->dependencyListItems($this->dependencies->search($criteria), $components),
            criteria: $criteria,
            components: $components,
            dependencyTypes: $this->lookups->dependencyTypes(),
            protocols: $this->lookups->communicationProtocols(),
            statuses: $this->lookups->componentStatuses(),
            criticalityLevels: $this->lookups->criticalityLevels(),
            people: $this->people->allActive(),
            teams: $this->lookups->teams(),
            dataObjects: $this->lookups->dataObjects(),
        ));
    }

    #[Get('/dependencies/create')]
    public function create(): Response {
        return new Ok($this->formView('../../View/dependencies/create.view.php'));
    }

    #[Post('/dependencies')]
    public function store(Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect('/dependencies/create')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return new Redirect('/dependencies/create')->flash('error', Translator::translate('flash.error.name_required'));
        }

        $documentationUrl = $this->urlOrNull($request->get('documentation_url'));
        if ($documentationUrl === false) {
            return new Redirect('/dependencies/create')->flash('error', Translator::translate('flash.error.valid_documentation_url_required'));
        }

        try {
            $dependency = $this->dependencyService->create(new CreateDependencyCommand(
                sourceComponentId: (int) $request->get('source_component_id'),
                targetComponentId: (int) $request->get('target_component_id'),
                dependencyTypeId: (int) $request->get('dependency_type_id'),
                protocolId: $this->intOrNull($request->get('protocol_id')),
                statusId: (int) $request->get('status_id'),
                criticalityId: $this->intOrNull($request->get('criticality_id')),
                ownerId: $this->intOrNull($request->get('owner_id')),
                ownerTeamId: $this->intOrNull($request->get('owner_team_id')),
                name: $name,
                description: $this->stringOrNull($request->get('description')),
                dataDescription: $this->stringOrNull($request->get('data_description')),
                frequency: $this->stringOrNull($request->get('frequency')),
                direction: $this->stringOrNull($request->get('direction')) ?? 'source_to_target',
                authenticationMethod: $this->stringOrNull($request->get('authentication_method')),
                documentationUrl: $documentationUrl,
                technicalNotes: $this->stringOrNull($request->get('technical_notes')),
                isBidirectional: $this->boolOrNull($request->get('is_bidirectional')) ?? false,
            ));
        } catch (InvalidArgumentException $exception) {
            return new Redirect('/dependencies/create')->flash('error', $exception->getMessage());
        }

        return new Redirect("/dependencies/{$dependency->id()}")->flash('success', Translator::translate('flash.success.dependency_created'));
    }

    #[Get('/dependencies/{id}')]
    public function show(int $id): Response {
        $dependency = $this->dependencies->findById($id);

        if ($dependency === null) {
            return new Ok(view('../../View/dependencies/show.view.php', detail: null))->setStatus(Status::NOT_FOUND);
        }

        $components = $this->components->all();

        return new Ok(view(
            '../../View/dependencies/show.view.php',
            detail: new DependencyDetailViewModel(
                dependency: $dependency,
                sourceComponentName: $this->componentName($components, $dependency->sourceComponentId()),
                targetComponentName: $this->componentName($components, $dependency->targetComponentId()),
                ownerName: $this->personName($this->people->all(), $dependency->ownerId()),
                ownerTeamName: $this->lookupName($this->lookups->teams(), $dependency->ownerTeamId()),
            ),
        ));
    }

    #[Get('/dependencies/{id}/edit')]
    public function edit(int $id): Response {
        $dependency = $this->dependencies->findById($id);
        if ($dependency === null) {
            return new Redirect('/dependencies')->flash('error', Translator::translate('flash.error.dependency_not_found'));
        }

        return new Ok($this->formView('../../View/dependencies/edit.view.php', $dependency));
    }

    #[Post('/dependencies/{id}')]
    public function update(int $id, Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect("/dependencies/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return new Redirect("/dependencies/{$id}/edit")->flash('error', Translator::translate('flash.error.name_required'));
        }

        $documentationUrl = $this->urlOrNull($request->get('documentation_url'));
        if ($documentationUrl === false) {
            return new Redirect("/dependencies/{$id}/edit")->flash('error', Translator::translate('flash.error.valid_documentation_url_required'));
        }

        try {
            $this->dependencyService->update(new UpdateDependencyCommand(
                id: $id,
                sourceComponentId: (int) $request->get('source_component_id'),
                targetComponentId: (int) $request->get('target_component_id'),
                dependencyTypeId: (int) $request->get('dependency_type_id'),
                protocolId: $this->intOrNull($request->get('protocol_id')),
                statusId: (int) $request->get('status_id'),
                criticalityId: $this->intOrNull($request->get('criticality_id')),
                ownerId: $this->intOrNull($request->get('owner_id')),
                ownerTeamId: $this->intOrNull($request->get('owner_team_id')),
                name: $name,
                description: $this->stringOrNull($request->get('description')),
                dataDescription: $this->stringOrNull($request->get('data_description')),
                frequency: $this->stringOrNull($request->get('frequency')),
                direction: $this->stringOrNull($request->get('direction')) ?? 'source_to_target',
                authenticationMethod: $this->stringOrNull($request->get('authentication_method')),
                documentationUrl: $documentationUrl,
                technicalNotes: $this->stringOrNull($request->get('technical_notes')),
                isBidirectional: $this->boolOrNull($request->get('is_bidirectional')) ?? false,
            ));
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return new Redirect("/dependencies/{$id}/edit")->flash('error', $exception->getMessage());
        }

        return new Redirect("/dependencies/{$id}")->flash('success', Translator::translate('flash.success.dependency_updated'));
    }

    #[Post('/dependencies/{id}/delete')]
    public function delete(int $id, Request $request): Response {
        if (! Csrf::isValid($request)) {
            return new Redirect("/dependencies/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        $this->dependencyService->delete($id);

        return new Redirect('/dependencies')->flash('success', Translator::translate('flash.success.dependency_deleted'));
    }

    private function formView(string $view, mixed $dependency = null): mixed {
        return view(
            $view,
            dependency: $dependency,
            components: $this->components->all(),
            dependencyTypes: $this->lookups->dependencyTypes(),
            protocols: $this->lookups->communicationProtocols(),
            statuses: $this->lookups->componentStatuses(),
            criticalityLevels: $this->lookups->criticalityLevels(),
            people: $this->people->allActive(),
            teams: $this->lookups->teams(),
        );
    }

    private function stringOrNull(mixed $value): ?string {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param \App\Domain\Dependency\Dependency[] $dependencies
     * @param \App\Domain\Component\Component[] $components
     * @return DependencyListItemViewModel[]
     */
    private function dependencyListItems(array $dependencies, array $components): array {
        return array_map(
            fn ($dependency): DependencyListItemViewModel => new DependencyListItemViewModel(
                id: (int) $dependency->id(),
                name: $dependency->name(),
                sourceComponentName: $this->componentName($components, $dependency->sourceComponentId()),
                targetComponentName: $this->componentName($components, $dependency->targetComponentId()),
                dataDescription: $dependency->dataDescription(),
                frequency: $dependency->frequency(),
                isIncomplete: $dependency->isIncomplete(),
            ),
            $dependencies,
        );
    }

    /** @param \App\Domain\Component\Component[] $components */
    private function componentName(array $components, int $id): string {
        foreach ($components as $component) {
            if ($component->id() === $id) {
                return $component->name();
            }
        }

        return 'C' . $id;
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

    private function boolOrNull(mixed $value): ?bool {
        if ($value === null || $value === '') {
            return null;
        }

        return in_array($value, ['1', 1, true, 'true', 'on'], true);
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
