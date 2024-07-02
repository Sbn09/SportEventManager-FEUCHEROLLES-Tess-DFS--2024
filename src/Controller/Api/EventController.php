<?php

namespace App\Controller\Api;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{

    public function __construct(
        private EventRepository $eventRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    )
    {
        // ...
    }
    
    #[Route('/api/events', name: 'app_api_event')]
    public function index(): JsonResponse
    {
        $events = $this->eventRepository->findAll();

        return $this->json([
            'events' => $events,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/events/{id}', name: 'app_api_event_get',  methods: ['GET'])]
    public function get(?Event $event = null): JsonResponse
    {
        if(!$event)
        {
            return $this->json([
                'error' => 'Ressource does not exist',
            ], 404);
        }

        return $this->json($event, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/events/{id}', name: 'app_api_event_add', methods: ['POST'])]
    public function add( 
        #[MapRequestPayload('json', ['groups' => 'create'])] Event $event
    ): JsonResponse
    {
        $this->em->persist($event);
        $this->em->flush();
        
        return $this->json($event, 200, [], [
            'groups' => ['read']
        ]);
    }
    #[Route('/api/events/{id}', name: 'app_api_event_update', methods: ['PUT'])]
    public function update( Event $event, Request $request): JsonResponse
    {
        $data = $request->getContent();
        $this->serializer->deserialize($data, Event::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $event,
            'groups' => ['update']
        ]);
        $this->em->flush();

        return $this->json($event, 200, [], [
            'groups' => ['read']
        ]);
    }
    
    #[Route('/api/events/{id}', name: 'app_api_event_delete',  methods: ['DELETE'])]
    public function delete(Event $event): JsonResponse
    {
        $this->em->remove($event);
        $this->em->flush();

        return $this->json([
           'message' => 'Event deleted',
        ], 200, [], [
            'groups' => ['read']
        ]);
    }
}
