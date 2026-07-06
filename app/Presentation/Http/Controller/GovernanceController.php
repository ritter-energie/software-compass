<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\Component\ComponentService;
use App\Application\Governance\GovernanceService;
use App\Application\Governance\UpdateGovernanceReviewCommand;
use App\Domain\Component\ComponentRepository;
use App\Domain\Governance\GovernanceReviewRepository;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Infrastructure\Security\CurrentUser;
use App\Presentation\ViewModel\GovernanceReviewListItemViewModel;
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
final readonly class GovernanceController
{
    public function __construct(
        private GovernanceService $governance,
        private GovernanceReviewRepository $reviews,
        private ComponentRepository $components,
        private ComponentService $componentService,
    ) {}

    #[Get('/governance')]
    #[Get('/governance/reviews')]
    public function index(): Response
    {
        return new Ok(view(
            '../../View/governance/index.view.php',
            reviews: $this->governanceReviewListItems($this->governance->openReviews(), $this->components->all()),
        ));
    }

    #[Get('/governance/reviews/{id}')]
    public function show(int $id): Response
    {
        $review = $this->reviews->findById($id);
        if ($review === null) {
            return new Redirect('/governance')->flash('error', Translator::translate('flash.error.governance_review_not_found'));
        }
        $component = $this->components->findById($review->componentId());
        $similar = $component ? $this->componentService->findSimilar((string) $component->purpose(), $component->name()) : [];

        return new Ok(view('../../View/governance/review.view.php', review: $review, component: $component, similarComponents: $similar));
    }

    #[Post('/governance/reviews/{id}')]
    public function update(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/governance/reviews/{$id}")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $this->governance->updateReview(new UpdateGovernanceReviewCommand(
            reviewId: $id,
            duplicateCheckDone: $request->get('duplicate_check_done') !== null,
            interfaceCheckDone: $request->get('interface_check_done') !== null,
            ownerCheckDone: $request->get('owner_check_done') !== null,
            dataCheckDone: $request->get('data_check_done') !== null,
            deploymentCheckDone: $request->get('deployment_check_done') !== null,
            notes: $this->stringOrNull($request->get('notes')),
        ));

        return new Redirect("/governance/reviews/{$id}")->flash('success', Translator::translate('flash.success.review_checklist_updated'));
    }

    #[Post('/governance/reviews/{id}/approve')]
    public function approve(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/governance/reviews/{$id}")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $reviewerId = CurrentUser::personId();
        if ($reviewerId === null) {
            return new Redirect("/governance/reviews/{$id}")->flash('error', Translator::translate('flash.error.user_not_linked_to_person'));
        }
        try {
            $this->governance->approve($id, reviewerId: $reviewerId, notes: $this->stringOrNull($request->get('notes')));
        } catch (RuntimeException $exception) {
            return new Redirect('/governance')->flash('error', $exception->getMessage());
        }

        return new Redirect("/governance/reviews/{$id}")->flash('success', Translator::translate('flash.success.review_approved'));
    }

    #[Post('/governance/reviews/{id}/reject')]
    public function reject(int $id, Request $request): Response
    {
        if (! Csrf::isValid($request)) {
            return new Redirect("/governance/reviews/{$id}")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }
        $reviewerId = CurrentUser::personId();
        if ($reviewerId === null) {
            return new Redirect("/governance/reviews/{$id}")->flash('error', Translator::translate('flash.error.user_not_linked_to_person'));
        }
        try {
            $this->governance->reject($id, reviewerId: $reviewerId, notes: $this->stringOrNull($request->get('notes')));
        } catch (RuntimeException $exception) {
            return new Redirect('/governance')->flash('error', $exception->getMessage());
        }

        return new Redirect("/governance/reviews/{$id}")->flash('success', Translator::translate('flash.success.review_rejected'));
    }

    private function stringOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));
        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param \App\Domain\Governance\GovernanceReview[] $reviews
     * @param \App\Domain\Component\Component[] $components
     * @return GovernanceReviewListItemViewModel[]
     */
    private function governanceReviewListItems(array $reviews, array $components): array
    {
        return array_map(
            fn ($review): GovernanceReviewListItemViewModel => new GovernanceReviewListItemViewModel(
                review: $review,
                componentName: $this->componentName($components, $review->componentId()),
                checksDoneLabel: str_replace('{count}', (string) $this->checksDone($review), Translator::translate('governance.checks_done')),
            ),
            $reviews,
        );
    }

    private function checksDone(\App\Domain\Governance\GovernanceReview $review): int
    {
        return (
            (int) $review->duplicateCheckDone()
            + (int) $review->interfaceCheckDone()
            + (int) $review->ownerCheckDone()
            + (int) $review->dataCheckDone()
            + (int) $review->deploymentCheckDone()
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
}
