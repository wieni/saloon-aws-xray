<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray;

use GuzzleHttp\Promise\PromiseInterface;
use Pkerrigan\Xray\HttpSegment;
use Pkerrigan\Xray\Submission\SegmentSubmitter;
use Pkerrigan\Xray\Trace;
use Saloon\Contracts\Sender;
use Saloon\Data\FactoryCollection;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

readonly class XrayHttpSender implements Sender
{

    public function __construct(
        private Sender $saloonSender,
        private Trace $parentTrace,
        private SegmentSubmitter $segmentSubmitter,
    )
    {
    }

    public function getFactoryCollection(): FactoryCollection
    {
        return $this->saloonSender->getFactoryCollection();
    }

    public function send(PendingRequest $pendingRequest): Response
    {
        $trace = $this->createTrace($pendingRequest);

        try {
            $response = $this->saloonSender->send($pendingRequest);
            $this->finalise($trace, $response);
            return $response;
        } catch (\Throwable $e) {
            $trace?->getCurrentSegment()
                ->setFault(true)
                ->addMetadata('error', $e->getMessage())
                ->end();
            throw $e;
        } finally {
            $trace?->end()->submit($this->segmentSubmitter);
            unset($trace);
            gc_collect_cycles();
        }
    }

    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface
    {
        $trace = $this->createTrace($pendingRequest);

        $promise = $this->saloonSender->sendAsync($pendingRequest);
        $promise->then(
            function (Response $response) use ($trace) {
                $this->finalise($trace, $response);
                $trace?->getCurrentSegment()->end();
                $trace?->end()->submit($this->segmentSubmitter);
                unset($trace);
                gc_collect_cycles();
            },
            function (\Throwable $e) use ($trace) {
                $trace?->getCurrentSegment()
                    ->setFault(true)
                    ->addMetadata('error', $e->getMessage())
                    ->end();
                $trace?->end()->submit($this->segmentSubmitter);
                unset($trace);
                gc_collect_cycles();
            },
        );

        return $promise;
    }

    private function createTrace(PendingRequest $pendingRequest): ?Trace
    {
        if ($pendingRequest->hasMockClient()) {
            return null;
        }

        $segment = (new HttpSegment())->begin();

        // This is always applied to ensure we have some metadata available
        (new DataProvider\RequestMetadataProvider())->addXrayMetadata(
            $segment,
            $pendingRequest
        );
        (new DataProvider\SaloonMetadataProvider())->addXrayMetadata(
            $segment,
            $pendingRequest
        );

        $traceId = $this->parentTrace->getTraceId();
        $parentId = $this->parentTrace->getCurrentSegment()->getId();
        if ($parentId === $this->parentTrace->getId()) {
            $parentId = null;
        }
        $traceName = $this->parentTrace->jsonSerialize()['name'];

        $trace = (new Trace())
            ->setTraceHeader($_SERVER['HTTP_X_AMZN_TRACE_ID'] ?? null)
            ->setSampled($this->parentTrace->isSampled())
            ->setName($traceName);

        $trace->setParentId($parentId);
        if ($traceId) {
            $trace->setTraceId($traceId);
        }

        $trace->begin();
        $trace->addSubsegment($segment);

        return $trace;
    }

    private function finalise(?Trace $trace, Response $response): void
    {
        if ($trace === null) {
            return;
        }

        $segment = $trace->getCurrentSegment();
        $segment->end();

        // This is always applied to ensure we have some metadata available
        (new DataProvider\ResponseMetadataProvider())->addXrayMetadata(
            $segment,
            $response
        );

        // Allow request-specific data providers to add metadata

        $request = $response->getRequest();
        if (!$request instanceof HasXrayDataProvidersInterface) {
            return;
        }

        foreach ($request->getXrayDataProviders($response) as $dataProvider) {
            $dataProvider->addXrayMetadata($segment, $response);
        }
    }

}
