<?php

namespace App\Controller\Api;

use App\Entity\Sport;
use App\Repository\SportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SportController extends AbstractController
{

    public function __construct(
        private SportRepository $sportRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    )
    {
        // ...
    }

    #[Route('/api/sports', name: 'app_api_sport')]
    public function index(): JsonResponse
    {
        $sports = $this->sportRepository->findAll();

        return $this->json([
            'sports' => $sports,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/sports/{id}', name: 'app_api_sport_get',  methods: ['GET'])]
    public function get(?Sport $sport = null): JsonResponse
    {
        if(!$sport)
        {
            return $this->json([
                'error' => 'Ressource does not exist',
            ], 404);
        }

        return $this->json($sport, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/sports/{id}', name: 'app_api_sport_add', methods: ['POST'])]
    public function add( 
        #[MapRequestPayload('json', ['groups' => 'create'])] Sport $sport
    ): JsonResponse
    {
        $this->em->persist($sport);
        $this->em->flush();
        
        return $this->json($sport, 200, [], [
            'groups' => ['read']
        ]);
    }
    #[Route('/api/sports/{id}', name: 'app_api_sport_update', methods: ['PUT'])]
    public function update( Sport $sport, Request $request): JsonResponse
    {
        $data = $request->getContent();
        $this->serializer->deserialize($data, Sport::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $sport,
            'groups' => ['update']
        ]);
        $this->em->flush();

        return $this->json($sport, 200, [], [
            'groups' => ['read']
        ]);
    }
    
    #[Route('/api/sports/{id}', name: 'app_api_sport_delete',  methods: ['DELETE'])]
    public function delete(Sport $sport): JsonResponse
    {
        $this->em->remove($sport);
        $this->em->flush();

        return $this->json([
           'message' => 'Sport deleted',
        ], 200, [], [
            'groups' => ['read']
        ]);
    }
}
