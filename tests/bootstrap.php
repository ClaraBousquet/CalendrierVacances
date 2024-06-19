<?php
declare(strict_types=1);

use App\Entity\User;
use App\Kernel;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\MigratorConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

function app(): Kernel
{
    static $kernel;
    $kernel ??= (function () {
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'test';
        $debug = $_ENV['APP_DEBUG'] ?? $_SERVER['APP_DEBUG'] ?? true;

        $kernel = new Kernel((string) $env, (bool) $debug);
        $kernel->boot();

        return $kernel;
    })();

    return $kernel;
}

/**
 * Shortcut to the test container (all services are public).
 */
function container(): ContainerInterface
{
    $container = app()->getContainer();

    return $container->has('test.service_container') ? $container->get('test.service_container') : $container;
}


/**
 * Create database if not exists.
 */
function createDatabase(): void
{
    $doctrine = container()->get('doctrine');
    $connection = $doctrine->getConnection($doctrine->getDefaultConnectionName());
    $params = $connection->getParams();
    $name = $params['path'] ?? $params['dbname'];
    unset($params['dbname'], $params['path'], $params['url']);
    $tmpConnection = DriverManager::getConnection($params);
    $tmpConnection->connect();

    if (\in_array($name, $tmpConnection->createSchemaManager()->listDatabases(), true)) {
        return;
    }

    $tmpConnection->createSchemaManager()->createDatabase(
        $tmpConnection->getDatabasePlatform()->quoteSingleIdentifier($name)
    );
}

function runMigrations(): void
{
    $dependencyFactory = container()->get('doctrine.migrations.dependency_factory');
    $dependencyFactory->getMetadataStorage()->ensureInitialized();
    $migratorConfiguration = new MigratorConfiguration();
    $planCalculator = $dependencyFactory->getMigrationPlanCalculator();
    $migrator = $dependencyFactory->getMigrator();
    $version = $dependencyFactory->getVersionAliasResolver()->resolveVersionAlias('latest');
    $plan = $planCalculator->getPlanUntilVersion($version);
    $migrator->migrate($plan, $migratorConfiguration);
}

function dropDatabase(): void
{
    $doctrine = container()->get('doctrine');
    $connection = $doctrine->getConnection($doctrine->getDefaultConnectionName());
    $params = $connection->getParams();
    $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($params['dbname']);
    $connection->getSchemaManager()->dropDatabase($name);
}

function createSchema(): void
{
    $entityManager = container()->get(EntityManagerInterface::class);
    $schemaTool = new SchemaTool($entityManager);
    $classes = $entityManager->getMetadataFactory()->getAllMetadata();
    $schemaTool->createSchema($classes);
}

/**
 * @return string[]
 */
function tables(): array
{
    $entityManager = container()->get(EntityManagerInterface::class);

    $notMappedSuperClassNames = \array_filter(
        $entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames(),
        fn (string $class): bool => false === $entityManager->getClassMetadata($class)->isMappedSuperclass,
    );

    return \array_map(
        fn (string $class): string => $entityManager->getClassMetadata($class)->getTableName(),
        $notMappedSuperClassNames
    );
}

function dropSchema(): void
{
    $entityManager = container()->get(EntityManagerInterface::class);

    $connection = $entityManager->getConnection();
    $connection->query('SET FOREIGN_KEY_CHECKS=0;');
    foreach (tables() as $table) {
        $connection->query(\sprintf('DROP TABLE `%s`;', $table));
    }
    $connection->query('SET FOREIGN_KEY_CHECKS=1;');

    // Ensure EntityManager doesn't contain entities after clearing DB
    $entityManager->clear();
}

/**
 * Use this helper at the beginning of a test to truncate all tables.
 */
function clearDatabase(): void
{
    $entityManager = container()->get(EntityManagerInterface::class);

    $connection = $entityManager->getConnection();
    $connection->executeQuery('SET foreign_key_checks = 0');
    foreach (tables() as $table) {
        $connection->executeQuery(\sprintf('DELETE FROM `%s`;', $table));
    }
    $connection->executeQuery('SET foreign_key_checks = 1');
    // Ensure EntityManager doesn't contain entities after clearing DB
    $entityManager->clear();
}

function save(object ...$entities): void
{
    $em = container()->get(EntityManagerInterface::class);
    foreach ($entities as $entity) {
        $em->persist($entity);
        $em->flush();
    }
}

function remove(object $entity): void
{
    $em = container()->get(EntityManagerInterface::class);
    $em->remove($entity);
    $em->flush();
}

function repository(string $className): EntityRepository
{
    return container()->get(EntityManagerInterface::class)->getRepository($className);
}


function login(?UserInterface $user, ?string $providerKey = 'main'): void
{
    $token = null !== $user ? new UsernamePasswordToken($user, null, $providerKey, $user->getRoles()) : null;
    container()->get('security.token_storage')->setToken($token);
}

function logout(): void
{
    container()->get('security.token_storage')->setToken(null);
}

function createUser(string $username = 'Romain', array $roles = [], $service): User
{
    $user = new User();
    $user->setUsername($username);
    $user->setPassword('toto');
    $user->setRoles($roles);
    $user->setIsCadre(false);
    $user->setEmail('a@a.fr');
    $user->setService($service);

    return $user;
}

function now(): \DateTimeImmutable
{
    return new \DateTimeImmutable();
}