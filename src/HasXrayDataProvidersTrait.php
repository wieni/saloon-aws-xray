<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray;

use Saloon\Http\Response;

trait HasXrayDataProvidersTrait
{

    public function getXrayDataProviders(Response $response): array
    {
        return [
            new DataProvider\ResponseBodyMetadataProvider(),
        ];
    }

}
