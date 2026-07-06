<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use App\Infrastructure\Persistence\LookupRepository;
use App\Infrastructure\Security\BasicAuthMiddleware;
use Tempest\Http\Response;
use Tempest\Http\Responses\Ok;
use Tempest\Router\Get;
use Tempest\Router\WithMiddleware;

use function Tempest\view;

#[WithMiddleware(BasicAuthMiddleware::class)]
final readonly class MasterDataController
{
    public function __construct(private LookupRepository $lookups) {}

    #[Get('/master-data')]
    public function index(): Response
    {
        return new Ok(view('../../View/master-data/index.view.php', groups: [
            'master_data.component_types' => $this->lookups->componentTypes(),
            'master_data.component_statuses' => $this->lookups->componentStatuses(),
            'master_data.criticality_levels' => $this->lookups->criticalityLevels(),
            'master_data.environments' => $this->lookups->environments(),
            'master_data.deployment_locations' => $this->lookups->deploymentLocations(),
            'master_data.dependency_types' => $this->lookups->dependencyTypes(),
            'master_data.communication_protocols' => $this->lookups->communicationProtocols(),
            'master_data.data_objects' => $this->lookups->dataObjects(),
            'master_data.tags' => $this->lookups->tags(),
        ]));
    }
}

