<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * F2CurrencyFixtures Class
 * 
 * Seed the Currency table in the database with pre-existing data.
 * 
 * @author Jesus Farias Lacroix <jesus.farias@gmail.com>
 * 
*/
class F2CurrencyFixtures extends Fixture
{

    /**
     * DEFAULT_CURRENCIES Pre-defined currencies 
    */    
    const DEFAULT_CURRENCIES = [
        
        [
            "code" => "GBP",
            
            "name" => "Pound Sterling",
            
            "symbol" => "£"
        ],
        
        [
            "code" => "EUR",
            
            "name" => "Euro",
            
            "symbol" => "€"
        ],

        [
            "code" => "USD",
            
            "name" => "US Dollar",
            
            "symbol" => "$"
        ],
    ];

    
    /**
     * Load data fixture and persist it to the database
     * 
     * @param ObjectManager $request manager
     * 
    */
    public function load(ObjectManager $manager)
    {
        
         foreach (SELF::DEFAULT_CURRENCIES as $currency) {

            // Creating Pitch instance
            $currencyObj = new Currency();
            
            // Setting attributes
            $currencyObj->setCode($currency["code"]);

            $currencyObj->setName($currency["name"]);
            
            $currencyObj->setSymbol($currency["symbol"]);
            
            // Persist Pitch object to the DB
            $manager->persist($currencyObj);
             
        }
        
        $manager->flush();
    }
}
