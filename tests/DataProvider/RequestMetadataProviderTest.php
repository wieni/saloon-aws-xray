<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\Test\DataProvider;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\HttpSegment;
use Saloon\Http\Connectors\NullConnector;
use Saloon\Http\PendingRequest;
use Wieni\SaloonAwsXray\DataProvider\RequestMetadataProvider;
use Wieni\SaloonAwsXray\Test\SaloonTestRequest;

class RequestMetadataProviderTest extends TestCase
{

    public function testItAddsMetadataToTheXraySegment(): void
    {
        $dataProvider = new RequestMetadataProvider();
        $request = new PendingRequest(
            new NullConnector(),
            new SaloonTestRequest(),
        );
        $segment = new HttpSegment();

        $dataProvider->addXrayMetadata($segment, $request);

        $segmentData = $segment->jsonSerialize();

        $this->assertEquals(
            [
                'query_string' => [
                    'X-Test-Query' => 'test-query',
                ],
                'headers' => [
                    'X-Test-Header' => 'test-header',
                    'Content-Type' => 'application/json',
                ],
                'body' => [
                    'data' => 'test-body',
                ],
            ],
            $segmentData['metadata']['request'],
        );

        $this->assertEquals(
            'www.foobar.com',
            $segmentData['annotations']['Host'],
        );

        $this->assertEquals(
            'https://www.foobar.com/foobar',
            $segmentData['http']['request']['url'],
        );

        $this->assertEquals(
            'GET',
            $segmentData['http']['request']['method'],
        );

    }

}