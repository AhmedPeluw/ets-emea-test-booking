<?php
declare(strict_types=1);
namespace App\Controller;

use App\DTO\CreateBookingDTO;
use App\Service\BookingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/bookings', name: 'api_bookings_')]
#[IsGranted('ROLE_USER')]
class BookingController extends AbstractController
{
    public function __construct(
        private BookingService $bookingService,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = min(50, max(1, (int) $request->query->get('itemsPerPage', 10)));

        $result = $this->bookingService->getUserBookings(
            $this->getUser(),
            $page,
            $itemsPerPage
        );

        return $this->json([
            'success' => true,
            'data' => array_map(fn($booking) => $this->formatBooking($booking), $result['items']),
            'pagination' => [
                'total' => $result['total'],
                'pages' => $result['pages'],
                'currentPage' => $result['currentPage'],
                'itemsPerPage' => $result['itemsPerPage']
            ]
        ]);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getUserBooking($this->getUser(), $id);
            return $this->json([
                'success' => true,
                'data' => $this->formatBooking($booking)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $dto = $this->serializer->deserialize(
                $request->getContent(),
                CreateBookingDTO::class,
                'json'
            );

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'errors' => $this->formatValidationErrors($errors)
                ], Response::HTTP_BAD_REQUEST);
            }

            $booking = $this->bookingService->createBooking($this->getUser(), $dto);

            return $this->json([
                'success' => true,
                'message' => 'Réservation créée avec succès',
                'data' => $this->formatBooking($booking)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'cancel', methods: ['DELETE'])]
    public function cancel(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $reason = $data['reason'] ?? null;

            $booking = $this->bookingService->cancelBooking($this->getUser(), $id, $reason);

            return $this->json([
                'success' => true,
                'message' => 'Réservation annulée avec succès',
                'data' => $this->formatBooking($booking)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function formatBooking($booking): array
    {
        $data = [
            'id' => $booking->getId(),
            'sessionId' => $booking->getSessionId(),
            'status' => $booking->getStatus(),
            'createdAt' => $booking->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $booking->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        if ($booking->getSession()) {
            $session = $booking->getSession();
            $data['session'] = [
                'id' => $session->getId(),
                'language' => $session->getLanguage(),
                'date' => $session->getDate()->format('Y-m-d'),
                'time' => $session->getTime(),
                'location' => $session->getLocation(),
                'level' => $session->getLevel()
            ];
        }

        return $data;
    }

    private function formatValidationErrors($errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formatted;
    }
}
