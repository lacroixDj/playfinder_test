<?php

namespace App\Controller;

use App\Entity\Pitch;
use App\Entity\Slot;
use App\Repository\SlotRepository;
use App\Repository\PitchRepository;
use App\Repository\CurrencyRepository;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * PitchController Class
 * 
 * This is the Main controller.
 * 
 * It defines the API REST endpoints routes by using Annotations.  
 * It also,  holds the underlaying bussines logic related to those endpoints.
 * 
 * @author Jesus Farias Lacroix <jesus.farias@gmail.com>
 * 
*/
class PitchController extends AbstractController
{
    
    /** Required meta response structure template */
    public static $metaResponseTpl = [
        "meta" => [
            "total_items" => 0
        ]
    ]; 
    
    /** Required data response structure template */
    public static $dataResponseTpl = [
        "data" => []
    ]; 
    
    
    /** Required error response structure template */
    public static $errorResponseTpl = [
        "error" => [
            "status" => "",
            "message" =>  "",
        ]
    ]; 

    
    /**
     * Fetch Pitches from database and return the collection as a Json response
     * 
     * @return JsonResponse  Pitch [], $HTTP_STATUS_CODE
     * 
     * @Route("/pitches", name="get_pitches", methods={"GET"})
    */
    public function getPitches(PitchRepository $pitchRepository)
    {
        
        $statusCode = Response::HTTP_OK;

        $arrayResponse = array_merge(SELF::$metaResponseTpl, SELF::$dataResponseTpl);
        
        $pitches = $pitchRepository->findAll();

        if(!empty($pitches) && !empty(count($pitches))) {

            $arrayResponse["meta"] ["total_items"] = count($pitches);

            foreach($pitches as $pitch) {

                $tmpArray = [
                    
                    "type" => "pitches",
                    
                    "id" => $pitch->getId(),
                    
                    "attributes" => [
                        
                        "name" => $pitch->getName(),
                        
                        "sport" => $pitch->getSport()->getName(),
                    ]
                ];
              
                $arrayResponse["data"] [] = $tmpArray;
            }
        
        } else {

            $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse = $this->prepareErrorResponse("No Pitches were found", $statusCode);

        }

        return new JsonResponse($arrayResponse, $statusCode);
    }


    /**
     * Get a Pitch from database with the given {$id} and return it as a Json response
     * 
     * @param int $id
     * 
     * @return JsonResponse  Pitch, $HTTP_STATUS_CODE
     * 
     * @Route("/pitches/{id}", name="show_pitch", methods={"GET"})
     */
    public function showPitch($id, PitchRepository $pitchRepository)
    {
       
        $statusCode = Response::HTTP_OK;

        $arrayResponse = SELF::$dataResponseTpl;
       
        $pitch = $pitchRepository->find($id);

        if(!empty($pitch)) {

            $tmpArray = [
                
                "type" => "pitches",
                
                "id" => $pitch->getId(),
                
                "attributes" => [
                
                    "name" => $pitch->getName(),
                
                    "sport" => $pitch->getSport()->getName(),
                ]
            ]; 
            
            $arrayResponse["data"] = $tmpArray;
        
        } else {

            $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse = $this->prepareErrorResponse("Pitch id: $id was not found", $statusCode);
        }

        return new JsonResponse($arrayResponse, $statusCode);
    }


