<?php

namespace App\V1\Helper;

use Doctrine\ORM\EntityManagerInterface;
use App\V1\Entity\User;
use App\V1\Entity\Post;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class ExternalDataHelper
{
    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $restApiClient, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->client = $restApiClient;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    // get 10 user items frome exteral api
    public function getUsersData()
    {
        $path = 'users';

        try {
            $response = $this->client->request('GET', $path);
            $parsedResponse = $response->toArray();

            return $parsedResponse;
        } catch (Exception $e) {
            $this->logger->critical(
                'Failed to get users from external API.',
                [
                    'exception' => $e,
                ],
            );
        }

        return [];
    }

    // get 10 post items from external api
    public function getPostsData(): array
    {
        $path = 'posts';

        try {
            $response = $this->client->request('GET', $path);
            $parsedResponse = $response->toArray();

            return $parsedResponse;
        } catch (Exception $e) {
            $this->logger->critical(
                'Failed to get posts from external API.',
                [
                    'exception' => $e,
                ],
            );
        }

        return [];
    }

    // get all posts by user from external api
    public function getPostsDataByUser(string $user)
    {
        $path = 'users/' . $user . '/posts';

        try {
            $response = $this->client->request('GET', $path);
            $parsedResponse = $response->toArray();

            return $parsedResponse;
        } catch (Exception $e) {
            $this->logger->critical(
                'Failed to get posts by user (user_api_id: ' . $user . ') from external API.',
                [
                    'exception' => $e,
                ],
            );
        }
    }

    public function syncUserData(): string
    {
        try {
            $usersData = $this->getUsersData();

            foreach ($usersData as $userData) {
                // check if user already exists in db
                $user = $this->entityManager->getRepository(User::class)->findOneByApiId($userData['id']) ?? new User();

                $user->setApiId($userData['id'])
                    ->setName($userData['name'])
                    ->setEmail($userData['email'])
                    ->setGender($userData['gender'])
                    ->setStatus($userData['status']);
                $this->entityManager->persist($user);

                $this->syncPostDataForUser($user);
            }

            $this->entityManager->flush();

            return 'success';
        } catch (Exception $e) {
            $this->logger->critical(
                'Failed to sync users data from external API.',
                [
                    'exception' => $e,
                ],
            );
        }
        return 'failed';
    }

    private function syncPostDataForUser(User $user)
    {
        try {
            $postsData = $this->getPostsDataByUser($user->getApiId());

            foreach ($postsData as $postData) {
                // check if post already exists in db
                $post = $this->entityManager->getRepository(Post::class)->findOneByApiId($postData['id']) ?? new Post();

                $post->setUser($user);

                $post->setApiId($postData['id'])
                    ->setTitle($postData['title'])
                    ->setBody($postData['body']);

                $this->entityManager->persist($post);
            }
        } catch (Exception $e) {
            $this->logger->critical(
                'Failed to sync posts data (user api_id: ' . $user->getApiId() . ') from external API.',
                [
                    'exception' => $e,
                ],
            );
        }
    }
}
