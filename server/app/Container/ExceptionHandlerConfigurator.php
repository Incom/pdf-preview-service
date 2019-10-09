<?php declare(strict_types=1);

namespace App\Container;

use Limoncello\Application\ExceptionHandlers\WhoopsThrowableJsonHandler;
use Limoncello\Contracts\Application\ContainerConfiguratorInterface;
use Limoncello\Contracts\Container\ContainerInterface as LimoncelloContainerInterface;
use Limoncello\Contracts\Exceptions\ThrowableHandlerInterface;

/**
 * @package App\Container
 */
class ExceptionHandlerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = self::CONFIGURE_EXCEPTION_HANDLER;

    /** Configurator callable */
    const CONFIGURE_EXCEPTION_HANDLER = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(LimoncelloContainerInterface $container): void
    {
        $container[ThrowableHandlerInterface::class] = function (): ThrowableHandlerInterface {
            return new WhoopsThrowableJsonHandler();
        };
    }
}