    /**
     * Fetch all Pitch slots from database with the given Pitch {$id} and return it as a Json response
     * 
     * @param int $id
     * 
     * @return JsonResponse  Slots[], $HTTP_STATUS_CODE
     * 
     * @Route("/pitches/{id}/slots", name="get_pitch_slots", methods={"GET"})
     */
    public function getPitchSlots($id, PitchRepository $pitchRepository)
    {
       
        $statusCode = Response::HTTP_OK;

        $arrayResponse = array_merge(SELF::$metaResponseTpl, SELF::$dataResponseTpl);
       
        $pitch = $pitchRepository->find($id);

        if(!empty($pitch)) {

            $slots = $pitch->getSlots();

            if(!empty($slots) && !empty(count($slots))) {

                $arrayResponse["meta"] ["total_items"] = count($slots);
    
                foreach($slots as $slot) {
    
                    $tmpArray = [
                        
                        "type" => "slots",
                        
                        "id" => $slot->getId(),
                        
                        "attributes" => [
                            
                            "starts" => $slot->getStarts()->format("Y-m-d\TH:i:sP"),
                            
                            "ends" => $slot->getEnds()->format("Y-m-d\TH:i:sP"),
                            
                            "price" => $slot->getPrice(),

                            "currency" => $slot->getCurrency()->getCode(),
                            
                            "available" => $slot->getAvailable()                                
                        ]
                    ];
                  
                    $arrayResponse["data"] [] = $tmpArray;
                }
            
            } else {

                $statusCode = Response::HTTP_NOT_FOUND;
            
                $arrayResponse = $this->prepareErrorResponse("No slots were found for Pitch id: $id", $statusCode);
            }
        
        } else {

            $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse = $this->prepareErrorResponse("Pitch id: $id was not found", $statusCode);
        }

        return new JsonResponse($arrayResponse, $statusCode);
    }


    /**
     * Insert/update slots information for a given pitch {$id}
     * 
     * @param int $id
     * 
     * @return JsonResponse  Message, $HTTP_STATUS_CODE
     * 
     * @Route("/pitches/{id}/slots", name="post_pitch_slots", methods={"POST"})
     */
    public function postPitchSlots($id, Request $request, ObjectManager $manager, ValidatorInterface $validator, PitchRepository $pitchRepository, SlotRepository $slotRepository, CurrencyRepository $currencyRepository)
    {
        // Basic variables setup
        $upsertedSlots = 0;

        $statusCode = Response::HTTP_CREATED;

        $arrayResponse = SELF::$metaResponseTpl;
        // ---
        
        // First we must find out if Pitch {$id} exists in the database, otherwise it doesn't make sense to continue with the process
        $pitch = $pitchRepository->find($id);

        if(!empty($pitch)) {
        
            // Getting the request raw data (json body)
            $requestData = json_decode($request->getContent(), TRUE);

            // Checking if the Json request comes with the required structure
            if(!empty($requestData["data"]) && !empty(count($requestData["data"]))){

                // We will need the currencies from DB only once (before looping the foreach)
                $arrCurrencies = $this->getCurrenciesByCodes($currencyRepository);
                
                // Getting the "Slots" validation rules or "constraints" for this method
                $constraints = $this->getSlotValidationsConstraint();

                // Looping the incoming Slots data
                foreach ($requestData["data"] as $slotData) {

                    // Data input validation block:

                        // Running data input validations against incoming Slot item
                        $validationResultArray = $this->runValidations($slotData, $constraints, $validator);
                    
                        // Raise and error and break if some validation was wrong
                        if ($validationResultArray["result"] == FALSE) {
                      
                            $statusCode = Response::HTTP_BAD_REQUEST;
                      
                            $arrayResponse = $this->prepareErrorResponse($validationResultArray["error_message"], $statusCode);
                      
                            break;
                        } 

                    // --- Block end.

                    // Bussines Logic validations block:

                        // Validates bussines logic regarding the slots start time and end time (check method comments for more details)
                        $logicValidationResultArray = $this->validateDateTimeBusinessLogic($slotData["attributes"]["starts"], $slotData["attributes"]["ends"]);

                        // Raise and error and break if something was wrong
                        if ($logicValidationResultArray["result"] == FALSE) {
                            
                            $statusCode = Response::HTTP_BAD_REQUEST;
                    
                            $arrayResponse = $this->prepareErrorResponse($logicValidationResultArray["error_message"], $statusCode);

                            break;
                        }
                        
                    // --- Block end. 

                    // Data insertion / updating block:
                    
                        // $slot var placeholder
                        $slot = [];

                        // Checking if is an Insert or Update Operation
                        if(!empty($slotData["id"])) {
                            
                            // Checking if given id slot exists for the given pitch
                            $slot = $slotRepository->findOneBy(["id"=>$slotData["id"] ,"pitch"=>$pitch]);

                            // If Slot was not found then raise an error and break
                            if(empty($slot)){

                                $statusCode = Response::HTTP_NOT_FOUND;
            
                                $arrayResponse = $this->prepareErrorResponse("Unable to update Slot, Slot ".$slotData["id"]." and Pitch id: $id were not found", $statusCode);

                                break;
                            }                        
                        } else {

                            //It's an Insert so we must create a new Slot instance
                            $slot = new Slot();
                        }
                        
                        // Setting the attributes:
                        
                        $slot->setPitch($pitch);

                        $slot->setStarts((new \DateTime($slotData["attributes"]["starts"])));

                        $slot->setEnds((new \DateTime($slotData["attributes"]["ends"])));
        
                        $slot->setPrice($slotData["attributes"]["price"]);
                    
                        $slot->setAvailable($slotData["attributes"]["available"]);
                    
                        // Setting currency relationsship by code
                        if (array_key_exists($slotData["attributes"]["currency"], $arrCurrencies)){

                            $slot->setCurrency($arrCurrencies[($slotData["attributes"]["currency"])]);
                    
                        } else {

                            $statusCode = Response::HTTP_BAD_REQUEST;
            
                            $arrayResponse = $this->prepareErrorResponse($slotData["attributes"]['currency']." Is not a valid currency code", $statusCode);

                            break;
                        }
                        
                        // Persist Slot to the DB
                        $manager->persist($slot);

                        // Increment counter
                        $upsertedSlots++;

                    // --- Block end.
                }
                // --- Foreach end.

                // flush data to DB if everything was fine
                if($statusCode == Response::HTTP_CREATED ) {

                    $manager->flush();

                    $arrayResponse["meta"] ["message"] = "SUCCESS! - Slots were created or updated";
                    
                    $arrayResponse["meta"] ["total_items"] = $upsertedSlots;
                }
            
            } else {

                $statusCode = Response::HTTP_BAD_REQUEST;
                
                $arrayResponse = $this->prepareErrorResponse("Request data is empty or invalid", $statusCode);
            }

        } else {

            $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse = $this->prepareErrorResponse("Pitch id: $id was not found", $statusCode);
        }
        
        // Returning the final Json response to the client
        return new JsonResponse($arrayResponse, $statusCode);
    }


