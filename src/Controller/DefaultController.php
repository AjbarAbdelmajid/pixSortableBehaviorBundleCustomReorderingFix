<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Slide;
use App\Application\Sonata\MediaBundle\Entity\Media;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Entity\GalleryManager;
use Doctrine\ORM\EntityManager;

use \DOMDocument;
use \DOMXPath;

class DefaultController extends AbstractController
{

    ############################### get the entity class     ########################################
        public function getEntityClass($entity){
            $className = '';

            if($entity === 'categories')
                    $className  = 'App\Entity\Categories';

                elseif($entity === 'slide')
                    $className = 'App\Entity\Slide';
            return $className;
        }
    #

    ############################### move position by dragging ###################################
        /**
         * @Route("/dragdrop/{id}/{entity}/{position}", name="dragdrop", options={"expose"=true})
         */
        public function dragdrop($entity ='categories', $id = '2', $position = '4')
        {

            # get the entity class
                $className = $this->getEntityClass($entity);
                
            # this is where the magic happend
            
                # old position is the position of the selected item
                    $oldPosition = $oldPosition= $this->getDoctrine()
                        ->getRepository($className)
                        ->findPositionById($id);

                # set the new position's numbore from type string to integer
                    $newPosition = intval($position);
                
                # initiate variables 
                    $success = '';
                    $allPositionInRange= null;

                # if dragging is up to down, Ex => set item with pos == 1 to pos = 5
                    if($oldPosition < $newPosition){

                        # get all positions between old and new position  where oldPos < newPos
                            $allPositionInRange = $this->getDoctrine()
                            ->getRepository($className)
                            ->positionRange($oldPosition, $newPosition);
                    }
                # if dragging is down to up, Ex => set item with pos == 5 to pos = 1
                    elseif ($oldPosition > $newPosition) {

                        # get all positions between old and new position
                            $allPositionInRange = $this->getDoctrine()
                            ->getRepository($className)
                            ->positionRange($newPosition, $oldPosition, 'desc');
                    }
                
                # set the old position to 99999 to free space
                    $success = $this->getDoctrine()
                        ->getRepository($className)
                        ->resetPosition($oldPosition, 999999);

                # reset the postion of all items in the range of oldPos and newPos
                    for ($i = 0 ; $i < count($allPositionInRange) - 1; $i++){
                        $this->getDoctrine()
                            ->getRepository($className)
                            ->resetPosition($allPositionInRange[$i+1], $allPositionInRange[$i]);
                    }

                # reset the val of old position to new position
                    $oldPosition= $this->getDoctrine()
                        ->getRepository($className)
                        ->findPositionById($id);

                    $success = $this->getDoctrine()
                        ->getRepository($className)
                        ->resetPosition($oldPosition, $newPosition);
            #

        return  new JsonResponse($allPositionInRange);
        }
    #

    ############################### move position by clicking ###################################
        /**
         * @Route("/test/{newPosition}/{currentPosition}/{entity}/{direction}", name="top_bottom", options={"expose"=true})
         */
        public function topButtom($newPosition, $currentPosition, $direction, $entity){

            $className = $this->getEntityClass($entity);
            if ($direction === 'bottom'){

                # get all positions between current and new position  where oldPos < newPos
                    $allPositionInRange = $this->getDoctrine()
                    ->getRepository($className)
                    ->positionRange($currentPosition, $newPosition);
            }
            elseif ('top'){
                
                # get all positions between old and new position
                    $allPositionInRange = $this->getDoctrine()
                        ->getRepository($className)
                        ->positionRange($newPosition, $currentPosition, 'desc');
            }
            
            # set the old position to 99999 to free space
                $success = $this->getDoctrine()
                    ->getRepository($className)
                    ->resetPosition($currentPosition, 999999);
                
            # reset the postion of all items in the range of oldPos and newPos
                for ($i = 0 ; $i < count($allPositionInRange) - 1; $i++){
                    $this->getDoctrine()
                        ->getRepository($className)
                        ->resetPosition($allPositionInRange[$i+1], $allPositionInRange[$i]);
                }
            # reset the val of old position to new position
                $success = $this->getDoctrine()
                    ->getRepository($className)
                    ->resetPosition(999999, $newPosition);
        
            return  new JsonResponse( $allPositionInRange);
        }
    #
        public function spelingCorrector($entity, $namespace){
            # get the position of EntityeName 
            $EntityNamePos = strpos(strtolower($namespace), $entity);

            #get the value of EntityeName 
                $EntityName = substr($namespace,$EntityNamePos);

            #ucfirst the EntityName 
                $correctEntityName = ucfirst(strtolower($EntityName));

            # replace new EntityName with the Old ENtityName
                $namespace = str_replace($EntityName, $correctEntityName, $namespace); 
            return $namespace;
        }
    ############################### move position by one step ###################################
        /**
         * @Route("/movebyOne/{currentPosition}/{entity}/{direction}", name="movebyone", options={"expose"=true})
         */
        public function movebyone($currentPosition, $direction, $entity){

            $className = $this->getEntityClass($entity);
                            
            # get the new position
                $newPosition = $this->getDoctrine()
                    ->getRepository($className)
                    ->getPosByPos($currentPosition, $direction);

            # set the old position to 99999 to free space
                $success = $this->getDoctrine()
                    ->getRepository($className)
                    ->resetPosition($currentPosition, 999999);
            
            # set the newpos to oldpos val
                $success = $this->getDoctrine()
                    ->getRepository($className)
                    ->resetPosition($newPosition, $currentPosition);

            # reset the val of old position to new position
                $success = $this->getDoctrine()
                    ->getRepository($className)
                    ->resetPosition(999999, $newPosition);
                    
            return  new Response( $newPosition);
            
        }
    #

    ############################### set the first position= 0 ###################################
        /**
         * @Route("/checkposition/{currentPosition}/{entity}", name="checkposition", options={"expose"=true})
         */
        public function checkposition($currentPosition, $entity){
            
            $className = $this->getEntityClass($entity);
            # get the first position
            $firstPosition = $this->getDoctrine()
                ->getRepository($className)
                ->getFirstPos();
            
            if (intval($currentPosition) == $firstPosition){
                $success = $this->getDoctrine()
                ->getRepository($className)
                ->resetPosition($currentPosition, 0);
            }

            if (intval($currentPosition) === $firstPosition and $firstPosition === 0){
                return  new JsonResponse( ['stop']);
            }
            else {
                return  new JsonResponse( ['dont stop']);
            }     
        }
    #

}
