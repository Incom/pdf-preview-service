<?php namespace App\Api;

use App\ImageResolutionRules;
use App\Routes\ApiRoutes;
use Imagick;
use ImagickException;
use Intervention\Image\Constraint;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use Limoncello\Contracts\FileSystem\FileSystemInterface;
use Limoncello\Flute\Contracts\Http\Controller\ControllerCreateInterface;
use Limoncello\Flute\Contracts\Http\Controller\ControllerIndexInterface;
use Limoncello\Flute\Contracts\Validation\FormValidatorFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Throwable;
use Zend\Diactoros\Response\HtmlResponse;
use function array_intersect_key;
use function assert;
use function count;
use function finfo_close;
use function finfo_file;
use function finfo_open;
use function is_array;
use function reset;
use function sys_get_temp_dir;
use function tempnam;

/**
 * @package App
 */
class PdfToImageController implements ControllerCreateInterface, ControllerIndexInterface
{
    /** @var string PDF mime */
    const MIME_PDF = 'application/pdf';

    /** @var string PDF mime */
    const MIME_JPG = 'image/jpeg';

    /** @var string Temporary files prefix */
    const TMP_FILE_PREFIX = 'pdf_to_image__';

    /** @var string Route key */
    const PARAM_WIDTH = 'width';

    /** @var string Route key */
    const PARAM_HEIGHT = 'height';

    /** @var string Convert parameter */
    const PARAM_QUALITY = 'quality';

    /** @var string Convert parameter */
    const PARAM_RESOLUTION = 'resolution';

    /** @var int Resolution limit */
    const MIN_WIDTH = 1;

    /** @var int Resolution limit */
    const MAX_WIDTH = 4096;

    /** @var int Resolution limit */
    const MIN_HEIGHT = 1;

    /** @var int Resolution limit */
    const MAX_HEIGHT = 2160;

    /** @var int Resolution limit */
    const MIN_QUALITY = 0;

    /** @var int Resolution limit */
    const MAX_QUALITY = 100;

    /** @var int Resolution limit */
    const MIN_RESOLUTION = 100;

    /** @var int Resolution limit */
    const MAX_RESOLUTION = 500;

    /** @var int Size param for image convert */
    const DEFAULT_WIDTH = 1920;

    /** @var int Size param for image convert */
    const DEFAULT_HEIGHT = 1080;

    /** @var int Quality param for image convert */
    const DEFAULT_QUALITY = 90;

    /** @var int Image resolution param for image convert */
    const DEFAULT_RESOLUTION = 300;

    /** @var int HTTP code */
    private const HTTP_BAD_REQUEST = 400;

    /** @var int HTTP code */
    private const HTTP_UNPROCESSABLE_ENTITY = 422;

    /**
     * @inheritdoc
     */
    public static function index(
        array $routeParams,
        ContainerInterface $container,
        ServerRequestInterface $request
    ): ResponseInterface
    {
        $uploadUrl       = ApiRoutes::API_URI_PREFIX . '/' . ApiRoutes::API_URL__CONVERT;
        $widthName       = static::PARAM_WIDTH;
        $widthValue      = static::DEFAULT_WIDTH;
        $heightName      = static::PARAM_HEIGHT;
        $heightValue     = static::DEFAULT_HEIGHT;
        $qualityName     = static::PARAM_QUALITY;
        $qualityValue    = static::DEFAULT_QUALITY;
        $resolutionName  = static::PARAM_RESOLUTION;
        $resolutionValue = static::DEFAULT_RESOLUTION;

        $body = <<<EOT
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>Upload File</title>
    </head>
    <body>
        <form action="$uploadUrl" enctype="multipart/form-data" method="POST">
            <input type="file" name="file"/><br>
            Width:&nbsp;<input type="number" name="$widthName" value="$widthValue"/><br>
            Height:&nbsp;<input type="number" name="$heightName" value="$heightValue"/><br>
            Quality:&nbsp;<input type="number" name="$qualityName" value="$qualityValue"/><br>
            Resolution:&nbsp;<input type="number" name="$resolutionName" value="$resolutionValue"/><br>
            <input type="submit" name="upload" value="Upload" />
        </form>
    </body>
</html>
EOT;

        return new HtmlResponse($body);
    }

