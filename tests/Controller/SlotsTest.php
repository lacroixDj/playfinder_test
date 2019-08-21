<?php

namespace App\Tests\Controller;

use Faker;
use DateTime;
use DateInterval;
use DatePeriod;
use App\Entity\Slot;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SlotsTest extends WebTestCase
{
    
    /** DEFAULT_TYPE default entity type */    
    const DEFAULT_TYPE = "slots";

    /** DEFAULT_DATE_FORMAT default datetime format used */
    const DEFAULT_DATE_FORMAT = "Y-m-d\TH:i:sP";

    /** DEFAULT_FLAGS default availabilty flags values */
    const DEFAULT_FLAGS = [TRUE, FALSE]; 
    
    /** DEFAULT_CURRENCIES default currencies codes */
    const DEFAULT_CURRENCIES = ["GBP", "EUR", "USD"]; 

    /** SLOT_MAX_WEEKS Max number of weeks to create time slots */    
    const SLOT_MAX_WEEKS = "7 weeks"; 

    /** SLOT_START_HOUR Daily Start hour to begin time slot generations */    
    const SLOT_START_HOUR = "06:00:00"; 
    
    /** SLOT_START_HOUR Daily Start hour to begin time slot generations */    
    const SLOT_END_HOUR = "23:00:00"; 

    /** SLOT_DURATION slot time duration */    
    const SLOT_DURATION = "1 hour"; 


    /** 
     * Test  GET "/pitches/{$id}/slots" endpoint 
    */
    public function testGetStlotsStatusResponse()
    {
        $client = static::createClient();

        //Data fixtures has loaded 33 sample pitches at the beginning 
        $randId = rand(1,11);

        $client->request('GET', "/pitches/$randId/slots");

        $response = $client->getResponse();

        $internalResponse = $client->getInternalResponse();
        
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertResponseHasHeader("Content-Type");

        $this->assertResponseHeaderSame("Content-Type", "application/json"); 
    }


    /** 
     * Test  GET "/pitches/{$id}/slots" endpoint, fetch data and count items
    */    
    public function testGetSlotsData()
    {
        $client = static::createClient();

        $randId = rand(1,11);

        $client->request('GET', "/pitches/$randId/slots");

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $slotsData = json_decode($response->getContent(), true);

        $this->assertIsIterable($slotsData);

        $this->assertArrayHasKey("meta", $slotsData);
        
        $this->assertIsIterable($slotsData["meta"]);
        
        $this->assertArrayHasKey("data", $slotsData);
        
        $this->assertIsIterable($slotsData["data"]);

        $this->assertArrayHasKey("total_items", $slotsData['meta']);

        // Verify total_items number is equal to the numbers of elements in the data;
        $this->assertEquals($slotsData['meta']["total_items"], count($slotsData['data']));

        $this->assertCount($slotsData['meta']["total_items"], $slotsData['data']);
    }
     
    
    /** 
     * Test  GET "/pitches/{$id}/slots" endpoint, check slots data items structure and content
    */    
    public function testGetSlotsDataItems()
    {
        $client = static::createClient();

        $randId = rand(1,11);

        $client->request('GET', "/pitches/$randId/slots");

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $slotsData = json_decode($response->getContent(), true);

        $this->assertIsIterable($slotsData);

        $this->assertArrayHasKey("data", $slotsData);

        $this->assertIsIterable($slotsData["data"]);

        $this->assertGreaterThan(0, count($slotsData['data']));

        foreach ($slotsData['data'] as $slotItem) {

            $this->assertIsIterable($slotItem);

            $this->assertArrayHasKey("type", $slotItem);

            $this->assertIsString($slotItem["type"]);

            $this->assertEquals(SELF::DEFAULT_TYPE, $slotItem["type"]);

            $this->assertArrayHasKey("id", $slotItem);

            $this->assertIsInt($slotItem["id"]);
            
            $this->assertArrayHasKey("attributes", $slotItem);

            $this->assertIsIterable($slotItem["attributes"]);

            // Testing starts datetime valid format     
                $this->assertArrayHasKey("starts", $slotItem["attributes"]);
            
                $this->assertIsString($slotItem["attributes"]["starts"]);

                $starts = \DateTime::createFromFormat(SELF::DEFAULT_DATE_FORMAT, $slotItem["attributes"]["starts"]);
                
                $this->assertTrue(($starts && $starts->format(SELF::DEFAULT_DATE_FORMAT) === $slotItem["attributes"]["starts"]));

            // Testing ends datetime valid format
                $this->assertArrayHasKey("ends", $slotItem["attributes"]);
            
                $this->assertIsString($slotItem["attributes"]["ends"]);

                $ends = \DateTime::createFromFormat(SELF::DEFAULT_DATE_FORMAT, $slotItem["attributes"]["ends"]);
                
                $this->assertTrue(($ends && $ends->format(SELF::DEFAULT_DATE_FORMAT) === $slotItem["attributes"]["ends"]));

            //Testing starts datetime < ends datetime
                $this->assertTrue(($starts < $ends));

            // Testing price
                $this->assertArrayHasKey("price", $slotItem["attributes"]);
            
                $this->assertIsNumeric($slotItem["attributes"]["price"]);

                $this->assertGreaterThan(0, $slotItem["attributes"]["price"]);
            
            // Testing currency
                $this->assertArrayHasKey("currency", $slotItem["attributes"]);

                $this->assertIsString($slotItem["attributes"]["currency"]);

                $this->assertContains($slotItem["attributes"]["currency"], SELF::DEFAULT_CURRENCIES);

            // Testing available
                $this->assertArrayHasKey("available", $slotItem["attributes"]);

                $this->assertIsBool($slotItem["attributes"]["available"]);
        }

    } 

    
    /** 
     * Test  GET "/pitches/{$id}/slots" endpoint  error message 
    */    
    public function testGetPitchSlotsError()
    {
        $client = static::createClient();

        // Lets generate some random string        
        $randId = str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ");

        $client->request('GET', "/pitches/$randId/slots");

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


    /** 
     * Test Slots bulk inserts  
     * 
     * POST "/pitches/{$id}/slots" endpoint
    */    
    public function testSlotsBulkInserts()
    {
        $client = static::createClient();

        // Get all ptiches first
        $client->request('GET', '/pitches');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $pitchData = json_decode($response->getContent(), true);

        $this->assertIsIterable($pitchData);

        $this->assertArrayHasKey("data", $pitchData);

        $this->assertIsIterable($pitchData["data"]);

        $this->assertGreaterThan(0, count($pitchData['data']));

        $faker = Faker\Factory::create("en_GB");

        // Truncate Slots table 
        $this->truncateEntityTable(Slot::class);

        // Generate datetimes timetable
        $arrayDateTimeSlots = $this->createDateTimeSlots();

        // Loop all pitches
        foreach ($pitchData['data'] as $pitchItem) {

            $this->assertIsIterable($pitchItem);
        
            $this->assertArrayHasKey("id", $pitchItem);

            $this->assertIsInt($pitchItem["id"]);

            // Get the pitch id
            $pitchId = $pitchItem["id"];

            // Prepare POST Url endpoint
            $postURL = "/pitches/$pitchId/slots";
            
            //echo  $postURL."\n";

            $slotsDataCollection = ["data" => []]; 
           

            foreach ($arrayDateTimeSlots as $timeSlot) {
                
                $slotItem = [];
    
                $slotItem["id"] = NULL;
    
                $slotItem["type"] = SELF::DEFAULT_TYPE;
                
                $slotItem["attributes"] = [
    
                    "starts" => $timeSlot["starts"],
    
                    "ends" => $timeSlot["ends"],
                    
                    "price" => ($faker->randomFloat(2, $min = 10, $max = 100)),
    
                    "currency" => (SELF::DEFAULT_CURRENCIES [ (array_rand(SELF::DEFAULT_CURRENCIES))]),
    
                    "available" => (SELF::DEFAULT_FLAGS [(array_rand(SELF::DEFAULT_FLAGS))]),
    
                ];
    
                $slotsDataCollection ["data"][] =  $slotItem;
                
            }

            // POST a raw JSON string in the body
            $client->request('POST', $postURL, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($slotsDataCollection));

            $responseSlots = $client->getResponse();

            // Check 201 created status code
            $this->assertEquals(201, $responseSlots->getStatusCode());

            $responseData = json_decode($responseSlots->getContent(), true);

            $this->assertIsIterable($responseData);

            $this->assertArrayHasKey("meta", $responseData);
        
            $this->assertIsIterable($responseData["meta"]);
        
            $this->assertArrayHasKey("total_items", $responseData['meta']);

            // Verify new created total items is equal to the numbers of elements in the generated data;
            $this->assertEquals($responseData['meta']["total_items"], count($slotsDataCollection ["data"]));

            $this->assertArrayHasKey("message", $responseData['meta']);

            $this->assertIsString($responseData['meta']["message"]);

            $this->assertEquals($responseData["meta"]["message"], "SUCCESS! - Slots were created or updated");
 
        }
    }

    
    /** 
     * Truncates a database entity table 
     * 
     * USE CAREFULLY!
    */
    private function truncateEntityTable($entity)
    {
        
        SELF::bootKernel();
        
        $entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        
        $connection = $entityManager->getConnection();
       
        $databasePlatform = $connection->getDatabasePlatform();
        
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        
        }
        
        $query = $databasePlatform->getTruncateTableSQL(
                
            $entityManager->getClassMetadata($entity)->getTableName()
            
        );
            
        $connection->executeUpdate($query);
        
        if ($databasePlatform->supportsForeignKeyConstraints()) {
            
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        
        }
    }

    /**
     * This is a tool function for creating an hourly timetable for date times strings 
     * from TODAY at SELF::SLOT_START_HOUR >= Current DateTime
     * until  TODAY + SLOT_MAX_WEEKS at SELF::SLOT_END_HOUR
     * 
     * This method prepares the arrayDateTimeSlots according to  "playfinder.test way" 
     * desired Slot model
     * 
     *  Example:
     *  
     *  [0] => Array 
     *      (
     *          [starts] => 2019-08-19 15:00:00
     *          [ends] => 2019-08-19 16:00:00
     *      )
     *  
     *  [1] => Array
     *      (
     *          [starts] => 2019-08-19 16:00:00
     *          [ends] => 2019-08-19 17:00:00
     *      )
     *  
     *   ...
     * 
     * @return Array  $arrayDateTimeSlots
     * 
    */
    private function createDateTimeSlots(): Array 
    {
        
        // Preparing the start time
        $dateTimeStart = new DateTime(date(sprintf("Y-m-d %s", SELF::SLOT_START_HOUR)));
                    
        // We need an aux copy of start time to keep the original value after date_add()
		$dateTimeStartAux = new DateTime(date(sprintf("Y-m-d %s", SELF::SLOT_START_HOUR))); 	
    
        // Creating the end time interval
		$dateTimeIntervalEnd = DateInterval::createFromDateString(SELF::SLOT_MAX_WEEKS);
        
        // Calculating the final date 
        $dateTimeEndAux = date_add($dateTimeStartAux, $dateTimeIntervalEnd);
        
        // Preparing the final dateTime including SELF::SLOT_END_HOUR
		$dateTimeEnd = new DateTime(date(sprintf(($dateTimeEndAux->format('Y-m-d'))." %s", SELF::SLOT_END_HOUR)));
        
        // Creating a time interval with SLOT_DURATION 
        $dateTimeIntervalHour = DateInterval::createFromDateString(SELF::SLOT_DURATION);
        
        // Finally creating the time period starting at $dateTimeStart, ending at  $dateTimeEnd, SLOT_DURATION steps 
        $slotsDateTimePeriod = new DatePeriod($dateTimeStart, $dateTimeIntervalHour, $dateTimeEnd);

        // Preparing the timetable array;
		$arrayDateTimeTable = [];
        
        // Preparing the return array;
		$arrayDateTimeSlots = [];
        
        // Getting current time (we will use it later)
        $currentDateTime = new DateTime();

        // Looping the time period by SLOT_DURATION steps 
        // We need to generate all the time table values
        foreach ($slotsDateTimePeriod as $dt) {
            
            // Getting the time in string format
            $strIntervalTime = $dt->format("H:i:s");

            // Avoid time slot creation in past time (today past hours)
            // Also it makes sure time slots are between the allowed time frame [SLOT_START_HOUR..SLOT_END_HOUR]
            if($dt>=$currentDateTime && $strIntervalTime >= SELF::SLOT_START_HOUR && $strIntervalTime <= SELF::SLOT_END_HOUR ) {
                
                // Fill the return array
                $arrayDateTimeTable [] = $dt;
                // $arrayDateTimeTable [] = $dt->format("Y-m-d H:i:s");

            }
        }
 
        // Finaly we need to prepare the returning  array according to required output 
        for($i=0, $j=1; $j<count($arrayDateTimeTable); $i++, $j++){
    
            if($arrayDateTimeTable[$i]->format("H:i:s")=="23:00:00") continue;

            $arrayDateTimeSlots [] = [
                "starts" => $arrayDateTimeTable[$i]->format(SELF::DEFAULT_DATE_FORMAT),
                "ends" => $arrayDateTimeTable[$j]->format(SELF::DEFAULT_DATE_FORMAT)
            ];	
        }

        return $arrayDateTimeSlots;
    }
    
}