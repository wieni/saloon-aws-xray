<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\DataProvider;

use Pkerrigan\Xray\HttpSegment;
use Pkerrigan\Xray\Segment;
use Saloon\Http\PendingRequest;

/**
 * This data provider is always being applied.
 *
 * Adds metadata to the Xray segment with information about the http request.
 *
 * @see \Wieni\SaloonAwsXray\XrayHttpSender::createHttpSegment()
 */
class RequestMetadataProvider
{

    public function addXrayMetadata(Segment $segment, PendingRequest $pendingRequest): array
    {
        $metaData = [
            'query_string' => $pendingRequest->query()->all(),
            'headers' => $pendingRequest->headers()->all(),
            'body' => $pendingRequest->body()?->all(),
        ];

        $segmentName = $pendingRequest->getMethod()->name . ' ' . $pendingRequest->getUrl();
        $segment->setName($segmentName);

        if ($segment instanceof HttpSegment) {
            $segment->setUrl($pendingRequest->getUrl());
            $segment->setMethod($pendingRequest->getMethod()->name);
        }

        $segment->addMetadata('request', $metaData);

        $segment->addAnnotation('Host', $pendingRequest->getUri()->getHost());

        return $metaData;
    }

}
