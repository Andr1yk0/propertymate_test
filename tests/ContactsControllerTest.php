<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactsControllerTest extends WebTestCase
{
    private $url = '/api/contacts';
    private $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function testGetAllContactsWithCustomFields()
    {
        $this->client->request('GET', $this->url);
        $content = $this->getResponseContent();

        $this->assertResponseIsSuccessful();
        $this->assertEquals('address 1', $content->data[0]->address);
        $this->assertCount(2, $content->data);
    }

    public function testFilterByCustomTextField()
    {
        $this->client->request('GET', "$this->url?filters[custom_fields][address]=ess 3");
        $content = $this->getResponseContent();

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $content->data);
        $this->assertEquals('address 3', $content->data[0]->address);
    }

    public function testFilterByCreateDate()
    {
        $this->client->request('GET', "$this->url?filters[create_at_from]=29/12/2018");
        $content = $this->getResponseContent();

        $this->assertResponseIsSuccessful();
        $this->assertEquals(2, $content->total);
        $this->assertEquals('address 1', $content->data[0]->address);
    }

    public function testPagination()
    {
        $this->client->request('GET', "$this->url?page=2");
        $content = $this->getResponseContent();

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $content->data);
        $this->assertEquals(2, $content->currentPage);
    }

    public function testWrongCustomFieldNameInFilter()
    {
        $this->client->request('GET', "$this->url?filters[custom_fields][addres]=ess 3");
        $content = $this->getResponseContent();

        $this->assertResponseStatusCodeSame(400);
        $this->assertEquals("Custom field with name addres doesn't exist", $content->error);
    }

    public function testOrderByCustomTextField()
    {
        $this->client->request('GET', "$this->url?order=-address");
        $content = $this->getResponseContent();

        $this->assertEquals('address 5', $content->data[0]->address);
    }

    private function getResponseContent()
    {
        return json_decode($this->client->getResponse()->getContent());
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->client = null;
    }
}
