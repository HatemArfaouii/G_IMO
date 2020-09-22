<?php

namespace App\Controller;
use App\Entity\Contact;
use App\Entity\Property;
use App\Entity\PropertySearch;
use App\Form\ContactType;
use App\Form\PropertySearchType;
use App\Notification\ContactNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use App\Repository\PropertyRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class PropertyController extends AbstractController
{
    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var PropertyRepository
     */
    private $repository;

    public function __construct(PropertyRepository $repository)
    {
        $this->repository =$repository;
    }
    
    /**
     * @Route("/biens", name="property.index")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $serach = new PropertySearch();
        $form =$this->createForm(PropertySearchType::class, $serach);
        $form ->handleRequest($request);

        $properties = $paginator->paginate(
            $this->repository->findAllVisibleQuery($serach),
            $request->query->getInt('page', 1),
            8
        );
        return $this->render('property/index.html.twig', [
            'current_menu' =>'properties',
            'properties' =>$properties,
            'form' => $form->createView()
            ]);
    }
    /**
     * @Route("/biens/{slug}-{id}", name="property.show", requirements={"slug": "[a-z0-9\-]*"})
     * @return Response
     * @param Property $property
     */
    public function show(Property $property, string $slug, Request $request, ContactNotification $notification):Response
    {
         if($property->getSlug()!== $slug)
        {
            return $this->redirectToRoute('property.show', [
                'id' =>$property->getId(),
                'slug'=>$property->getSlug()
            ], 301);
        }

        $contact = new Contact();
        $contact->setProperty($property);
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $notification->notify($contact);
//            $this->addFlash('success', 'Votre message a bien été envoyé');

            return $this->redirectToRoute('property.show', [
                'id' =>$property->getId(),
                'slug'=>$property->getSlug()
            ]);
        }


        return $this->render('property/show.html.twig',[
            'property' => $property, 
            'current_menu'=>'properties',
            'form' => $form->createView()
        ]);
    }
}
