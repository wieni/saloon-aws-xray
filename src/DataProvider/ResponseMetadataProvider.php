<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\DataProvider;

use Pkerrigan\Xray\HttpSegment;
use Pkerrigan\Xray\Segment;
use Saloon\Http\Response;

/**
 * This data provider is always being applied.
 *
 * Adds metadata to the Xray segment with information about the http response.
 * 
 * @see \Wieni\SaloonAwsXray\XrayHttpSender::finalise()
 */
class ResponseMetadataProvider
{

    public function addXrayMetadata(Segment $segment, Response $response): array
    {
        $metaData = [
            'status' => $response->status(),
            'headers' => $response->headers()->all(),
        ];

        if ($segment instanceof HttpSegment) {
            $segment->setResponseCode($response->status());
        }

        $segment->addMetadata('response', $metaData);

        return $metaData;
    }

}