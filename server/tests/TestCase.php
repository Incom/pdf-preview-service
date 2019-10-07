<?php namespace Tests;

use App\Application;
use Limoncello\Contracts\Container\ContainerInterface;
use Limoncello\Contracts\Core\ApplicationInterface;
use Limoncello\Testing\ApplicationWrapperInterface;
use Limoncello\Testing\ApplicationWrapperTrait;
use Limoncello\Testing\HttpCallsTrait;
use Limoncello\Testing\MeasureExecutionTimeTrait;
use Limoncello\Testing\Sapi;
use Limoncello\Testing\TestCaseTrait;
use Mockery;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

/**
 * @package Tests
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use TestCaseTrait, HttpCallsTrait, MeasureExecutionTimeTrait;

    /** @var null|PsrContainerInterface */
    private $appContainer = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetEventHandlers();

        $this->addOnContainerConfiguredEvent(function (ApplicationInterface $app, ContainerInterface $container) {
            assert($app);
            $this->appContainer = $container;
        });
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->appContainer = null;

        $this->resetEventHandlers();

        Mockery::close();
    }

    /**
     * Returns application container.
     *
     * @return PsrContainerInterface
     */
    protected function getAppContainer(): PsrContainerInterface
    {
        return $this->appContainer;
    }

    /**
     * @inheritdoc
     */
    protected function createApplication(): ApplicationInterface
    {
        $wrapper = new class extends Application implements ApplicationWrapperInterface
        {
            use ApplicationWrapperTrait;
        };

        foreach ($this->getHandleRequestEvents() as $handler) {
            $wrapper->addOnHandleRequest($handler);
        }

        foreach ($this->getHandleResponseEvents() as $handler) {
            $wrapper->addOnHandleResponse($handler);
        }

        foreach ($this->getContainerCreatedEvents() as $handler) {
            $wrapper->addOnContainerCreated($handler);
        }

        foreach ($this->getContainerConfiguredEvents() as $handler) {
            $wrapper->addOnContainerLastConfigurator($handler);
        }

        return $wrapper;
    }

    /**
     * @inheritdoc
     */
    protected function createSapi(
        array $server = null,
        array $queryParams = null,
        array $parsedBody = null,
        array $cookies = null,
        array $files = null,
        $messageBody = 'php://input',
        string $protocolVersion = '1.1'
    ): Sapi {
        /** @var EmitterInterface $emitter */
        $emitter = Mockery::mock(EmitterInterface::class);

        $sapi =
            new Sapi($emitter, $server, $queryParams, $parsedBody, $cookies, $files, $messageBody, $protocolVersion);

        return $sapi;
    }
}
