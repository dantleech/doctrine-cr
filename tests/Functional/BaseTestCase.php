<?php

namespace DTL\DoctrineCR\Tests\Functional;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use DTL\DoctrineCR\ObjectManager;
use DTL\DoctrineCR\NodeManager\Dbal;
use DTL\DoctrineCR\Path\Storage\Dbal\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use DTL\DoctrineCR\Tests\Functional\Resources\Entity\Article;
use DTL\DoctrineCR\Subscriber\CRSubscriber;
use DTL\DoctrineCR\Path\Storage\DbalStorage;
use DTL\DoctrineCR\Tests\Functional\Resources\Entity\Page;
use Symfony\Component\Filesystem\Filesystem;
use Metadata\MetadataFactory;
use Metadata\Driver\FileLocator;
use Doctrine\Common\Persistence\Mapping\Driver\DefaultFileLocator;
use DTL\DoctrineCR\Metadata\Locator\DoctrineLocator;
use Doctrine\ORM\Mapping\Driver\XmlDriver as DoctrineXmlDriver;
use DTL\DoctrineCR\Metadata\Driver\XmlDriver;
use DTL\DoctrineCR\Path\PathManager;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $entityManager;

    protected function setUp()
    {
        $this->initTmpDir();
    }

    protected function initTmpDir()
    {
        $filesystem = new Filesystem();
        if (file_exists($this->getTmpDir())) {
            $filesystem->remove($this->getTmpDir());
        }
        $filesystem->mkdir($this->getTmpDir());
    }

    protected function getTmpDir()
    {
        return __DIR__ . '/Resources/tmp';
    }

    protected function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $dbName = $this->getTmpDir() . '/test.db';
        if (file_exists($dbName)) {
            unlink($dbName);
        };

        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'path' => $dbName,
        ]);

        $schema = new Schema();
        $statements = $schema->toSql($this->connection->getDriver()->getDatabasePlatform());

        foreach ($statements as $statement) {
            $this->connection->exec($statement);
        }

        $tool = new SchemaTool($this->getEntityManager());
        $tool->createSchema([
            $this->getEntityManager()->getClassMetadata(Page::class)
        ]);

        return $this->connection;
    }

    protected function getEntityManager()
    {
        if ($this->entityManager) {
            return $this->entityManager;
        }

        $paths = [ 
            __DIR__ . '/Resources/config/doctrine'
        ];

        $config = Setup::createConfiguration($paths, true);
        $locator = new DefaultFileLocator($paths, '.dcm.xml');
        $config->setMetadataDriverImpl(new DoctrineXmlDriver($locator));
        $config->setProxyDir($this->getTmpDir());

        $metadataFactory = new MetadataFactory(
            new XmlDriver(
                new DoctrineLocator($locator)
            )
        );

        $this->pathManager = new PathManager(
            new DbalStorage($this->getConnection())
        );

        $this->entityManager = new ObjectManager(
            $this->pathManager,
            $entityManager = EntityManager::create($this->getConnection(), $config)
        );
        $this->entityManager->getEventManager()->addEventSubscriber(
            new CRSubscriber($this->pathManager, $metadataFactory, $entityManager)
        );

        return $this->entityManager;
    }
}
