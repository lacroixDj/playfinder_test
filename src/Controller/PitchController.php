<?php

namespace App\Controller;

use App\Entity\Pitch;
use App\Repository\PitchRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

            $arrayResponse = SELF::$errorResponseTpl;

            $arrayResponse["error"]["status"] = $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse["error"]["message"] = "ERROR! - No Pitches were found";
        }

        return new JsonResponse($arrayResponse, $statusCode);
    }


    /**
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

            $arrayResponse = SELF::$errorResponseTpl;

            $arrayResponse["error"]["status"] = $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse["error"]["message"] = "ERROR! - Pitch id: $id was not found";

        }

        return new JsonResponse($arrayResponse, $statusCode);
    }


    /**
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
                            
                            "starts" => $slot->getStarts()->format("c"),
                            
                            "ends" => $slot->getEnds()->format("c"),
                            
                            "price" => $slot->getPrice(),

                            "currency" => $slot->getCurrency()->getCode(),
                            
                            "available" => $slot->getAvailable()                                
                        ]
                    ];
                  
                    $arrayResponse["data"] [] = $tmpArray;
                }
            
            } else {

                $arrayResponse = SELF::$errorResponseTpl;

                $arrayResponse["error"]["status"] = $statusCode = Response::HTTP_NOT_FOUND;
            
                $arrayResponse["error"]["message"] = "ERROR! - No slots were found for Pitch id: $id ";
            
            }
        
        } else {

            $arrayResponse = SELF::$errorResponseTpl;

            $arrayResponse["error"]["status"] = $statusCode = Response::HTTP_NOT_FOUND;
            
            $arrayResponse["error"]["message"] = "ERROR! - Pitch id: $id was not found";

        }

        return new JsonResponse($arrayResponse, $statusCode);
    }
}

