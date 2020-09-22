<?php

namespace App\Controller;
use App\Entity\Property;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PropertyRepository;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends AbstractController
{
      
    /**
     * @Route("/home", name="home")
     * @param PropertyRepository $repository
     * @return Response
     */
    public function index(PropertyRepository $repository):Response
    {
        $properties = $repository ->findLatest();
        return $this->render('home/index.html.twig', [
            'properties' =>$properties,
         ]);
    }

   
}
