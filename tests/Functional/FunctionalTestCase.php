<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for functional tests: boots the kernel, resets the database
 * schema to a clean state and loads the fixtures before each test.
 */
abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->resetDatabase();
    }

    protected function entityManager(): EntityManagerInterface
    {
        return $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function repository(string $entityClass): \Doctrine\Persistence\ObjectRepository
    {
        return $this->entityManager()->getRepository($entityClass);
    }

    protected function findUser(string $email): User
    {
        $user = $this->repository(User::class)->findOneBy(['email' => $email, 'isDeleted' => false]);
        $this->assertInstanceOf(User::class, $user);

        return $user;
    }

    /**
     * Logs the given user in by its fixture email (no password round-trip).
     *
     * Source: https://symfony.com/doc/7.4/testing.html#testing-protected-pages
     */
    protected function login(string $email): void
    {
        $this->client->loginUser($this->findUser($email));
    }

    private function resetDatabase(): void
    {
        $container = $this->client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        try {
            $schemaTool->dropSchema($metadata);
        } catch (\Throwable) {
            $dbPath = $connection->getParams()['path'] ?? null;
            if ($dbPath && file_exists($dbPath)) {
                $connection->close();
                @unlink($dbPath);
                $connection->connect();
            }
        }

        $schemaTool->createSchema($metadata);

        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor(
            $entityManager,
            new \Doctrine\Common\DataFixtures\Purger\ORMPurger($entityManager),
        );
        $executor->execute($container->get('doctrine.fixtures.loader')->getFixtures());

        $entityManager->clear();
    }
}
