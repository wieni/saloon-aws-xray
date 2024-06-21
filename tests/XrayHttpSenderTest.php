<?php

declare(strict_types=1);

namespace Wieni\SaloonAwsXray\Test;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Pkerrigan\Xray\Segment;
use Pkerrigan\Xray\Submission\SegmentSubmitter;
use Pkerrigan\Xray\Trace;
use Saloon\Contracts\Sender;
use PHPUnit\Framework\TestCase;
use Saloon\Http\Connectors\NullConnector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Wieni\SaloonAwsXray\XrayHttpSender;

class XrayHttpSenderTest extends TestCase
{

    public function testItCreatesTrace(): void
    {
        $xraySender = new XrayHttpSender(
            $mockSender = $this->createMock(Sender::class),
            $parentTrace = new Trace(),
            $segmentSubmitter = new StubSegmentSubmitter(),
        );

        $parentTrace->setSampled(true)
            ->setName('parent-trace')
            ->setTraceId('parent-trace-id');

        $request = new PendingRequest(
            new NullConnector(),
            new SaloonTestRequest(),
        );
        $response = new Response(
            new Psr7Response(
                200,
                [
                    'Content-Type' => 'application/json',
                    'X-Test-Header' => 'test-header-value',
                ],
                '{"foo": "bar"}',
            ),
            $request,
            $request->createPsrRequest(),
        );
        
        $mockSender->method('send')->willReturn($response);
        $xraySender->send($request);
        
        $submittedSegments = $segmentSubmitter->submittedSegments;
        $this->assertCount(1, $submittedSegments);
        $submittedSegmentData = json_decode(json_encode($submittedSegments[0]), true);

        $this->assertEquals('parent-trace-id', $submittedSegmentData['trace_id']);
        $this->assertEquals('parent-trace', $submittedSegmentData['name']);
        $this->assertEquals('test-annotation-value', $submittedSegmentData['subsegments'][0]['annotations']['test-annotation']);
        $this->assertEquals('https://www.foobar.com/foobar', $submittedSegmentData['subsegments'][0]['http']['request']['url']);
        $this->assertEquals('test-header-value', $submittedSegmentData['subsegments'][0]['metadata']['response']['headers']['X-Test-Header']);
        $this->assertEquals(SaloonTestRequest::class, $submittedSegmentData['subsegments'][0]['annotations']['SaloonRequest']);
    }

}

class StubSegmentSubmitter implements SegmentSubmitter
{

    public array $submittedSegments = [];

    public function submitSegment(Segment $segment): void
    {
        $this->submittedSegments[] = $segment;
    }
}