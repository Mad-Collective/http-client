<?php

namespace features\Cmp\Http;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Cmp\Http\Client\MultiClient;
use Cmp\Http\ClientBuilder;
use Cmp\Http\Message\Request;
use Cmp\Http\Message\Response;
use Cmp\Http\Provider\HttpClientServiceProvider;
use Cmp\Http\Sender\GuzzleSender;
use GuzzleHttp\Client;
use Pimple\Container;
use Psr\Log\NullLogger;
use Webmozart\Assert\Assert;

/**
 * Class ClientContext
 */
class ClientContext implements Context
{
    /**
     * @var MultiClient
     */
    private $client;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    private $body;

    /**
     * FeatureContext constructor.
     */
    public function __construct()
    {
        $container = new Container();
        $provider  = new HttpClientServiceProvider(ClientBuilder::class, MultiClient::class);
        $container->register($provider, [
            'http_client.yaml'    => realpath(__DIR__.'/../../../config-sample/requests.yml'),
            'http_client.logger'  => new NullLogger(),
            'http_client.sender'  => new GuzzleSender(new Client()),
        ]);

        $this->client  = $container[MultiClient::class];
    }

    /**
     * @Given I create the request for :service :request
     */
    public function iCreateTheRequestFor($service, $request)
    {
        $this->request = $this->client->request($service, $request);
    }

    /**
     * @Given I create the request for :service :request with:
     */
    public function iCreateTheRequestForWith($service, $request, TableNode $table)
    {
        $this->request = $this->client->request($service, $request, $table->getHash()[0]);
    }

    /**
     * @Given I configure the request with a json body of:
     */
    public function iConfigureTheRequestWithAJsonBodyOf(TableNode $table)
    {
        $this->request = $this->request->withPost($table->getHash()[0]);
    }

    /**
     * @When I execute the request
     */
    public function iExecuteTheRequest()
    {
        $this->response = $this->client->send($this->request);
    }

    /**
     * @When Parse the body as json
     */
    public function parseTheBodyAsJson()
    {
        $this->body = $this->response->jsonAsArray();
    }

    /**
     * @Then I should have a status code of :code
     */
    public function iShouldHaveAStatusCodeOf($code)
    {
        Assert::eq($this->response->getStatusCode(), $code, "Status code has to be $code");
    }

    /**
     * @Then The contents should be:
     */
    public function theContentsShouldBe(TableNode $table)
    {
        if (count($table->getHash()) > 1) {
            $body = $table->getHash();
        } else {
            $body = $table->getHash()[0];
        }

        Assert::eq($this->body, $body, "The response does not match the mocked requests");
    }
}
