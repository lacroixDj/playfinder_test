<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PitchTest extends WebTestCase
{
    const DEFAULT_TYPE = "pitches";

    const DEFAULT_SPORTS = [
        "Golf", 
        
        "Rugby", 
        
        "Tennis",
       
        "Cricket", 
        
        "Football",
        
        "Badminton"    
    ];

    /** 
     * Test  GET "/pitches" endpoint 
    */
    public function testGetPitchesStatusResponse()
    {
        $client = static::createClient();

        $client->request('GET', '/pitches');

        $response = $client->getResponse();

        $internalResponse = $client->getInternalResponse();
        
        //print_r(get_class_methods($response));
        
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertResponseHasHeader("Content-Type");

        $this->assertResponseHeaderSame("Content-Type", "application/json");
    }

    
    /** 
     * Test  GET "/pitches" endpoint, fetch data and count items
    */    
    public function testGetPitchesData()
    {
        $client = static::createClient();

        $client->request('GET', '/pitches');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $pitchData = json_decode($response->getContent(), true);

        $this->assertIsIterable($pitchData);

        $this->assertArrayHasKey("meta", $pitchData);
        
        $this->assertIsIterable($pitchData["meta"]);
        
        $this->assertArrayHasKey("data", $pitchData);
        
        $this->assertIsIterable($pitchData["data"]);

        $this->assertArrayHasKey("total_items", $pitchData['meta']);

        // Verify total_items number is equal to the numbers of elements in the data;
        $this->assertEquals($pitchData['meta']["total_items"], count($pitchData['data']));

        $this->assertCount($pitchData['meta']["total_items"], $pitchData['data']);
    }
    
    
    /** 
     * Test  GET "/pitches" endpoint, check ptiches data items structure and content
    */    
    public function testGetPitchesDataItems()
    {
        $client = static::createClient();

        $client->request('GET', '/pitches');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $pitchData = json_decode($response->getContent(), true);

        $this->assertIsIterable($pitchData);

        $this->assertArrayHasKey("data", $pitchData);

        $this->assertIsIterable($pitchData["data"]);

        $this->assertGreaterThan(0, count($pitchData['data']));

        foreach ($pitchData['data'] as $pitchItem) {

            $this->assertIsIterable($pitchItem);
            
            $this->assertArrayHasKey("type", $pitchItem);

            $this->assertIsString($pitchItem["type"]);

            $this->assertEquals(SELF::DEFAULT_TYPE, $pitchItem["type"]);
            
            $this->assertArrayHasKey("id", $pitchItem);

            $this->assertIsInt($pitchItem["id"]);

            $this->assertArrayHasKey("attributes", $pitchItem);

            $this->assertIsIterable($pitchItem["attributes"]);

            $this->assertArrayHasKey("name", $pitchItem["attributes"]);

            $this->assertIsString($pitchItem["attributes"]["name"]);
            
            $this->assertArrayHasKey("sport", $pitchItem["attributes"]);

            $this->assertIsString($pitchItem["attributes"]["sport"]);

            $this->assertContains($pitchItem["attributes"]["sport"], SELF::DEFAULT_SPORTS);
        }
    }

    
    /** 
     * Test  GET "/pitches/{id}" endpoint  data and structure 
    */    
    public function testGetPitchDetails()
    {
        $client = static::createClient();

        //Data fixtures has loaded 33 sample pitches at the beginning 

        $randId = rand(1,11);

        $client->request('GET', "/pitches/$randId");

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $pitchData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey("data", $pitchData);

        $pitchItem = $pitchData['data'];

        $this->assertIsIterable($pitchItem);

        $this->assertArrayHasKey("type", $pitchItem);

        $this->assertIsString($pitchItem["type"]);

        $this->assertEquals(SELF::DEFAULT_TYPE, $pitchItem["type"]);  
            
        $this->assertArrayHasKey("id", $pitchItem);

        $this->assertIsInt($pitchItem["id"]);

        $this->assertEquals($randId, $pitchItem["id"]);

        $this->assertArrayHasKey("attributes", $pitchItem);

        $this->assertIsIterable($pitchItem["attributes"]);

        $this->assertArrayHasKey("name", $pitchItem["attributes"]);
            
        $this->assertIsString($pitchItem["attributes"]["name"]);
            
        $this->assertArrayHasKey("sport", $pitchItem["attributes"]);

        $this->assertIsString($pitchItem["attributes"]["sport"]);

        $this->assertContains($pitchItem["attributes"]["sport"], SELF::DEFAULT_SPORTS);
    }

    
    /** 
     * Test  GET "/pitches/{id}" endpoint  error message 
    */    
    public function testGetPitchDetailsError()
    {
        $client = static::createClient();

        // Lets generate some random string        
        $randId = str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");

        $client->request('GET', "/pitches/$randId");

        $response = $client->getResponse();

        // Not found
        $this->assertEquals(404, $response->getStatusCode());

        $errorData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey("error", $errorData);

        $this->assertIsIterable($errorData["error"]);

        $this->assertArrayHasKey("status", $errorData["error"]);

        $this->assertIsInt($errorData["error"]["status"]);

        $this->assertEquals(404, $errorData["error"]["status"]);

        $this->assertArrayHasKey("message", $errorData["error"]);

        $this->assertIsString($errorData["error"]["message"]);

        $this->assertEquals($errorData["error"]["message"], "ERROR! - Pitch id: $randId was not found");
    }
}