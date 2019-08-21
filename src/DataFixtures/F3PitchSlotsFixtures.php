<?php

namespace App\DataFixtures;

use Faker;
use DateTime;
use DateInterval;
use DatePeriod;
use App\Entity\Pitch;
use App\Entity\Sport;
use App\Entity\Slot;
use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;


/**
 * F3PitchSlotsFixtures Class
 * 
 * Seed the Pitch table in the database with a combination of 
 * random generated data using fzaninotto/faker library and
 * pre-loaded database data. 
 * 
 * Also seed the Slot table records for each created pitch.
 * 
 * @author Jesus Farias Lacroix <jesus.farias@gmail.com>
 * 
*/
class F3PitchSlotsFixtures extends Fixture
{
    
    /** MAX_PITCHES Max number pitches to create */    
    const MAX_PITCHES = 33; 

    /** SLOT_MAX_WEEKS Max number of weeks to create time slots */    
    const SLOT_MAX_WEEKS = "7 weeks"; 

    /** SLOT_START_HOUR Daily Start hour to begin time slot generations */    
    const SLOT_START_HOUR = "06:00:00"; 
    
    /** SLOT_START_HOUR Daily Start hour to begin time slot generations */    
    const SLOT_END_HOUR = "23:00:00"; 

    /** SLOT_DURATION slot time duration */    
    const SLOT_DURATION = "1 hour"; 

    /** DEFAULTS_LOCALES Default locales for data generations (addresses, names, etc) */    
    const DEFAULTS_LOCALES = ["en_GB", "en_US"]; 

    /** DEFAULTS_MIDDLE_TITLES Default titles for generating pitchs names */    
    const DEFAULTS_MIDDLE_TITLES = [
        'Sports',
        'Training',
        'Fitness', 
        'Leisure',
        'Play',
    ]; 

    /** DEFAULTS_MIDDLE_TITLES Default titles for generating pitchs names */    
    const DEFAULTS_TITLES_SUFFIXES = [
        'Center',
        'Court',
        'Gym', 
        'Club',
    ]; 
    

    /**
     * Load data fixture and persist it to the database
     * 
     * @param ObjectManager $request manager
     * 
    */
    public function load(ObjectManager $manager)
    {
     
        // Init repositories classes
        $sportRepository = $manager->getRepository(Sport::class); 

        $currencyRepository = $manager->getRepository(Currency::class);

        $sports = $sportRepository->findAll();

        $pound = $currencyRepository->findOneBy(["code"=>"GBP"]);
        
        $euro = $currencyRepository->findOneBy(["code"=>"EUR"]);

        $dollar = $currencyRepository->findOneBy(["code"=>"USD"]);
        
        $arr_europe_currencies = [$pound, $euro];

        // Generate datetimes timetable
        $arrayDateTimeSlots = $this->createDateTimeSlots(); 


        for ($i = 0; $i < SELF::MAX_PITCHES; $i++) {

            $locale = SELF::DEFAULTS_LOCALES[array_rand(SELF::DEFAULTS_LOCALES)];

            // If locale is 
            $currency = ($locale=="en_US")? $dollar : $arr_europe_currencies[array_rand($arr_europe_currencies)];

            // Creating faker object with random locale 
            $faker = Faker\Factory::create($locale);

            // Generating fake data:
            $pitchCity = $faker->city;

            $pitchMiddleTitle = $faker->randomElement(SELF::DEFAULTS_MIDDLE_TITLES);
            
            $pitchTitleSufix = $faker->randomElement(SELF::DEFAULTS_TITLES_SUFFIXES);
            
            $pitchName = "$pitchCity  $pitchMiddleTitle $pitchTitleSufix";            
            
            $pitchSport = $faker->randomElement($sports);
            
            // Creating Pitch instance
            $pitch = new Pitch();
            
            // Setting generated name
            $pitch->setName($pitchName);
            
            // Setting sport relation 
            $pitch->setSport($pitchSport);
           
            // Persist Pitch to the DB
            $manager->persist($pitch);
            
            //Call Slots creation
            $this->createSlots($pitch, $currency, $faker, $arrayDateTimeSlots, $manager);
        }

        $manager->flush();
    }


    /**
     * Create Slots sample data and persist it to the database
     * 
     * @param ObjectManager $request manager
     * 
    */
    protected function createSlots($pitch, $currency, $faker, $arrayDateTimeSlots, $manager)
    {
        
        foreach ($arrayDateTimeSlots as $timeSlot) {
              
            // Creating Slot instance
            $slot = new Slot();
            
            // Setting start time
            $slot->setStarts(($timeSlot["starts"]));
            
            // Setting end time
            $slot->setEnds(($timeSlot["ends"]));
            
            // Setting the price
            $slot->setPrice(($faker->randomFloat(2, $min = 10, $max = 100)));
            
            // Setting the currency
            $slot->setCurrency($currency);

            // Setting the availability
            $arrAvailability = [TRUE, FALSE];
            $slot->setAvailable(($arrAvailability[array_rand($arrAvailability)]));

            // Setting the Pitch relation
            $slot->setPitch($pitch);

            // Persist Slot to the DB
            $manager->persist($slot);
        
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
    protected function createDateTimeSlots(): Array 
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
                "starts" => $arrayDateTimeTable[$i],
                "ends" => $arrayDateTimeTable[$j]
            ];	
        }

        return $arrayDateTimeSlots;
    }
}
