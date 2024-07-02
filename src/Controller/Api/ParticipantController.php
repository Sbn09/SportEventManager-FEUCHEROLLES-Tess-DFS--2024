<?php

namespace App\Controller\Api;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParticipantController extends AbstractController
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    )
    {
        // ...
    }

    #[Route('/api/participants', name: 'app_api_participant')]
    public function index(): JsonResponse
    {
        $participants = $this->participantRepository->findAll();

        return $this->json([
            'participants' => $participants,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/participants/{id}', name: 'app_api_participant_get',  methods: ['GET'])]
    public function get(?Participant $participant = null): JsonResponse
    {
        if(!$participant)
        {
            return $this->json([
                'error' => 'Ressource does not exist',
            ], 404);
        }

        return $this->json($participant, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/participants/{id}', name: 'app_api_participant_add', methods: ['POST'])]
    public function add( 
        #[MapRequestPayload('json', ['groups' => 'create'])] Participant $participant
    ): JsonResponse
    {
        $this->em->persist($participant);
        $this->em->flush();
        
        return $this->json($participant, 200, [], [
            'groups' => ['read']
        ]);
    }
    #[Route('/api/participants/{id}', name: 'app_api_participant_update', methods: ['PUT'])]
    public function update( Participant $participant, Request $request): JsonResponse
    {
        $data = $request->getContent();
        $this->serializer->deserialize($data, Participant::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $participant,
            'groups' => ['update']
        ]);
        $this->em->flush();

        return $this->json($participant, 200, [], [
            'groups' => ['read']
        ]);
    }
    
    #[Route('/api/participants/{id}', name: 'app_api_participants_delete',  methods: ['DELETE'])]
    public function delete(Participant $participant): JsonResponse
    {
        $this->em->remove($participant);
        $this->em->flush();

        return $this->json([
           'message' => 'participant deleted',
        ], 200, [], [
            'groups' => ['read']
        ]);
    }
}
