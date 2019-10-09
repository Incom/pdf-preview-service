<?php declare(strict_types=1);

namespace App\Container;

use Limoncello\Contracts\Application\ApplicationConfigurationInterface as A;
use Limoncello\Contracts\Application\CacheSettingsProviderInterface;
use Limoncello\Contracts\Application\ContainerConfiguratorInterface;
use Limoncello\Contracts\Container\ContainerInterface as LimoncelloContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @package App\Container
 */
class LoggerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(LimoncelloContainerInterface $container): void
    {
        $container[LoggerInterface::class] = function (PsrContainerInterface $container) {
            /** @var CacheSettingsProviderInterface $settingsProvider */
            $settingsProvider = $container->get(CacheSettingsProviderInterface::class);
            $appConfig        = $settingsProvider->getApplicationConfiguration();

            $monolog = new Logger($appConfig[A::KEY_APP_NAME]);
            $handler = $appConfig[A::KEY_IS_LOG_ENABLED] === true ?
                static::createHandler($appConfig) : new NullHandler();

            $monolog->pushHandler($handler);

            return $monolog;
        };
    }

    /**
     * @param array $settings
     *
     * @return HandlerInterface
     */
    protected static function createHandler(array $settings): HandlerInterface
    {
        $logLevel = $settings[A::KEY_IS_DEBUG] ? Logger::DEBUG : Logger::ERROR;
        $handler  = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $logLevel);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $handler->pushProcessor(new WebProcessor());
        $handler->pushProcessor(new UidProcessor());

        return $handler;
    }
}
