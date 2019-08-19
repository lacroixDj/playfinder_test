<?php

namespace App\DataFixtures;

use App\Entity\Sport;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * F1SportFixtures Class
 * 
 * Seed the Sports table in the database with pre-existing data.
 * 
 * @author Jesus Farias Lacroix <jesus.farias@gmail.com>
 * 
*/
class F1SportFixtures extends Fixture
{

    /**
     * DEFAULT_SPORTS Pre-defined sports 
    */    
    const DEFAULT_SPORTS = [
        
        [ "name" => "Golf" ],
        
        [ "name" => "Rugby" ],
        
        [ "name" => "Tennis" ],
        
        [ "name" => "Cricket" ],
        
        [ "name" => "Football" ],
        
        [ "name" => "Badminton" ]
    
    ];

    
    /**
     * Load data fixture and persist it to the database
     * 
     * @param ObjectManager $request manager
     * 
    */
    public function load(ObjectManager $manager)
    {
        
        foreach (SELF::DEFAULT_SPORTS as $sport) {

            // Creating Sport instance
            $sportObj = new Sport();
            
            // Setting attributes
            $sportObj->setName($sport["name"]);
            
            // Persist Sport object to the DB
            $manager->persist($sportObj);
             
        }

        $manager->flush();
    }
}
