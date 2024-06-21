<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray;

use Pkerrigan\Xray\Segment;
use Saloon\Http\Response;

interface XrayDataProviderInterface
{

    public function addXrayMetadata(Segment $segment, Response $response): void;

}