    /** --- UTILITY METHODS:  ---*/ 

    /**
     * Prepares Json Error response with an standard structure for code and error message 
     * 
     * @param string $errorMessage
     * 
     * @param int $statusCode Response::HTTP_NOT_FOUND or any other valid error HTTP_STATUS_CODE 400
     * 
    */
    protected function prepareErrorResponse($errorMessage, $statusCode = Response::HTTP_NOT_FOUND ): array
    {
        $errorMessage = (!empty($errorMessage))? "ERROR! - $errorMessage" : "ERROR! - Object not found";

        $arrayResponse = SELF::$errorResponseTpl;

        $arrayResponse["error"]["status"] = $statusCode;
            
        $arrayResponse["error"]["message"] = $errorMessage;

        return  $arrayResponse;
    }


    /**
     * Excute validations and returns an array with found errors 
     * 
     * @param array mixed $data The input data to be validated
     * 
     * @param  ValidatorInterface $validator Symfony validator object
    */
    protected function runValidations($data, $constraints, $validator)
    {
        // Preparing the array to be returned
        $validationResultArray = [
            
            "result" => TRUE,
            
            "error_message" => ""
        ];
        
        // Running the Synfony validator
        $validationErrors = $validator->validate($data, $constraints);
            
        // Checking for errors
        if (count($validationErrors)) {

            // Error separator
            $strSep = "";
            
            // Boolean flag to know that something went wrong
            $validationResultArray["result"] = FALSE;

            // Looping the found errors
            foreach ($validationErrors as $violation) {
                
                // Concat the messages
                $validationResultArray["error_message"] .= $strSep.($violation->getPropertyPath()).": ".($violation->getMessage());
                
                $strSep = " - ";
            }
        } 
        
        return $validationResultArray;
    }


