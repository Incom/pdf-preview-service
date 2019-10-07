<?php declare(strict_types=1);

namespace App\Container;

use Limoncello\Contracts\Application\ContainerConfiguratorInterface;
use Limoncello\Contracts\Container\ContainerInterface as LimoncelloContainerInterface;
use Limoncello\Contracts\L10n\FormatterFactoryInterface;
use Limoncello\Contracts\Settings\SettingsProviderInterface;
use Limoncello\Flute\Contracts\Validation\FormValidatorFactoryInterface;
use Limoncello\Flute\Contracts\Validation\FormValidatorInterface;
use Limoncello\Flute\Validation\Form\Execution\FormRulesSerializer;
use Limoncello\Flute\Validation\Form\FormValidator;
use Limoncello\Validation\Execution\ContextStorage;
use Messages\Messages;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Settings\Validation;

/**
 * @package App\Container
 */
class ValidationConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable */
    const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(LimoncelloContainerInterface $container): void
    {
        $container[FormValidatorFactoryInterface::class] = function (PsrContainerInterface $container) {
            return new class($container) implements FormValidatorFactoryInterface
            {
                /**
                 * @var PsrContainerInterface
                 */
                private $container;

                /**
                 *
                 * @param PsrContainerInterface $container
                 */
                public function __construct(PsrContainerInterface $container)
                {
                    $this->container = $container;
                }

                /**
                 * @inheritdoc
                 */
                public function createValidator(string $rulesClass): FormValidatorInterface
                {
                    /** @var SettingsProviderInterface $settingsProvider */
                    $settingsProvider = $this->container->get(SettingsProviderInterface::class);
                    $serializedData   = $settingsProvider
                        ->get(Validation::class)[Validation::KEY_FORM_VALIDATORS_RULES_DATA];

                    /** @var FormatterFactoryInterface $factory */
                    $factory   = $this->container->get(FormatterFactoryInterface::class);
                    $formatter = $factory->createFormatter(Messages::NAMESPACE_NAME);

                    $validator = new FormValidator(
                        $rulesClass,
                        FormRulesSerializer::class,
                        $serializedData,
                        new ContextStorage(FormRulesSerializer::readBlocks($serializedData), $this->container),
                        $formatter
                    );

                    return $validator;
                }
            };
        };
    }
}
