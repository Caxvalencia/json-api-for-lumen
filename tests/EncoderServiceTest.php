<?php

namespace RealPage\JsonApi;

use Neomerx\JsonApi\Encoder\Encoder;

/**
 * @property array config
 * @property EncoderService $encoderService
 */
class EncoderServiceTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->config = [
            'schemas' => [],
            'encoders' => [
                'test-1' => [
                    'jsonapi' => true,
                    'meta' => [
                        'apiVersion' => '1.0',
                    ],
                    'encoder-options' => [
                        'options' => JSON_PRETTY_PRINT,
                        'urlPrefix' => '/',
                        'depth' => 512
                    ],
                ],
                'test-2' => [
                    'jsonapi' => [
                        'extensions' => 'bulk',
                    ],
                    'meta' => [
                        'apiVersion' => '1.0',
                    ],
                ],
            ]
        ];

        $this->encoderService = new EncoderService($this->config);
    }

    /**
     * @throws \Exception
     */
    public function testGetDefaultEncoder()
    {
        $this->assertInstanceOf(Encoder::class, $this->encoderService->getEncoder());
    }

    /**
     * @throws \Exception
     */
    public function testGetNamedEncoder()
    {
        $this->assertInstanceOf(Encoder::class, $this->encoderService->getEncoder('test-1'));
        $this->assertInstanceOf(Encoder::class, $this->encoderService->getEncoder('test-2'));
    }

    /**
     * @throws \Exception
     */
    public function testGetUnconfiguredEncoder()
    {
        $this->expectException(\Exception::class);
        $this->encoderService->getEncoder('missing');
    }

    /**
     * @throws \Exception
     */
    public function testEncoderIsSingleton()
    {
        $encoder = $this->encoderService->getEncoder();
        $this->assertSame($encoder, $this->encoderService->getEncoder());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetEncoderOptionsDefaults()
    {
        $service = new EncoderService([]);
        $method = $this->getMethod(EncoderService::class, 'getEncoderOptions');

        $encoderOptions = $method->invokeArgs($service, [[]]);

        $this->assertEquals(0, $encoderOptions->getOptions());
        $this->assertNull($encoderOptions->getUrlPrefix());
        $this->assertEquals(512, $encoderOptions->getDepth());
    }

    /**
     * @throws \ReflectionException
     */
    public function testGetEncoderOptions()
    {
        $service = new EncoderService([]);
        $method = $this->getMethod(EncoderService::class, 'getEncoderOptions');

        $configs = [
            [
                'options' => 0,
                'urlPrefix' => null,
                'depth' => 512
            ],
            [
                'options' => JSON_PRETTY_PRINT,
                'urlPrefix' => '/',
                'depth' => 1024
            ],
        ];

        foreach ($configs as $config) {
            $encoder_options = $method->invokeArgs($service, [$config]);

            $this->assertEquals($config['options'], $encoder_options->getOptions());
            $this->assertEquals($config['urlPrefix'], $encoder_options->getUrlPrefix());
            $this->assertEquals($config['depth'], $encoder_options->getDepth());
        }
    }

    /**
     * @throws \Exception
     */
    public function testSetMetaAndJsonApiVersion()
    {
        $config = [
            'schemas' => [],
        ];

        $encoderService = new EncoderService($config);
        $encoder = $encoderService->getEncoder();

        $this->assertNull($this->getProperty($encoder, 'meta'));
        $this->assertFalse($this->getProperty($encoder, 'isAddJsonApiVersion'));
        $this->assertNull($this->getProperty($encoder, 'jsonApiVersionMeta'));

        $config = [
            'schemas' => [],
            'jsonapi' => true,
            'meta' => [
                'apiVersion' => '1.0',
            ],
        ];

        $encoderService = new EncoderService($config);
        $encoder = $encoderService->getEncoder();

        $this->assertEquals($config['meta'], $this->getProperty($encoder, 'meta'));
        $this->assertEquals($config['jsonapi'], $this->getProperty($encoder, 'isAddJsonApiVersion'));
        $this->assertTrue($this->getProperty($encoder, 'jsonApiVersionMeta'));

        $config = [
            'schemas' => [],
            'jsonapi' => [
                'foo' => 'bar',
            ],
        ];

        $encoderService = new EncoderService($config);
        $encoder = $encoderService->getEncoder();

        $this->assertEquals($config['jsonapi'], $this->getProperty($encoder, 'jsonApiVersionMeta'));
        $this->assertTrue($this->getProperty($encoder, 'isAddJsonApiVersion'));
    }

    /**
     * @param $object
     * @param $name
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function getProperty($object, $name)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param $class
     * @param $name
     * @return \ReflectionMethod
     * @throws \ReflectionException
     */
    protected static function getMethod($class, $name)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
