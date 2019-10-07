<?php namespace Settings;

use Limoncello\Common\Reflection\ClassIsTrait;
use Limoncello\Contracts\Settings\SettingsInterface;
use Limoncello\Flute\Contracts\Validation\FormRulesInterface;
use Limoncello\Flute\Validation\Form\Execution\FormRulesSerializer;
use Limoncello\Validation\Execution\BlockSerializer;
use ReflectionException;

/**
 * @package Settings
 */
class Validation implements SettingsInterface
{
    use ClassIsTrait;

    /** @var int Config key */
    const KEY_FORM_VALIDATORS_RULES_DATA = 0;

    /**
     * @inheritdoc
     *
     * @throws ReflectionException
     */
    public function get(array $appConfig): array
    {
        $formValFolder = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'app', 'Validation', '**.php']);

        return [
            static::KEY_FORM_VALIDATORS_RULES_DATA => static::serializeFormValidationRules($formValFolder),
        ];
    }

    /**
     * @param string $formsValPath
     *
     * @return array
     *
     * @throws ReflectionException
     */
    private function serializeFormValidationRules(string $formsValPath): array
    {
        $serializer = new FormRulesSerializer(new BlockSerializer());

        foreach ($this->selectClasses($formsValPath, FormRulesInterface::class) as $rulesClass) {
            $serializer->addRulesFromClass($rulesClass);
        }

        return $serializer->getData();
    }
}
