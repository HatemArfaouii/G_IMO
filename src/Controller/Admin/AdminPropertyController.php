<?php

namespace App\Controller\Admin;

use App\Entity\Option;
use App\Entity\Property;
use App\Form\PropertySearchType;
use App\Form\PropertyType;
use App\Repository\PropertyRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response; 

 use Doctrine\ORM\EntityManagerInterface;

class AdminPropertyController extends AbstractController
{
    /**
     * @var Propertyrepository
     */
    private $repository;
 
    private $entityManager;

    public function __construct(Propertyrepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository =$repository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin", name="admin.property.index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request)
    {
        $properties = $paginator->paginate(
            $this->repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );
        $form =$this->createForm(PropertyType::class);
        $form ->handleRequest($request);

        return $this->render('admin/property/index.html.twig', [
            'properties' =>$properties,
            'form' => $form->createView()

        ]);
     }

    /**
     * @Route("/admin/property/create", name="admin.property.new")
     */
    public function new(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $property = new Property();
        $form = $this->createForm(PropertyType::class, $property);
        $form ->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($property);
            $em->flush();
             $this->addFlash('success', 'Bien creé avec succés');

            return $this->redirectToRoute('admin.property.index');
        }
        return $this->render('admin/property/new.html.twig', [
            'property' => $property,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/property/{id}", name="admin.property.edit", methods="GET|POST")
     * @param Property $property
     * @return \Symfony\Component\HttpFoundation\Response
     * @param Request $request
     */
    public function edit(Property $property, Request $request)
    {
         $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(PropertyType::class, $property);
        $form ->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
             $this->addFlash('success', 'Bien modifié avec succés');
            return $this->redirectToRoute('admin.property.index');
        }
        return $this->render('admin/property/edit.html.twig', [
            'property' => $property,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/property/{id}", name="admin.property.delete", methods="DELETE")
     * @param Property $property
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Property $property, Request $request):Response
    {
        if ($this->isCsrfTokenValid('delete'.$property->getId(), $request->get('_token'))) {
           
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($property);
            $entityManager->flush();
            $this->addFlash('success', 'Bien supprimé avec succés');

        }
       return $this->redirectToRoute('admin.property.index');

    }
}