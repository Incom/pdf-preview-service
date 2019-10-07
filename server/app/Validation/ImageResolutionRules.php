<?php namespace App;

use App\Api\PdfToImageController as c;
use Limoncello\Flute\Contracts\Validation\FormRulesInterface;
use Limoncello\Validation\Rules as r;

/**
 * @package App
 */
class ImageResolutionRules implements FormRulesInterface
{
    /**
     * @inheritdoc
     */
    public static function getAttributeRules(): array
    {
        return [
            c::PARAM_WIDTH      => r::stringToInt(r::between(c::MIN_WIDTH, c::MAX_WIDTH)),
            c::PARAM_HEIGHT     => r::stringToInt(r::between(c::MIN_HEIGHT, c::MAX_HEIGHT)),
            c::PARAM_QUALITY    => r::stringToInt(r::between(c::MIN_QUALITY, c::MAX_QUALITY)),
            c::PARAM_RESOLUTION => r::stringToInt(r::between(c::MIN_RESOLUTION, c::MAX_RESOLUTION)),
        ];
    }
}
