<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Alcohol;
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
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        
        $this->assertArrayHasKey('hydra:totalItems', $data);
        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertSame(50, $data['hydra:totalItems']);
    }
    
    public function testGetListFailure(): void
    {
        $this->client->request('GET', '/api/alcohols?page=0');
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetItemSuccess(): void
    {
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'architecto quod']);

        $response = $this->client->request('GET', '/api/alcohols/' . $alcohol->getId());

        $responseContent = $response->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertJson($responseContent);

        $responseData = json_decode($responseContent, true);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('@id', $responseData);
        $this->assertArrayHasKey('@type', $responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('name', $responseData);

        $this->assertSame('/api/alcohols/' . $alcohol->getId(), $responseData['@id']);
        $this->assertSame('Alcohol', $responseData['@type']);
        $this->assertEquals($alcohol->getId(), $responseData['id']);
        $this->assertEquals($alcohol->getName(), $responseData['name']);
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

        $postData = [
            'name' => 'Test Beer',
            'type' => 'beer',
            'description' => 'Test for creating a delicious beer',
            'producer' => "/api/producers/48c4de77-111a-4089-8a21-fa41da88ec0f", 
            'abv' => 7.5,
            "image" => "/api/images/df29e726-ccd0-4dee-8f90-1a0fad5efa70"
        ];
    
        $this->client->request(
            'POST',
            '/api/alcohols',
            ['json' => $postData],
        );
        
        $this->assertResponseIsSuccessful();
        
        $responseContent = $this->client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('name', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('abv', $responseData);
        
        $this->assertEquals('Test Beer', $responseData['name']);
        $this->assertEquals('beer', $responseData['type']);
        $this->assertEquals(7.5, $responseData['abv']);
        
        $this->assertArrayHasKey('producer', $responseData);
        $this->assertEquals('48c4de77-111a-4089-8a21-fa41da88ec0f', $responseData['producer']['id']);
        $this->assertEquals('Hartmann-Huels', $responseData['producer']['name']);
        $this->assertEquals('Ukraine', $responseData['producer']['country']);

        $this->assertArrayHasKey('image', $responseData);
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
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'vel quasi']);
        $itemId = $alcohol->getId();

        $this->client->request('PUT', '/api/alcohols/' . $itemId);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateItemSuccess(): void
    {
        $this->authenticateClient();
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'vel quasi']);
        $itemId = $alcohol->getId();

        $updatedData = [
            'name' => 'Updated Beer',
            'type' => 'beer',
            'description' => 'Updated beer description',
            'producer' => "/api/producers/48c4de77-111a-4089-8a21-fa41da88ec0f", 
            'abv' => 7.5,
            "image" => "/api/images/df29e726-ccd0-4dee-8f90-1a0fad5efa70"
        ];

        $this->client->request(
            'PUT',
            '/api/alcohols/' . $itemId, 
            ['json' => $updatedData],
        );

        $this->assertResponseIsSuccessful();

        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $updatedAlcohol = $entityManager->getRepository(Alcohol::class)->find($itemId);

        $this->assertNotNull($updatedAlcohol);
        $this->assertEquals('Updated Beer', $updatedAlcohol->getName());
        $this->assertEquals('beer', $updatedAlcohol->getType());
        $this->assertEquals('Updated beer description', $updatedAlcohol->getDescription());
        $this->assertEquals('48c4de77-111a-4089-8a21-fa41da88ec0f', $updatedAlcohol->getProducer()->getId());
        $this->assertEquals(7.5, $updatedAlcohol->getAbv());
    }

    public function testUpdateItemFailure(): void
    {
        $this->authenticateClient();
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'vel quasi']);
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
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'vel quasi']);
        $itemId = $alcohol->getId();

        $this->client->request('DELETE', '/api/alcohols/' . $itemId);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteItemSuccess(): void
    {
        $this->authenticateClient();
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $alcohol = $entityManager->getRepository(Alcohol::class)->findOneBy(['name' => 'vel quasi']);
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
