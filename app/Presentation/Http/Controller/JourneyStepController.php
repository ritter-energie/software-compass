<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Journey\AddJourneyStepCommand;
use App\Application\Journey\JourneyService;
use App\Application\Journey\UpdateJourneyStepCommand;
use App\Domain\Journey\JourneyStepComponent;
use App\Infrastructure\Security\BasicAuthMiddleware;
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
final readonly class JourneyStepController
{
    public function __construct(
        private JourneyService $journeys,
    ) {}

    #[Get('/journeys/{id}/steps/create')]
    public function create(int $id): Response
    {
        return new Ok(view('../../View/journeys/step-create.view.php', journeyId: $id));
    }

    #[Post('/journeys/{id}/steps')]
    public function store(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/journeys/{$id}")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return new Redirect("/journeys/{$id}/steps/create")->flash('error', Translator::translate('flash.error.step_name_required'));
        }
        $this->journeys->addStep(new AddJourneyStepCommand(
            journeyId: $id,
            name: $name,
            description: $this->stringOrNull($request->get('description')),
            sortOrder: (int) ($request->get('sort_order') ?? 0),
        ));

        return new Redirect("/journeys/{$id}")->flash('success', Translator::translate('flash.success.journey_step_added'));
    }

    #[Get('/journey-steps/{id}/edit')]
    public function edit(int $id): Response
    {
        try {
            $step = $this->journeys->step($id);
        } catch (RuntimeException) {
            return new Redirect('/journeys')->flash('error', Translator::translate('flash.error.journey_step_not_found'));
        }

        return new Ok(view('../../View/journeys/step-edit.view.php', step: $step));
    }

    #[Post('/journey-steps/{id}')]
    public function update(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/journey-steps/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $step = $this->journeys->step($id);
        $name = trim((string) $request->get('name', ''));
        if ($name === '') {
            return new Redirect("/journey-steps/{$id}/edit")->flash('error', Translator::translate('flash.error.step_name_required'));
        }

        $this->journeys->updateStep(new UpdateJourneyStepCommand(
            id: $id,
            journeyId: $step->journeyId(),
            name: $name,
            description: $this->stringOrNull($request->get('description')),
            sortOrder: (int) ($request->get('sort_order') ?? 0),
        ));

        return new Redirect('/journeys/' . $step->journeyId())->flash('success', Translator::translate('flash.success.journey_step_updated'));
    }

    #[Post('/journey-steps/{id}/delete')]
    public function delete(int $id, Request $request): Response
    {
        $step = $this->journeys->step($id);
        if (! Csrf::isValid($request)) {
            return new Redirect('/journeys/' . $step->journeyId())->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $this->journeys->deleteStep($id);

        return new Redirect('/journeys/' . $step->journeyId())->flash('success', Translator::translate('flash.success.journey_step_deleted'));
    }

    #[Post('/journey-steps/{id}/components')]
    public function attachComponent(int $id, Request $request): Response
    {
        $step = $this->journeys->step($id);
        if (! Csrf::isValid($request)) {
            return new Redirect('/journeys/' . $step->journeyId())->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        try {
            $this->journeys->attachComponent(
                stepId: $id,
                componentId: (int) $request->get('component_id'),
                roleInStep: (string) ($request->get('role_in_step') ?? JourneyStepComponent::ROLE_SUPPORTING),
                notes: $this->stringOrNull($request->get('notes')),
            );
        } catch (RuntimeException $exception) {
            return new Redirect('/journeys/' . $step->journeyId())->flash('error', $exception->getMessage());
        }

        return new Redirect('/journeys/' . $step->journeyId())->flash('success', Translator::translate('flash.success.component_assigned_to_step'));
    }

    #[Post('/journey-step-components/{id}/delete')]
    public function deleteStepComponent(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect('/journeys')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $redirect = (int) ($request->get('journey_id') ?? 0);
        $this->journeys->deleteStepComponent($id);

        return new Redirect($redirect > 0 ? '/journeys/' . $redirect : '/journeys')->flash('success', Translator::translate('flash.success.component_assignment_removed'));
    }

    private function stringOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));
        return $trimmed === '' ? null : $trimmed;
    }
}
