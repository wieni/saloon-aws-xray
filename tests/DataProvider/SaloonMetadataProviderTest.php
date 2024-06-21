<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\Test\DataProvider;

use PHPUnit\Framework\TestCase;
use Pkerrigan\Xray\HttpSegment;
use Saloon\Http\Connectors\NullConnector;
use Saloon\Http\PendingRequest;
use Wieni\SaloonAwsXray\DataProvider\SaloonMetadataProvider;
use Wieni\SaloonAwsXray\Test\SaloonTestRequest;

class SaloonMetadataProviderTest extends TestCase
{

    public function testItAddsMetadataToTheXraySegment(): void
    {
        $dataProvider = new SaloonMetadataProvider();

        $request = new PendingRequest(
            new NullConnector(),
            new SaloonTestRequest(),
        );
        $segment = new HttpSegment();

        $dataProvider->addXrayMetadata($segment, $request);

        $segmentData = $segment->jsonSerialize();

        $this->assertEquals(
            'Saloon',
            $segmentData['annotations']['framework'],
        );

        $this->assertEquals(
            SaloonTestRequest::class,
            $segmentData['annotations']['SaloonRequest'],
        );

    }

}