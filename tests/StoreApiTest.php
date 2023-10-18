<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Alcohol;
use App\Entity\Image;
use App\Entity\Producer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreApiTest extends ApiTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    private function authenticateClient($username = 'user1', $password = 'pass1')
    {
        $client = static::createClient();
    
        $response = $client->request('POST', '/login_check', [
            'json' => [
                'username' => $username,
                'password' => $password,
            ],
        ]);
    
        $data = $response->toArray();
        $token = $data['token'];
    
        $client = static::createClient([], [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
    
        $this->client = $client;
    }
    public function testGetCollectionSuccess(): void
    {
        $response = $this->client->request('GET', '/api/alcohols?page=1&itemsPerPage=30');
        $content = $response->getContent();
        $data = json_decode($content, true);
    
        $this->assertResponseIsSuccessful();
    
        $expectedData = [
            'hydra:totalItems' => 50,
            "hydra:member" => []
        ];
    
        $this->assertJsonContains($expectedData);
    }
    
    public function testGetListFailure(): void
    {
        $this->client->request('GET', '/api/alcohols?page=0');
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetItemSuccess(): void
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'Test Alcohol']);
    
        $this->client->request('GET', '/api/alcohols/' . $alcohol->getId());
    
        $this->assertResponseIsSuccessful();
    
        $expectedData = [
            '@id' => '/api/alcohols/' . $alcohol->getId(),
            '@type' => 'Alcohol',
            'id' => $alcohol->getId()->toString(),
            'name' => $alcohol->getName(),
        ];
    
        $this->assertJsonContains($expectedData);
    }
    
    public function testGetItemFailure(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->client->request('GET', '/api/alcohols/999');
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateItemUnauthenticated(): void
    {
        $this->client->request('POST', '/api/alcohols');
        $this->assertResponseStatusCodeSame(401);
    }
    
    public function testCreateItemSuccess(): void
    {
        $this->authenticateClient();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $producer = $entityManager->getRepository(Producer::class)->findOneBy(['name' => 'Test Company']);
        $image = $entityManager->getRepository(Image::class)->findOneBy(['name' => 'Test Image']);

        $postData = [
            'name' => 'Test Beer',
            'type' => 'beer',
            'description' => 'Test for creating a delicious beer',
            'producer' => '/api/producers/' . $producer->getId(),
            'abv' => 7.5,
            'image' => '/api/images/' . $image->getId(),
        ];
    
        $this->client->request(
            'POST',
            '/api/alcohols',
            ['json' => $postData],
        );
        
        $this->assertResponseIsSuccessful();
        
        $expectedData = [
            'name' => 'Test Beer',
            'type' => 'beer',
            'abv' => 7.5,
            'producer' => [
                'id' => $producer->getId()->toString(),
                'name' => $producer->getName(),
                'country' => $producer->getCountry(),
            ],
            'image' => [
                'id' => $image->getId()->toString(),
                'name' => $image->getName(),
                'url' => $image->getUrl(),
            ],
        ];
    
        $this->assertJsonContains($expectedData);
    }
    public function testCreateItemFailure(): void
    {
        $this->authenticateClient();
    
        $this->client->request(
            'POST',
            '/api/alcohols',
            ['json' => []]
        );
    
        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateItemUnauthenticated(): void
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'Test Alcohol']);
        $itemId = $alcohol->getId();

        $this->client->request('PUT', '/api/alcohols/' . $itemId);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateItemSuccess(): void
    {
        $this->authenticateClient();
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'Test Alcohol']);
        $producer = $entityManager->getRepository(Producer::class)->findOneBy(['name' => 'Test Company']);
        $image = $entityManager->getRepository(Image::class)->findOneBy(['name' => 'Test Image']);

        $updatedData = [
            'name' => 'Updated Beer',
            'type' => 'beer',
            'description' => 'Updated beer description',
            'producer' => "/api/producers/" . $producer->getId(), 
            'abv' => 7.5,
            "image" => "/api/images/" . $image->getId()
        ];

        $this->client->request(
            'PUT',
            '/api/alcohols/' . $alcohol->getId(), 
            ['json' => $updatedData],
        );

        $this->assertResponseIsSuccessful();

        $expectedData = [
            'name' => 'Updated Beer',
            'type' => 'beer',
            'description' => 'Updated beer description',
            'producer' => [
                'id' => $producer->getId()->toString(),
                'name' => $producer->getName(),
                'country' => $producer->getCountry(),
            ],
            'abv' => 7.5,
            'image' => [
                'id' => $image->getId()->toString(),
                'name' => $image->getName(),
                'url' => $image->getUrl(),
            ],
        ];
    
        $this->assertJsonContains($expectedData);
    }

    public function testUpdateItemFailure(): void
    {
        $this->authenticateClient();
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'Test Alcohol']);
        $itemId = $alcohol->getId();

        $this->client->request(
            'PUT',
            '/api/alcohols/' . $itemId, 
            ['json' => []],
        );

        $this->assertResponseStatusCodeSame(422); 
    }

    public function testDeleteItemUnauthenticated(): void
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'Test Alcohol']);
        $itemId = $alcohol->getId();

        $this->client->request('DELETE', '/api/alcohols/' . $itemId);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteItemSuccess(): void
    {
        $this->authenticateClient();
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'Test Alcohol']);
        $itemId = $alcohol->getId();

        $this->client->request('DELETE', '/api/alcohols/' . $itemId);

        $this->assertResponseIsSuccessful();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $deletedAlcohol = $entityManager->getRepository(Alcohol::class)->find($itemId);
        
        $this->assertNull($deletedAlcohol, 'The item should be deleted');
    }

    public function testDeleteItemFailure(): void
    {
        $this->authenticateClient();
        $this->expectException(NotFoundHttpException::class);
        $this->client->request('DELETE', '/api/alcohols/999');
        $this->client->getResponse()->getContent();
    
        $this->assertResponseStatusCodeSame(404);
    }
}
