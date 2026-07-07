<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use PDOException;
use Tempest\Core\Priority;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;

use function Tempest\view;

/**
 * Intercepts PDOExceptions that indicate the database is unreachable and
 * returns a user-friendly 503 Service Unavailable page instead of a raw
 * stack trace or a generic 500 error.
 *
 * Only raw PDOExceptions (i.e. connection-level failures) are caught here.
 * Query-level errors wrapped in QueryWasInvalid propagate normally so that
 * the development exception handler can still surface them.
 */
#[Priority(Priority::HIGHEST)]
final readonly class DatabaseAvailabilityMiddleware implements HttpMiddleware {
    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response {
        try {
            return $next($request);
        } catch (PDOException) {
            return new GenericResponse(
                status: Status::SERVICE_UNAVAILABLE,
                body: view('../../Presentation/View/errors/db-unavailable.view.php'),
            );
        }
    }
}
