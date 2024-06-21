<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\Test;

use Pkerrigan\Xray\Segment;
use Saloon\Http\Response;
use Wieni\SaloonAwsXray\XrayDataProviderInterface;

class TestMetadataProvider implements XrayDataProviderInterface
{

    public function addXrayMetadata(Segment $segment, Response $response): void
    {
        $segment->addAnnotation('test-annotation', 'test-annotation-value');
    }

}