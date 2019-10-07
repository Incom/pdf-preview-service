<?php namespace App\Routes;

use App\Api\PdfToImageController;
use Limoncello\Contracts\Application\RoutesConfiguratorInterface;
use Limoncello\Contracts\Routing\GroupInterface;
use Limoncello\Flute\Http\Traits\FluteRoutesTrait;

/**
 * @package App
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ApiRoutes implements RoutesConfiguratorInterface
{
    use FluteRoutesTrait;

    /** @var string API URI prefix */
    const API_URI_PREFIX = '/api/v1';

    /** @var string URI for convert */
    const API_URL__CONVERT = 'convert';

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function configureRoutes(GroupInterface $routes): void
    {
        // Every group, controller and even method may have custom `Request` factory and `Container` configurator.
        // Thus container for `API` and `Web` groups can be configured differently which could be used for
        // improving page load time for every HTTP route.
        // Container can be configured even for individual controller method (e.g. `PaymentsController::index`).
        // Also custom middleware could be specified for a group, controller or method.

        $routes->get('', [PdfToImageController::class, PdfToImageController::METHOD_INDEX]);

        $routes
            ->group(self::API_URI_PREFIX, function (GroupInterface $routes): void {
                $routes->post(
                    self::API_URL__CONVERT,
                    [PdfToImageController::class, PdfToImageController::METHOD_CREATE]
                );
            });
    }

    /**
     * This middleware will be executed on every request even when no matching route is found.
     *
     * @return string[]
     */
    public static function getMiddleware(): array
    {
        return [
            //ClassName::class,
        ];
    }
}
