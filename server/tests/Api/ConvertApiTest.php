<?php namespace Tests\Api;

use App\Api\PdfToImageController;
use App\Routes\ApiRoutes;
use Limoncello\Testing\JsonApiCallsTrait;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;
use Zend\Diactoros\UploadedFile;

/**
 * @package Tests
 */
class ConvertApiTest extends TestCase
{
    use JsonApiCallsTrait;

    /**
     * Test convert API.
     */
    public function testConvertValid()
    {
        $response = $this->callConvert(
            $this->getTestFullFileName('valid_sample.pdf'),
            500,
            500
        );
        $this->assertEquals(200, $response->getStatusCode(), (string)$response->getBody());
    }

    /**
     * Test convert API.
     */
    public function testConvertInvalidFile()
    {
        $response = $this->callConvert(
            $this->getTestFullFileName('invalid_sample.pdf'),
            500,
            500
        );
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString('invalid mime type', json_encode(json_decode((string)$response->getBody())));
    }

    /**
     * Test convert API.
     */
    public function testConvertInvalidResolution()
    {
        $response = $this->callConvert(
            $this->getTestFullFileName('valid_sample.pdf'),
            -1,
            500
        );
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertStringContainsString(
            'Invalid input parameter `width`. The value should be between ',
            json_encode(json_decode((string)$response->getBody()))
        );
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getTestFullFileName(string $name): string
    {
        return implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'Files', $name]);
    }

    /**
     * @param string $filePath
     * @param int    $width
     * @param int    $height
     * @param int    $uploadErrorStatus
     *
     * @return ResponseInterface
     */
    private function callConvert(
        string $filePath,
        int $width,
        int $height,
        int $uploadErrorStatus = UPLOAD_ERR_OK
    ): ResponseInterface {
        $files = [
            new UploadedFile(
                $filePath,
                filesize($filePath),
                $uploadErrorStatus,
                basename($filePath)
            )
        ];

        return $this->call(
            'POST',
            ApiRoutes::API_URI_PREFIX . '/' . ApiRoutes::API_URL__CONVERT ,
            [], // query params
            [PdfToImageController::PARAM_WIDTH => $width, PdfToImageController::PARAM_HEIGHT => $height], // parsed body
            [], // headers
            [], // cookies
            $files
        );
    }
}
