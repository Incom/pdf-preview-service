<?php namespace App\Routes;

//use Limoncello\Application\Packages\Application\ApplicationContainerConfigurator;
//use Limoncello\Application\Packages\Application\WhoopsContainerConfigurator;
//use Limoncello\Application\Packages\FileSystem\FileSystemContainerConfigurator;
//use Limoncello\Application\Packages\L10n\L10nContainerConfigurator;
use Limoncello\Contracts\Application\RoutesConfiguratorInterface;
use Limoncello\Contracts\Routing\GroupInterface;
use Limoncello\Flute\Http\Traits\FluteRoutesTrait;

/**
 * @package App
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CliRoutes implements RoutesConfiguratorInterface
{
    use FluteRoutesTrait;

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function configureRoutes(GroupInterface $routes): void
    {
        // Individual console commands can have their custom containers too!
        // For example, limoncello `db` command might need `Faker` for data seeding.

//        // commands require composer
//        if (class_exists('Composer\Command\BaseCommand') === true) {
//            // Common configurators that typically needed in commands.
//            // We configure them independently from the main application so even if all
//            // providers will be disabled in the main app the commands will continue to work.
//            $routes->addContainerConfigurators([
//                WhoopsContainerConfigurator::CONFIGURE_EXCEPTION_HANDLER,
//                ApplicationContainerConfigurator::CONFIGURATOR,
//                L10nContainerConfigurator::CONFIGURATOR,
//                FileSystemContainerConfigurator::CONFIGURATOR,
//            ]);
//        }
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
