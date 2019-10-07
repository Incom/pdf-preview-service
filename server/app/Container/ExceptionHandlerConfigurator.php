<?php declare(strict_types=1);

namespace App\Container;

use Limoncello\Application\ExceptionHandlers\WhoopsThrowableHtmlHandler;
use Limoncello\Application\ExceptionHandlers\WhoopsThrowableJsonHandler;
use Limoncello\Application\ExceptionHandlers\WhoopsThrowableTextHandler;
use Limoncello\Contracts\Application\ContainerConfiguratorInterface;
use Limoncello\Contracts\Container\ContainerInterface as LimoncelloContainerInterface;
use Limoncello\Contracts\Exceptions\ThrowableHandlerInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use function php_sapi_name;

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
