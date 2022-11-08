<?php declare(strict_types=1);

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\Faking\MockClient;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Http\Faking\MockResponse;
use Sammyjo20\Saloon\Exceptions\InvalidPoolItemException;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

it('accepts an array for requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = [
        new UserRequest,
        new UserRequest,
        new UserRequest,
    ];

    $pool = $connector->pool($requests);

    $pool->setConcurrency(5);

    $pool->withResponseHandler(function (SaloonResponse $response, int $index) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$index]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
});

it('accepts an array for aliased requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = [
        'a' => new UserRequest,
        'b' => new UserRequest,
        'c' => new UserRequest,
    ];

    $pool = $connector->pool($requests);

    $pool->setConcurrency(5);

    $pool->withResponseHandler(function (SaloonResponse $response, string $name) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$name]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
    expect($requests)->toHaveKeys(['a', 'b', 'c']);
});

it('accepts a generator for requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = collect([]);

    $generatorCallback = function () use ($requests): Generator {
        for ($i = 0; $i < 3; $i++) {
            $request = new UserRequest;
            $requests->put($i, $request);

            yield $i => $request;
        }
    };

    expect($generatorCallback)->toBeCallable();
    expect($generatorCallback())->toBeInstanceOf(Generator::class);

    $pool = $connector->pool($generatorCallback());
    $pool->setConcurrency(5);
    $pool->withResponseHandler(function (SaloonResponse $response, int $index) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$index]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
});

it('accepts a generator for aliased requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = collect();

    $generatorCallback = function () use ($requests): Generator {
        for ($name = 'a'; $name !== 'd'; $name++) {
            $request = new UserRequest;
            $requests->put($name, $request);

            yield $name => $request;
        }
    };

    expect($generatorCallback)->toBeCallable();
    expect($generatorCallback())->toBeInstanceOf(Generator::class);

    $pool = $connector->pool($generatorCallback());
    $pool->setConcurrency(5);
    $pool->withResponseHandler(function (SaloonResponse $response, string $name) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$name]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
    expect($requests)->toHaveKeys(['a', 'b', 'c']);
});

it('accepts a callback that returns an array for requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = collect();

    $arrayCallback = function () use (&$requests) {
        $requests = $requests->merge([
            new UserRequest,
            new UserRequest,
            new UserRequest,
        ]);

        return $requests->all();
    };

    expect($arrayCallback)->toBeCallable();
    expect($requests->all())->toBeArray();

    $pool = $connector->pool($arrayCallback);

    $pool->setConcurrency(5);

    $pool->withResponseHandler(function (SaloonResponse $response, int $index) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$index]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
});

it('accepts a callback that returns an array for aliased requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = collect();

    $arrayCallback = function (SaloonConnector $callbackConnector) use (&$requests, $connector) {
        expect($callbackConnector)->toEqual($connector);

        $requests = $requests->merge([
            'a' => new UserRequest,
            'b' => new UserRequest,
            'c' => new UserRequest,
        ]);

        return $requests->all();
    };

    expect($arrayCallback)->toBeCallable();
    expect($requests->all())->toBeArray();

    $pool = $connector->pool($arrayCallback);
    $pool->setConcurrency(5);
    $pool->withResponseHandler(function (SaloonResponse $response, string $name) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$name]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
    expect($requests->all())->toHaveKeys(['a', 'b', 'c']);
});

it('accepts a callback that returns a generator for requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = collect();

    $generatorCallback = function () use ($requests): Generator {
        for ($i = 0; $i < 3; $i++) {
            $request = new UserRequest;
            $requests->put($i, $request);

            yield $i => $request;
        }
    };

    expect($generatorCallback)->toBeCallable();
    expect($generatorCallback())->toBeInstanceOf(Generator::class);

    $pool = $connector->pool($generatorCallback);
    $pool->setConcurrency(5);
    $pool->withResponseHandler(function (SaloonResponse $response, int $index) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$index]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
});

it('accepts a callback that returns a generator for aliased requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Mantas']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $count = 0;

    $requests = collect();

    $generatorCallback = function () use ($requests): Generator {
        for ($name = 'a'; $name !== 'd'; $name++) {
            $request = new UserRequest;
            $requests->put($name, $request);

            yield $name => $request;
        }
    };

    expect($generatorCallback)->toBeCallable();
    expect($generatorCallback())->toBeInstanceOf(Generator::class);

    $pool = $connector->pool($generatorCallback);
    $pool->setConcurrency(5);
    $pool->withResponseHandler(function (SaloonResponse $response, string $name) use ($requests, &$count) {
        expect($response->getRequest())->toBe($requests[$name]);

        $count++;
    });

    $pool->send()->wait();

    expect($count)->toBe(3);
    expect($requests->all())->toHaveKeys(['a', 'b', 'c']);
});

test('throws an exception if an invalid item is passed into the iterator', function () {
    $connector = new TestConnector;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new TestConnector,
    ]);

    expect(fn () => $pool->send()->wait())->toThrow(InvalidPoolItemException::class);
});
