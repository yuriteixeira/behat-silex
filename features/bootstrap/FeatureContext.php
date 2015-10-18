<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpKernel\Client;
use \PHPUnit_Framework_Assert as Assert;

/**
 * Features context.
 */
class FeatureContext implements SnippetAcceptingContext
{
    /**
     * @var Api\Application
     */
    protected $app;

    /**
     * @var \Symfony\Component\BrowserKit\Client
     */
    protected $client;

    /**
     * @BeforeScenario
     */
    public function setup($event)
    {
        $app = new \Api\Application();
        $app['debug'] = true;
        unset($app['exception_handler']);

        $this->app = $app;
        $this->client = new Client($this->app);
    }

    /**
     * @When /^call "([^"]*)" "([^"]*)" with parameters:$/
     */
    public function callWithParameters($method, $endpoint, PyStringNode $postParametersStringNode)
    {
        $postParameters = json_decode($postParametersStringNode->getRaw(), true);
        $this->client->request($method, $endpoint, $postParameters);
    }

    /**
     * @Then /^response status is "([^"]*)"$/
     */
    public function responseStatusIs($statusCode)
    {
        Assert::assertEquals($statusCode, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @Given /^collection "([^"]*)" having the following data:$/
     */
    public function collectionHavingTheFollowingData($collectionName, PyStringNode $dataStringNode)
    {
        $data = json_decode($dataStringNode->getRaw(), true);

        foreach ($data as $document) {
            $this->app->storage[$collectionName][] = $document;
        }
    }

    /**
     * @When /^call "([^"]*)" "([^"]*)" with resource id "([^"]*)"$/
     */
    public function callWithResourceId($method, $endpoint, $resourceId)
    {
        $this->client->request($method, "{$endpoint}/{$resourceId}");
    }

    /**
     * @Then /^response status should be "([^"]*)"$/
     */
    public function responseStatusShouldBe($statusCode)
    {
        return $this->responseStatusIs($statusCode);
    }

    /**
     * @Given /^json response should be:$/
     */
    public function jsonResponseShouldBe(PyStringNode $expectedResponseStringNode)
    {
        $clientResponse = json_decode($this->client->getResponse()->getContent(), true);
        $expectedResponse = json_decode($expectedResponseStringNode->getRaw(), true);
        Assert::assertEquals($expectedResponse, $clientResponse);
    }

    /**
     * @Given /^response content is blank$/
     */
    public function responseContentIsBlank()
    {
        Assert::assertEmpty($this->client->getResponse()->getContent());
    }

    /**
     * @When call ":method" ":endpoint"
     */
    public function callEndpoint($method, $endpoint)
    {
        $this->client->request($method, "{$endpoint}");
    }

    /**
     * @Then response content is ":content"
     */
    public function responseContentIs($content)
    {
        Assert::assertEquals($content, $this->client->getResponse()->getContent());
    }
}
