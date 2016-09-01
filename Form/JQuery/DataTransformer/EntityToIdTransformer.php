<?php
/**
 * User: ilya.vertakov
 * Date: 02.09.16
 * Time: 0:34
 */

namespace Genemu\Bundle\FormBundle\Form\JQuery\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class EntityToIdTransformer implements DataTransformerInterface
{

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var boolean
     */
    private $multiple;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, $entityClass, $multiple = false)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->multiple = $multiple;
    }

    /**
     * Transforms an object to a string (id).
     *
     * @param  Object|Object[]|null $entity
     * @return string
     */
    public function transform($entity)
    {
        if (!$this->multiple) {
            return null === $entity ? '' : $entity->getId();
        }

        return implode(',', array_map(function($object) { return $object->getId(); }, $entity->toArray()));
    }

    /**
     * Transforms a string (id) to an object.
     *
     * @param  string $id
     * @return Object|null
     * @throws TransformationFailedException if object is not found.
     */
    public function reverseTransform($id)
    {
        if (!$id && !$this->multiple) {
            return null;
        }

        $ids = explode(',', $id);
        $entities = $this->om->getRepository($this->entityClass)->findById($ids);

        if (!$this->multiple) {
            if (!$entities) {

                throw new TransformationFailedException(sprintf(
                    'An entity of class ' . $this->entityClass . ' with id "%s" does not exist!', $id
                ));
            }

            return reset($entities);
        }

        return new PersistentCollection($this->om, $this->om->getClassMetadata($this->entityClass), new ArrayCollection($entities));
    }

    /**
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param boolean $multiple
     * @return EntityToIdTransformer
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }
}