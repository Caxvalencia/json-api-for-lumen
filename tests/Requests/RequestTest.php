<?php

namespace RealPage\JsonApi\Requests;

use Illuminate\Http\Request as IlluminateRequest;
use Mockery;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use PHPUnit\Framework\TestCase;
use RealPage\JsonApi\Authorization\RequestFailedAuthorization;
use RealPage\JsonApi\Validation\RequestFailedValidation;
use RealPage\JsonApi\Validation\ValidatesRequests;

class RequestTest extends TestCase
{
    /** @var Mockery\MockInterface */
    protected $illuminateRequest;

    /** @var \RealPage\JsonApi\Requests\Request */
    protected $request;

    /** @var \RealPage\JsonApi\Requests\Request */
    protected $validator;

    public function setUp()
    {
        parent::setUp();

        $this->illuminateRequest = Mockery::mock(IlluminateRequest::class);
        $this->request = new Request($this->illuminateRequest);
    }

    /** @test */
    public function suppliesRequest()
    {
        $this->assertEquals($this->illuminateRequest, $this->request->request());
    }

    /** @test */
    public function suppliesValidator()
    {
        $validator = Mockery::mock(ValidatesRequests::class);

        /** @var ValidatesRequests $validator */
        $this->request->setValidator($validator);

        $this->assertEquals($validator, $this->request->validator());
    }

    /** @test */
    public function decodesJson()
    {
        $this->illuminateRequest->shouldReceive('getContent')->andReturn('{"Hello": "world"}');

        $this->assertEquals(['Hello' => 'world'], $this->request->json());
    }

    /** @test */
    public function throwsMalformedRequestWhenJsonIsInvalid()
    {
        $this->illuminateRequest->shouldReceive('getContent')->andReturn('{"Hello": "world""}');

        $error = null;

        try {
            $this->request->json();
        } catch (JsonApiException $e) {
            $this->assertEquals(1, $e->getErrors()->count());
            $error = $e->getErrors()->getArrayCopy()[0];
        }

        $this->assertInstanceOf(MalformedRequest::class, $error);
    }

    /** @test */
    public function canPassValidation()
    {
        $validator = Mockery::mock(ValidatesRequests::class);
        $validator->shouldReceive('isValid')->with($this->request)->andReturn(true);

        /** @var ValidatesRequests $validator */
        $this->request->setValidator($validator);

        $exception = null;

        try {
            $this->request->validate();
        } catch (RequestFailedValidation $e) {
            $exception = $e;
        }

        $this->assertNull($exception);

    }

    /** @test */
    public function throwsExceptionWhenValidationIsInvalid()
    {
        $errors = new ErrorCollection();
        $validator = Mockery::mock(ValidatesRequests::class);
        $validator->shouldReceive('isValid')->with($this->request)->andReturn(false);
        $validator->shouldReceive('errors')->andReturn($errors);

        /** @var ValidatesRequests $validator */
        $this->request->setValidator($validator);

        $exception = null;
        try {
            $this->request->validate();
        } catch (RequestFailedValidation $e) {
            $exception = $e;
        }

        $this->assertInstanceOf(RequestFailedValidation::class, $exception);
        $this->assertEquals($errors, $exception->getErrors());
    }

    /** @test */
    public function authorizeShouldCheckPolicy()
    {
        $action = 'action';
        $object = new \stdClass;

        $this->illuminateRequest
            ->shouldReceive('user->cant')
            ->with($action, $object)
            ->andReturn(false);

        $this->assertTrue($this->request->authorize($action, $object));
    }

    /** @test */
    public function authorizeShouldSucceedIfAuthorized()
    {
        $action = 'action';
        $object = new \stdClass;

        $this->illuminateRequest
            ->shouldReceive('user->cant')
            ->with($action, $object)
            ->andReturn(false); // authorized

        $result = $this->request->authorize($action, $object);
        $this->assertEquals(true, $result);
    }

    /** @test */
    public function authorizeShouldFailIfUnauthorized()
    {
        $action = 'action';
        $object = new \stdClass;

        $this->illuminateRequest
            ->shouldReceive('user->cant')
            ->with($action, $object)
            ->andReturn(true); // unauthorized

        $this->expectException(RequestFailedAuthorization::class);
        $this->request->authorize($action, $object);
    }

    /** @test */
    public function authorizeShouldThrowExceptionWithError()
    {
        $expectedError = new Error(
            $id = null,
            $link = new Link('https://tools.ietf.org/html/rfc7231#section-6.5.3'),
            $status = '403',
            $code = null,
            $title = 'Forbidden',
            $desc = 'Access is denied for one or more of the specified resources',
            $source = null,
            $meta = null
        );

        $action = 'action';
        $object = new \stdClass;

        $this->illuminateRequest
            ->shouldReceive('user->cant')
            ->with($action, $object)
            ->andReturn(true); // unauthorized

        $errors = [0 => null];

        try {
            $this->request->authorize($action, $object);
        } catch (JsonApiException $e) {
            $errors = $e->getErrors();
        }

        $this->assertEquals($expectedError, $errors[0]);
    }
}
