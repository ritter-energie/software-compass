<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Application\ReferenceData\ReferenceDataService;
use App\Application\ReferenceData\SaveReferenceDataEntryCommand;
use App\Domain\ReferenceData\ReferenceDataType;
use App\Infrastructure\Security\BasicAuthMiddleware;
use App\Infrastructure\Security\CurrentUser;
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
final readonly class ReferenceDataController
{
    public function __construct(private ReferenceDataService $referenceData) {}

    #[Get('/master-data')]
    public function index(): Response
    {
        $entriesByType = [];

        foreach ($this->referenceData->types() as $type) {
            $entriesByType[$type->value] = $this->referenceData->entries($type);
        }

        return new Ok(view(
            '../../View/master-data/index.view.php',
            types: $this->referenceData->types(),
            entriesByType: $entriesByType,
            canManage: CurrentUser::hasRole('admin'),
        ));
    }

    #[Get('/master-data/{type}/create')]
    public function create(string $type): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        try {
            return new Ok(view(
                '../../View/master-data/create.view.php',
                type: ReferenceDataType::fromRoute($type),
                entry: null,
            ));
        } catch (InvalidArgumentException) {
            return new Redirect('/master-data')->flash('error', Translator::translate('flash.error.master_data_group_not_found'));
        }
    }

    #[Post('/master-data/{type}')]
    public function store(string $type, Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect("/master-data/{$type}/create")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        try {
            $referenceDataType = ReferenceDataType::fromRoute($type);
            $this->referenceData->create($this->command($referenceDataType, $request));
        } catch (InvalidArgumentException $exception) {
            return new Redirect("/master-data/{$type}/create")->flash('error', Translator::translate($exception->getMessage()));
        }

        return new Redirect('/master-data')->flash('success', Translator::translate('flash.success.master_data_created'));
    }

    #[Get('/master-data/{type}/{id}/edit')]
    public function edit(string $type, int $id): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        try {
            $referenceDataType = ReferenceDataType::fromRoute($type);

            return new Ok(view(
                '../../View/master-data/edit.view.php',
                type: $referenceDataType,
                entry: $this->referenceData->entry($referenceDataType, $id),
            ));
        } catch (InvalidArgumentException) {
            return new Redirect('/master-data')->flash('error', Translator::translate('flash.error.master_data_group_not_found'));
        } catch (RuntimeException $exception) {
            return new Redirect('/master-data')->flash('error', Translator::translate($exception->getMessage()));
        }
    }

    #[Post('/master-data/{type}/{id}')]
    public function update(string $type, int $id, Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect("/master-data/{$type}/{$id}/edit")->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        try {
            $referenceDataType = ReferenceDataType::fromRoute($type);
            $this->referenceData->update($id, $this->command($referenceDataType, $request));
        } catch (InvalidArgumentException $exception) {
            return new Redirect("/master-data/{$type}/{$id}/edit")->flash('error', Translator::translate($exception->getMessage()));
        }

        return new Redirect('/master-data')->flash('success', Translator::translate('flash.success.master_data_updated'));
    }

    #[Post('/master-data/{type}/{id}/delete')]
    public function delete(string $type, int $id, Request $request): Response
    {
        if (! CurrentUser::hasRole('admin')) {
            return new Ok('Admin role required.')->setStatus(Status::FORBIDDEN);
        }

        if (! Csrf::isValid($request)) {
            return new Redirect('/master-data')->flash('error', Translator::translate('flash.error.invalid_security_token'));
        }

        try {
            $this->referenceData->delete(ReferenceDataType::fromRoute($type), $id);
        } catch (InvalidArgumentException $exception) {
            return new Redirect('/master-data')->flash('error', Translator::translate($exception->getMessage()));
        }

        return new Redirect('/master-data')->flash('success', Translator::translate('flash.success.master_data_deleted'));
    }

    private function command(ReferenceDataType $type, Request $request): SaveReferenceDataEntryCommand
    {
        return new SaveReferenceDataEntryCommand(
            type: $type,
            name: trim((string) $request->get('name', '')),
            description: $this->stringOrNull($request->get('description')),
            sortOrder: (int) ($request->get('sort_order') ?? 0),
            locationType: $this->stringOrNull($request->get('location_type')),
            containsPersonalData: $this->bool($request->get('contains_personal_data')),
            containsSensitiveData: $this->bool($request->get('contains_sensitive_data')),
        );
    }

    private function stringOrNull(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed === '' ? null : $trimmed;
    }

    private function bool(mixed $value): bool
    {
        return in_array($value, ['1', 1, true, 'true', 'on'], true);
    }
}