    /**
     * 
     * Prepares the request validation mapping for incoming "Slots" arrays 
     * Returns validation constraints
     * 
    */
    protected function getSlotValidationsConstraint()
    {

        $constraint = new Assert\Collection([
                
            "type" => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
                new Assert\EqualTo('slots')
            ],

            "id" => [
                new Assert\Type(['type' => 'int']),
            ],

            "attributes" => new Assert\Collection([

                "starts" => [
                    new Assert\NotBlank(),
                    new Assert\DateTime(['format' => "Y-m-d\TH:i:sP"])
                ],

                "ends" => [
                    new Assert\NotBlank(),
                    new Assert\DateTime(['format' => "Y-m-d\TH:i:sP"])
                ],

                "price" => [
                    new Assert\NotBlank(),
                    new Assert\PositiveOrZero(),
                ],

                "currency" => [
                    new Assert\NotBlank(),
                    new Assert\Choice([
                        'choices' => ['GBP', 'EUR', 'USD'],
                        'message' => 'Use a valid currency code (GBP, EUR, USD)',
                    ]),
                ],

                "available" => [
                    new Assert\NotNull(),
                    new Assert\Type(['type' => 'bool']),
                ],

            ]),
            
        ]);

        return $constraint;
    }

    
    /**
     * Get currencies from database and return them in array with currencies codes as array keys 
     * 
     * @param CurrencyRepository $currencyRepository
     * 
    */
    protected function getCurrenciesByCodes($currencyRepository){

        $arrCurrencies = [];

        $currencies = $currencyRepository->findAll();

        if(!empty($currencies) && !empty(count($currencies))) {

            foreach($currencies as $currency) {

                $arrCurrencies[($currency->getCode())] = $currency;
            
            }
        }

        return $arrCurrencies;
    }

    
    /**
     * Check and validates bussines logic regarding the slots start time and end time 
     * 
     * @param string $startDateTime  Valid ISO8601 formated datetime string  "Y-m-d\TH:i:sP"
     * 
     * @param string $endDateTime  Valid ISO8601 formated datetime string  "Y-m-d\TH:i:sP"
     * 
     * @TO-DO: 
     * There are also other logic validations left for example:
     *  
     * Checking if there would be overlaping times with the existing records in the database, 
     * in order to avoid "overbookings". 
     * 
     * Due this involve some complexity (getting out of technical test time and scope) 
     * By this moment we will keep this validation in mind for future versions ;)
     * 
    */
    protected function validateDateTimeBusinessLogic($startDateTime, $endDateTime){

        $now = new \DateTime("NOW");
                    
        $startDateTime = new \DateTime($startDateTime);
                    
        $endDateTime = new \DateTime($endDateTime);
        
        // Preparing the array to be returned
        $validationResultArray = [
            
            "result" => TRUE,
            
            "error_message" => ""
        ];

        // Error separator
        $strSep = "";

        // It doesn't make sense to booking past time slots (the past doesn't exist ;) )
        if($startDateTime < $now) {

            $validationResultArray["result"] = FALSE;

            $validationResultArray["error_message"] = "The slot start time cannot be earlier than the current time";

            $strSep = " - ";
        }

        // Check if  the start time is before the end time
        if($startDateTime > $endDateTime) {

            $validationResultArray["result"] = FALSE;

            $validationResultArray["error_message"] .= $strSep."The slot end time cannot be earlier than the start time";
        }

        return $validationResultArray;
    }


}

