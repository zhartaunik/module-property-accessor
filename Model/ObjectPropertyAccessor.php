<?php
declare(strict_types=1);

namespace PerfectCode\PropertyAccessor\Model;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Class ObjectPropertyAccessor
 */
class ObjectPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function setValue(&$object, $property, $value)
    {
        if ($this->propertyExists($object, $property)) {
            return $this->propertyAccess(
                $object,
                $property,
                function ($property) use ($value) {
                    $this->{$property} = $value;
                }
            );
        }

        $object->{$property} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($object, $property)
    {
        return $this->propertyAccess(
            $object,
            $property,
            function ($property) {
                return $this->{$property};
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($object, $property)
    {
        return $this->propertyExists($object, $property);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($object, $property)
    {
        return $this->propertyExists($object, $property);
    }

    /**
     * @param object $object
     * @param string $property
     * @param callable $command
     *
     * @return mixed
     */
    protected function propertyAccess($object, $property, callable $command)
    {
        $classes = $this->getClasses($object);

        foreach ($classes as $class) {
            if (!property_exists($class, $property)) {
                continue;
            }

            return \Closure::bind(
                $command,
                $object,
                $class
            )($property);
        }
    }

    /**
     * @param object $object
     * @param string $property
     *
     * @return bool
     */
    protected function propertyExists($object, $property)
    {
        $classes = $this->getClasses($object);

        foreach ($classes as $class) {
            if (!property_exists($class, $property)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param object $object
     *
     * @return \Generator
     */
    protected function getClasses($object)
    {
        yield get_class($object) => get_class($object);
        yield from class_parents($object);
    }
}