    /**
     * @inheritdoc
     */
    public static function create(
        array $routeParams,
        ContainerInterface $container,
        ServerRequestInterface $request
    ): ResponseInterface {
        $fileName = static::getUploadedFileFromInput($request);
        try {
            $mime = static::getMimeType($fileName);
            if ($mime !== static::MIME_PDF) {
                throw static::createException(
                    'The uploaded file has invalid mime type.',
                    static::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // filter only known params
            $parsedBody = array_intersect_key(
                $request->getParsedBody(),
                [
                    static::PARAM_WIDTH      => true,
                    static::PARAM_HEIGHT     => true,
                    static::PARAM_QUALITY    => true,
                    static::PARAM_RESOLUTION => true
                ]
            );

            $imageParams = static::getValidatedImageParams($parsedBody, $container);
            $width       = $imageParams[static::PARAM_WIDTH] ?? static::DEFAULT_WIDTH;
            $height      = $imageParams[static::PARAM_HEIGHT] ?? static::DEFAULT_HEIGHT;
            $quality     = $imageParams[static::PARAM_QUALITY] ?? static::DEFAULT_QUALITY;
            $resolution  = $imageParams[static::PARAM_RESOLUTION] ?? static::DEFAULT_RESOLUTION;

            $source = new Imagick();
            $source->setResolution($resolution, $resolution);
            $source->readImage($fileName . '[0]'); // first page from pdf

            $image  = (new ImageManager(['driver' => 'imagick']))->make($source);
            $image->resize($width, $height, function (Constraint $constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->orientate();

            return $image->psrResponse(static::MIME_JPG, $quality);

        } catch (ImagickException | ImageException $exception) {
            throw static::createException('Error while converting input file.', static::HTTP_BAD_REQUEST, $exception);
        } finally {
            /** @var FileSystemInterface $fs */
            $fs = $container->get(FileSystemInterface::class);
            $fs->delete($fileName);
        }
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    private static function getUploadedFileFromInput(ServerRequestInterface $request): string
    {
        if (empty($files = $request->getUploadedFiles()) === true) {
            throw static::createException('No uploaded file.', static::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (count($files) > 1) {
            throw static::createException(
                'Too many uploaded files. Please send 1 file.',
                static::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ((($file = reset($files)) instanceof UploadedFileInterface) === false) {
            throw static::createException(
                'Uploaded file is incorrect.',
                static::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        assert($file instanceof UploadedFileInterface);

        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw static::createException(
                'The file was uploaded unsuccessfully.',
                static::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (($tmpFileName = tempnam(sys_get_temp_dir(), static::TMP_FILE_PREFIX)) === false) {
            throw static::createException(
                'Error while while allocating temporary file.',
                static::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $file->moveTo($tmpFileName);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            throw static::createException(
                'Error while saving the uploaded file as a temporary.',
                static::HTTP_BAD_REQUEST,
                $exception
            );
        }

        return $tmpFileName;
    }

    /**
     * @param string $path
     *
     * @return null|string
     */
    private static function getMimeType(string $path): ?string
    {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        try {
            $mime = finfo_file($fileInfo, $path);

            return $mime !== false ? $mime : null;
        } finally {
            finfo_close($fileInfo);
        }
    }

    /**
     * @param array|object|null  $parsedBody
     * @param ContainerInterface $container
     *
     * @return array
     */
    private static function getValidatedImageParams($parsedBody, ContainerInterface $container): array
    {
        if (is_array($parsedBody) === false) {
            throw static::createException('Invalid input image parameters.', static::HTTP_UNPROCESSABLE_ENTITY);
        }

        $factory = $container->get(FormValidatorFactoryInterface::class);
        assert($factory instanceof FormValidatorFactoryInterface);
        $validator = $factory->createValidator(ImageResolutionRules::class);

        if ($validator->validate($parsedBody) === false) {
            foreach ($validator->getMessages() as $name => $message) {
                throw static::createException(
                    "Invalid input parameter `$name`. " . $message,
                    static::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        $parameters = $validator->getCaptures();

        return $parameters;
    }

    /**
     * @param string         $message
     * @param int            $status
     * @param Throwable|null $throwable
     *
     * @return ApplicationException
     */
    private static function createException(
        string $message,
        int $status,
        Throwable $throwable = null
    ): ApplicationException {
        return new ApplicationException($message, $status, $throwable);
    }
}
