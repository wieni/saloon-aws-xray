<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\Test\DataProvider;

use GuzzleHttp\Psr7\Response as Psr7Response;
use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\HttpSegment;
use Saloon\Http\Connectors\NullConnector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Wieni\SaloonAwsXray\DataProvider\ResponseBodyMetadataProvider;
use Wieni\SaloonAwsXray\Test\SaloonTestRequest;

class ResponseBodyMetadataProviderTest extends TestCase
{

    public function testItAddsMetadataToTheXraySegment(): void
    {
        $dataProvider = new ResponseBodyMetadataProvider();

        $request = new PendingRequest(
            new NullConnector(),
            new SaloonTestRequest(),
        );
        $response = new Response(
            new Psr7Response(
                200,
                [
                    'X-Test-Header' => 'test-header',
                    'Content-Type' => 'application/json',
                ],
                '{ "data": "test-body" }',
            ),
            $request,
            $request->createPsrRequest(),
        );
        $segment = new HttpSegment();

        $dataProvider->addXrayMetadata($segment, $response);

        $segmentData = $segment->jsonSerialize();

        $this->assertEquals(
            [
                'headers' => [
                    'X-Test-Header' => 'test-header',
                    'Content-Type' => 'application/json',
                ],
                'status' => 200,
                'body' => [
                    'data' => 'test-body',
                ],
            ],
            $segmentData['metadata']['response'],
        );

        $this->assertEquals(
            200,
            $segmentData['http']['response']['status'],
        );

    }

}