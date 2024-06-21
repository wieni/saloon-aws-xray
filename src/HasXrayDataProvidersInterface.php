<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray;

use Saloon\Http\Response;

interface HasXrayDataProvidersInterface
{

    /** @return XrayDataProviderInterface[] */
    public function getXrayDataProviders(Response $response): array;

}
