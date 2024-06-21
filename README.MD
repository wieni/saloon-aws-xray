# AWS X-Ray integration for Saloon

This package provides a Saloon Sender that sends traces to AWS X-Ray.  
It creates a separate trace for each request and sends the trace to AWS X-Ray when the request is finished.

## Installation

```bash
composer require wieni/saloon-aws-xray
```

## Configuration

### Laravel

When using Laravel, this package plays nicely with [napp/xray-laravel](https://github.com/Napp/xray-laravel).  
Please read the documentation of that package to configure AWS X-Ray for Laravel.

You can then create a ServiceProvider to configure Saloon to use the AWS X-Ray sender.

```php
// app/Providers/SaloonXrayServiceProvider.php
<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Napp\Xray\Submission\DaemonSegmentSubmitter;
use Napp\Xray\Xray;
use Saloon\Config as SaloonConfig;
use Saloon\HttpSender\HttpSender;
use Wieni\SaloonAwsXray\XrayHttpSender;

final class SaloonXrayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Configure the XrayHttpSender
        $this->app->bind(
            XrayHttpSender::class,
            function (): XrayHttpSender {
                $submitterClass = config('xray.submitter', DaemonSegmentSubmitter::class);
                $senderClass = config('saloon.default_sender', HttpSender::class);

                return new XrayHttpSender(
                    new $senderClass(),
                    $this->app[Xray::class]->tracer(),
                    new $submitterClass(),
                );
            },
        );

        // Configure Saloon to use the XrayHttpSender
        SaloonConfig::setSenderResolver(fn() => $this->app[XrayHttpSender::class]);
    }
}
```

And register the ServiceProvider in your `config/app.php`:

```diff
// config/app.php
'providers' => [
    // ...
+    App\Providers\SaloonXrayServiceProvider::class,
    // ...
],
```