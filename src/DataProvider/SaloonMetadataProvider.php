<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\DataProvider;

use Pkerrigan\Xray\Segment;
use Saloon\Http\PendingRequest;

/**
 * This data provider is always being applied.
 * 
 * Adds metadata to the Xray segment with information about the Saloon request.
 * 
 * @see \Wieni\SaloonAwsXray\XrayHttpSender::createTrace()
 */
class SaloonMetadataProvider
{

    public function addXrayMetadata(Segment $segment, PendingRequest $pendingRequest): void
    {
        $requestClass = get_class($pendingRequest->getRequest());

        $segment->addAnnotation('framework', 'Saloon');
        $segment->addAnnotation('SaloonRequest', $requestClass);
    }

}