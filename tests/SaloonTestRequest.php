<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\Test;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Wieni\SaloonAwsXray\HasXrayDataProvidersInterface;
use Wieni\SaloonAwsXray\HasXrayDataProvidersTrait;

class SaloonTestRequest extends Request implements HasBody, HasXrayDataProvidersInterface
{

    use HasJsonBody;
    use HasXrayDataProvidersTrait {
        HasXrayDataProvidersTrait::getXrayDataProviders as protected getXrayDataProvidersTrait;
    }

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return 'https://www.foobar.com/foobar';
    }

    public function defaultQuery(): array
    {
        return [
            'X-Test-Query' => 'test-query',
        ];
    }

    public function defaultHeaders(): array
    {
        return [
            'X-Test-Header' => 'test-header',
        ];
    }

    public function defaultBody(): array
    {
        return [
            'data' => 'test-body',
        ];
    }

    public function getXrayDataProviders(Response $response): array
    {
        return [
            ...$this->getXrayDataProvidersTrait($response),
            new TestMetadataProvider(),
        ];
    }


}