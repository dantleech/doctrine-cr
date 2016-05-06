<?php

namespace DTL\DoctrineCR\Tests\Support;

use Pimple\Container as BaseContainer;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use DTL\DoctrineCR\ObjectManager;
use DTL\DoctrineCR\NodeManager\Dbal;
use DTL\DoctrineCR\Path\Storage\Dbal\Schema;
use Doctrine\ORM\Tools\SchemaTool;
use DTL\DoctrineCR\Tests\Functional\Resources\Entity\Article;
use DTL\DoctrineCR\Subscriber\DcrSubscriber;
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

class Container extends BaseContainer
{
    public function __construct(array $params)
    {
        parent::__construct();

        $params = array_merge([
            'dbal.connection' => [],
            'orm.config_paths' => [],
            'orm.proxy_dir' => null
        ], $params);

        $this->configureDbal($params);
        $this->configureOrm($params);
        $this->configureCr($params);
    }

    private function configureDbal(array $params)
    {
        $this['dbal.connection'] = function () use ($params) {
            return DriverManager::getConnection($params['dbal.connection']);
        };
    }

    private function configureOrm(array $params)
    {
        $this['orm.file_locator'] = function () use ($params) {
            return new DefaultFileLocator($params['orm.config_paths'], '.dcm.xml');
        };

        $this['orm.config'] = function (Container $container) use ($params) {
            $config = Setup::createConfiguration($params['orm.config_paths'], true);
            $config->setMetadataDriverImpl(new DoctrineXmlDriver(
                $container['orm.file_locator']
            ));
            $config->setProxyDir($params['orm.proxy_dir']);

            return $config;
        };

        $this['orm.entity_manager'] = function (Container $container) {
            return EntityManager::create(
                $container['dbal.connection'],
                $container['orm.config']
            );
        };
    }

    private function configureCr(array $params)
    {
        $this['dcr.metadata.factory'] = function (Container $container) use ($params) {
            return new MetadataFactory(
                new XmlDriver(
                    new DoctrineLocator($container['orm.file_locator'])
                )
            );
        };

        $this['dcr.path.storage.dbal'] = function (Container $container) {
            return new DbalStorage($container['dbal.connection']);
        };

        $this['dcr.path.manager'] = function (Container $container) {
            return new PathManager(
                $container['dcr.path.storage.dbal']
            );
        };

        $this['dcr.subscriber'] = function (Container $container) {
            return new DcrSubscriber($container['dcr.path.manager'], $container['dcr.metadata.factory'], $container['orm.entity_manager']);
        };

        $this['dcr.object_manager'] = function (Container $container) {
            $container['orm.entity_manager']->getEventManager()->addEventSubscriber(
                $container['dcr.subscriber']
            );

            return new ObjectManager(
                $container['dcr.path.manager'],
                $container['orm.entity_manager']
            );
        };
    }
}
