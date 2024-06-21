<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\DataProvider;

use Pkerrigan\Xray\Segment;
use Saloon\Http\Response;
use Wieni\SaloonAwsXray\XrayDataProviderInterface;

class ResponseBodyMetadataProvider implements XrayDataProviderInterface
{

    public function addXrayMetadata(Segment $segment, Response $response): void
    {
        // Re-use the ResponseMetadataProvider because we will overwrite
        // the 'response' metadata attribute of the segment
        $metaData = (new ResponseMetadataProvider())->addXrayMetadata(
            $segment,
            $response
        );

        $responseBody = $response->body();
        if (str_contains($response->header('Content-Type') ?? '', 'json')) {
            $responseBody = $response->array();
        }

        // Add the response body to the metadata and overwrite the response
        // metadata on the segment
        $metaData['body'] = $responseBody;
        $segment->addMetadata('response', $metaData);
    }

}
