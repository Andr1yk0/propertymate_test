<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ContactsApiController extends AbstractController
{
    /**
     * @Route("/api/contacts", name="contacts_api")
     * @param Request $request
     * @param ContactRepository $contactRepository
     * @return JsonResponse
     */
    public function index(Request $request, ContactRepository $contactRepository)
    {
        try{
            $contacts = $contactRepository->getFiltered($request, $this->getParameter('pagination.on_page'));
        }catch (\Throwable $exception){
            return  $this->json(['error'=>$exception->getMessage()],400);
        }
        return $this->json($contacts);
    }
}
