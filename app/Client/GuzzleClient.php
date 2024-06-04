<?php

namespace App\Client;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Laminas\Feed\Reader\Http\ClientInterface as FeedReaderHttpClientInterface;
use Laminas\Feed\Reader\Http\Psr7ResponseDecorator;

class GuzzleClient implements FeedReaderHttpClientInterface
{
    /**
     * @var GuzzleClientInterface
     */
    private $client;

    /**
     * @param GuzzleClientInterface|null $client
     */
    public function __construct(GuzzleClientInterface $client = null)
    {
        $this->client = $client ?: app(\GuzzleHttp\Client::class);
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri)
    {
        return new Psr7ResponseDecorator(
            $this->client->request('GET', $uri)
        );
    }
}
