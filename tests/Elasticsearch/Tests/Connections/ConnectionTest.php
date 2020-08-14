<?php
/**
 * Elasticsearch PHP client
 *
 * @link      https://github.com/elastic/elasticsearch-php/
 * @copyright Copyright (c) Elasticsearch B.V (https://www.elastic.co)
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license   https://www.gnu.org/licenses/lgpl-2.1.html GNU Lesser General Public License, Version 2.1
 *
 * Licensed to Elasticsearch B.V under one or more agreements.
 * Elasticsearch B.V licenses this file to you under the Apache 2.0 License or
 * the GNU Lesser General Public License, Version 2.1, at your option.
 * See the LICENSE file in the project root for more information.
 */


declare(strict_types = 1);

namespace Elasticsearch\Tests\Connections;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\ServerErrorResponseException;
use Elasticsearch\Connections\Connection;
use Elasticsearch\Serializers\SerializerInterface;
use Elasticsearch\Serializers\SmartSerializer;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&\Psr\Log\LoggerInterface
     */
    private $trace;
    /**
     * @var \Elasticsearch\Serializers\SerializerInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private $serializer;

    protected function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->trace = $this->createMock(LoggerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
    }

    /**
     * @covers \Connection
     */
    public function testConstructor()
    {
        $host = [
            'host' => 'localhost'
        ];

        $connection = new Connection(
            function () {
            },
            $host,
            [],
            $this->serializer,
            $this->logger,
            $this->trace
        );

        $this->assertInstanceOf(Connection::class, $connection);
    }

    /**
     * @depends testConstructor
     *
     * @covers \Connection::getHeaders
     */
    public function testGetHeadersContainUserAgent()
    {
        $params = [];
        $host = [
            'host' => 'localhost'
        ];

        $connection = new Connection(
            function () {
            },
            $host,
            $params,
            $this->serializer,
            $this->logger,
            $this->trace
        );

        $headers = $connection->getHeaders();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertContains('elasticsearch-php/'. Client::VERSION, $headers['User-Agent'][0]);
    }

    /**
     * @depends testGetHeadersContainUserAgent
     *
     * @covers \Connection::getHeaders
     * @covers \Connection::performRequest
     * @covers \Connection::getLastRequestInfo
     */
    public function testUserAgentHeaderIsSent()
    {
        $params = [];
        $host = [
            'host' => 'localhost'
        ];

        $connection = new Connection(
            ClientBuilder::defaultHandler(),
            $host,
            $params,
            $this->serializer,
            $this->logger,
            $this->trace
        );
        $result  = $connection->performRequest('GET', '/');
        $request = $connection->getLastRequestInfo()['request'];

        $this->assertArrayHasKey('User-Agent', $request['headers']);
        $this->assertContains('elasticsearch-php/'. Client::VERSION, $request['headers']['User-Agent'][0]);
    }

    /**
     * @depends testConstructor
     *
     * @covers \Connection::getHeaders
     * @covers \Connection::performRequest
     * @covers \Connection::getLastRequestInfo
     */
    public function testGetHeadersContainsHostArrayConfig()
    {
        $host = [
            'host' => 'localhost',
            'user' => 'foo',
            'pass' => 'bar',
        ];

        $connection = new Connection(
            ClientBuilder::defaultHandler(),
            $host,
            [],
            $this->serializer,
            $this->logger,
            $this->trace
        );
        $result  = $connection->performRequest('GET', '/');
        $request = $connection->getLastRequestInfo()['request'];

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $request['client']['curl']);
        $this->assertArrayHasKey(CURLOPT_USERPWD, $request['client']['curl']);
        $this->assertArrayNotHasKey('Authorization', $request['headers']);
        $this->assertContains('foo:bar', $request['client']['curl'][CURLOPT_USERPWD]);
    }

    /**
     * @depends testGetHeadersContainsHostArrayConfig
     *
     * @covers \Connection::getHeaders
     * @covers \Connection::performRequest
     * @covers \Connection::getLastRequestInfo
     */
    public function testGetHeadersContainApiKeyAuth()
    {
        $params = ['client' => ['headers' => [
            'Authorization' => [
                'ApiKey ' . base64_encode(sha1((string)time()))
            ]
        ] ] ];
        $host = [
            'host' => 'localhost'
        ];

        $connection = new Connection(
            ClientBuilder::defaultHandler(),
            $host,
            $params,
            $this->serializer,
            $this->logger,
            $this->trace
        );
        $result  = $connection->performRequest('GET', '/');
        $request = $connection->getLastRequestInfo()['request'];

        $this->assertArrayHasKey('Authorization', $request['headers']);
        $this->assertArrayNotHasKey(CURLOPT_HTTPAUTH, $request['headers']);
        $this->assertContains($params['client']['headers']['Authorization'][0], $request['headers']['Authorization'][0]);
    }

    /**
     * @depends testGetHeadersContainApiKeyAuth
     *
     * @covers \Connection::getHeaders
     * @covers \Connection::performRequest
     * @covers \Connection::getLastRequestInfo
     */
    public function testGetHeadersContainApiKeyAuthOverHostArrayConfig()
    {
        $params = ['client' => ['headers' => [
            'Authorization' => [
                'ApiKey ' . base64_encode(sha1((string)time()))
            ]
        ] ] ];
        $host = [
            'host' => 'localhost',
            'user' => 'foo',
            'pass' => 'bar',
        ];

        $connection = new Connection(
            ClientBuilder::defaultHandler(),
            $host,
            $params,
            $this->serializer,
            $this->logger,
            $this->trace
        );
        $result  = $connection->performRequest('GET', '/');
        $request = $connection->getLastRequestInfo()['request'];

        $this->assertArrayHasKey('Authorization', $request['headers']);
        $this->assertArrayNotHasKey(CURLOPT_HTTPAUTH, $request['headers']);
        $this->assertContains($params['client']['headers']['Authorization'][0], $request['headers']['Authorization'][0]);
    }

    /**
     * @depends testGetHeadersContainsHostArrayConfig
     *
     * @covers \Connection::getHeaders
     * @covers \Connection::performRequest
     * @covers \Connection::getLastRequestInfo
     */
    public function testGetHeadersContainBasicAuth()
    {
        $params = ['client' => ['curl' => [
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => 'username:password',
        ] ] ];
        $host = [
            'host' => 'localhost'
        ];

        $connection = new Connection(
            ClientBuilder::defaultHandler(),
            $host,
            $params,
            $this->serializer,
            $this->logger,
            $this->trace
        );
        $result  = $connection->performRequest('GET', '/');
        $request = $connection->getLastRequestInfo()['request'];

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $request['client']['curl']);
        $this->assertArrayHasKey(CURLOPT_USERPWD, $request['client']['curl']);
        $this->assertArrayNotHasKey('Authorization', $request['headers']);
        $this->assertContains($params['client']['curl'][CURLOPT_USERPWD], $request['client']['curl'][CURLOPT_USERPWD]);
    }

    /**
     * @depends testGetHeadersContainBasicAuth
     *
     * @covers \Connection::getHeaders
     * @covers \Connection::performRequest
     * @covers \Connection::getLastRequestInfo
     */
    public function testGetHeadersContainBasicAuthOverHostArrayConfig()
    {
        $params = ['client' => ['curl' => [
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD  => 'username:password',
        ] ] ];
        $host = [
            'host' => 'localhost',
            'user' => 'foo',
            'pass' => 'bar',
        ];

        $connection = new Connection(
            ClientBuilder::defaultHandler(),
            $host,
            $params,
            $this->serializer,
            $this->logger,
            $this->trace
        );
        $result  = $connection->performRequest('GET', '/');
        $request = $connection->getLastRequestInfo()['request'];

        $this->assertArrayHasKey(CURLOPT_HTTPAUTH, $request['client']['curl']);
        $this->assertArrayHasKey(CURLOPT_USERPWD, $request['client']['curl']);
        $this->assertArrayNotHasKey('Authorization', $request['headers']);
        $this->assertContains('username:password', $request['client']['curl'][CURLOPT_USERPWD]);
    }

    /**
     * @see https://github.com/elastic/elasticsearch-php/issues/977
     */
    public function testTryDeserializeErrorWithMasterNotDiscoveredException()
    {
        $host = [
            'host' => 'localhost'
        ];

        $connection = new Connection(
            function () {
            },
            $host,
            [],
            new SmartSerializer(),
            $this->logger,
            $this->trace
        );

        $reflection = new ReflectionClass(Connection::class);
        $tryDeserializeError = $reflection->getMethod('tryDeserializeError');
        $tryDeserializeError->setAccessible(true);

        $body = '{"error":{"root_cause":[{"type":"master_not_discovered_exception","reason":null}],"type":"master_not_discovered_exception","reason":null},"status":503}';
        $response = [
            'transfer_stats' => [],
            'status' => 503,
            'body' => $body
        ];

        $result = $tryDeserializeError->invoke($connection, $response, ServerErrorResponseException::class);
        $this->assertInstanceOf(ServerErrorResponseException::class, $result);
        $this->assertContains('master_not_discovered_exception', $result->getMessage());
    }
}
