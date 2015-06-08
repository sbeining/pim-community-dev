<?php

namespace Akeneo\Bundle\StorageUtilsBundle\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Configure the ORM mappings of the metadata classes.
 *
 * @author    Julien Janvier <jjanvier@gmail.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class is largely based on the Sylius project's class
 * Sylius/Bundle/ResourceBundle/EventListener/LoadORMMetadataSubscriber.php
 *
 * Original authors are Ivan Molchanov <ivan.molchanov@opensoftdev.ru> and Paweł Jędrzejewski <pjedrzejewski@sylius.pl>
 *
 * TODO: spec it
 */
class ConfigureOrmMappingsSubscriber implements EventSubscriber
{
    /** @var array */
    protected $concreteEntities;

    /**
     * Constructor
     *
     * @param array $concreteEntities
     */
    public function __construct(array $concreteEntities)
    {
        $this->concreteEntities = $concreteEntities;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();

        if ($metadata instanceof ClassMetadataInfo) {
            $configuration = $eventArgs->getEntityManager()->getConfiguration();
            $this->configureMappings($metadata, $configuration);
        }
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @param Configuration     $configuration
     */
    protected function configureMappings(ClassMetadataInfo $metadata, Configuration $configuration)
    {
        $metadata->isMappedSuperclass = $this->shouldMetadataBeMappedSuperClass($metadata);

        if (!$metadata->isMappedSuperclass) {
            $this->setAssociationMappings($metadata, $configuration);
        } else {
            $this->unsetAssociationMappings($metadata);
        }
    }

    /**
     * If the given mapping metadata is used as a concrete model, than
     * this mapping should not be declared as a mapped superclass.
     *
     * @param ClassMetadataInfo $metadata
     *
     * @return bool
     */
    protected function shouldMetadataBeMappedSuperClass(ClassMetadataInfo $metadata)
    {
        foreach ($this->concreteEntities as $class) {
            if ($class === $metadata->getName()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set the association mappings of a metadata.
     *
     * @param ClassMetadataInfo $metadata
     * @param Configuration     $configuration
     */
    protected function setAssociationMappings(ClassMetadataInfo $metadata, Configuration $configuration)
    {
        foreach (class_parents($metadata->getName()) as $parent) {
            $parentMetadata = new ClassMetadata(
                $parent,
                $configuration->getNamingStrategy()
            );
            if (in_array($parent, $configuration->getMetadataDriverImpl()->getAllClassNames())) {
                $configuration->getMetadataDriverImpl()->loadMetadataForClass($parent, $parentMetadata);
                if ($parentMetadata->isMappedSuperclass) {
                    foreach ($parentMetadata->getAssociationMappings() as $key => $value) {
                        if ($this->hasRelation($value['type'])) {
                            $metadata->associationMappings[$key] = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * Unset the association mappings of a metadata.
     *
     * @param ClassMetadataInfo $metadata
     */
    protected function unsetAssociationMappings(ClassMetadataInfo $metadata)
    {
        foreach ($metadata->getAssociationMappings() as $key => $value) {
            if ($this->hasRelation($value['type'])) {
                unset($metadata->associationMappings[$key]);
            }
        }
    }

    /**
     * @param $type
     *
     * @return bool
     */
    protected function hasRelation($type)
    {
        return in_array(
            $type,
            [
                ClassMetadataInfo::MANY_TO_MANY,
                ClassMetadataInfo::ONE_TO_MANY,
                ClassMetadataInfo::ONE_TO_ONE,
            ],
            true
        );
    }
}
