<?php

namespace App\Test\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListingControllerTest extends WebTestCase
{
    public function testGetAll()
    {
        $client = static::createClient();

        $client->request('GET', '/listing/all');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Assert the structure and values of the response data
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        $listing = $responseData[0];

        $this->assertArrayHasKey('id', $listing);
        $this->assertArrayHasKey('city', $listing);
        $this->assertArrayHasKey('country', $listing);
        $this->assertArrayHasKey('description', $listing);
        $this->assertArrayHasKey('listingSubCategory', $listing);
        $this->assertArrayHasKey('fkListingStatus', $listing);
        $this->assertArrayHasKey('fkListingType', $listing);
        $this->assertArrayHasKey('name', $listing);
        $this->assertArrayHasKey('createdAt', $listing);
        $this->assertArrayHasKey('postCode', $listing);
        $this->assertArrayHasKey('title', $listing);
    }

    public function testGetByStatus()
    {
        $client = static::createClient();
        $client->request('GET', '/listing/byStatus/1');

        // Verify that the response status code is equal to HTTP 200 OK
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Assert the structure and values of the response data
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);

        $listing = $responseData[0];

        // Assert the presence of specific keys in the response data
        $this->assertArrayHasKey('id', $listing);
        $this->assertArrayHasKey('city', $listing);
        $this->assertArrayHasKey('country', $listing);
        $this->assertArrayHasKey('description', $listing);
        $this->assertArrayHasKey('fkListingStatus', $listing);
        $this->assertArrayHasKey('listingCategory', $listing);
        $this->assertArrayHasKey('listingSubCategory', $listing);
        $this->assertArrayHasKey('fkListingType', $listing);
        $this->assertArrayHasKey('name', $listing);
        $this->assertArrayHasKey('createdAt', $listing);
        $this->assertArrayHasKey('photo', $listing);
        $this->assertArrayHasKey('postCode', $listing);
        $this->assertArrayHasKey('title', $listing);
    }
}