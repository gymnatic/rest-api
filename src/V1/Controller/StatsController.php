<?php

namespace App\V1\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\V1\Entity\User;
use App\V1\Helper\ExternalDataHelper;

class StatsController extends AbstractController
{
    private ExternalDataHelper $externalDataHelper; // separate communication with external api
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, ExternalDataHelper $dataHelper)
    {
        $this->externalDataHelper = $dataHelper;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/v1/syncData')]
    public function syncData()
    {
        // could be executed via cron job e.g. each hour
        // also caching system should be used if data was not modified!
        $status = $this->externalDataHelper->syncUserData();

        return $this->json(['status' => $status]);
    }

    #[Route('/api/v1/postingStats')]
    public function getPostingStats(Request $request): JsonResponse
    {
        $gender = $request->query->get('gender');
        $status = $request->query->get('status');

        $fields = [
            'gender' => $gender,
            'status' => $status,
        ];

        // getting all users with specific attributes
        $users = $this->entityManager->getRepository(User::class)->findByFields($fields);

        // calculate average word count
        $averageWordCount = $this->calculateAverageWordCount($users);

        return $this->json(['average' => $averageWordCount]);
    }

    private function calculateAverageWordCount(array $users)
    {
        $totalWords = 0;
        $count = 0;

        foreach ($users as $user) {
            $posts = $user->getPosts();

            foreach ($posts as $post) {
                $totalWords += str_word_count($post->getBody());
                $count++;
            }
        }

        // important: prevent devision by 0
        if ($count > 0) {
            return $totalWords / $count;
        }

        return 0;
    }
}
