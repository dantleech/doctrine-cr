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
use DTL\DoctrineCR\Tests\Support\Container;

class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $dbPath;

    protected function setUp()
    {
        $this->dbPath = $this->getTmpDir() . '/test.db';
        $this->initTmpDir();
        $this->initSchema();
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

    protected function getContainer()
    {
        if ($this->container) {
            return $this->container;    
        }

        $this->container = new Container([
            'dbal.connection' => [
                'driver' => 'pdo_sqlite',
                'path' => $this->dbPath,
            ],
            'orm.config_paths' => [
                __DIR__ . '/Resources/config/doctrine'
            ],
            'orm.proxy_dir' => $this->getTmpDir()
        ]);

        return $this->container;
    }

    protected function getDbalConnection()
    {
        return $this->getContainer()->offsetGet('dbal.connection');
    }

    protected function initSchema()
    {
        if (file_exists($this->dbPath)) {
            unlink($this->dbPath);
        };

        $schema = new Schema();
        $statements = $schema->toSql($this->getDbalConnection()->getDriver()->getDatabasePlatform());

        foreach ($statements as $statement) {
            $this->getDbalConnection()->exec($statement);
        }

        $tool = new SchemaTool($this->getEntityManager());
        $tool->createSchema([
            $this->getEntityManager()->getClassMetadata(Page::class)
        ]);
    }

    protected function getEntityManager()
    {
        return $this->getContainer()->offsetGet('dcr.object_manager');
    }
}
