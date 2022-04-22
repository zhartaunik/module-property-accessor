<?php

declare(strict_types=1);

namespace PerfectCode\PropertyAccessor\Model;

use Closure;
use Generator;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;

/**
 * Class ObjectPropertyAccessor
 */
class ObjectPropertyAccessor implements PropertyAccessorInterface
{
    /**
     * {@inheritdoc}
     * @return void
     * @throws PropertyAccessorException
     */
    public function setValue(
        object|array &$objectOrArray,
        string|PropertyPathInterface $propertyPath,
        mixed $value
    ): void {
        if (is_array($objectOrArray) || $propertyPath instanceof PropertyPathInterface) {
            throw new PropertyAccessorException (__('This functionality does not support.'));
        }
        if ($this->propertyExists($objectOrArray, $propertyPath)) {
            $this->propertyAccess(
                $objectOrArray,
                $propertyPath,
                function ($propertyPath) use ($value) {
                    $this->{$propertyPath} = $value;
                }
            );
            return;
        }

        $objectOrArray->{$propertyPath} = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): mixed
    {
        if (is_array($objectOrArray) || $propertyPath instanceof PropertyPathInterface) {
            throw new PropertyAccessorException (__('This functionality does not support.'));
        }

        return $this->propertyAccess(
            $objectOrArray,
            $propertyPath,
            function ($property) {
                return $this->{$property};
            }
        );
    }

    /**
     * {@inheritdoc}
     * @throws PropertyAccessorException
     */
    public function isWritable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        if (is_array($objectOrArray) || $propertyPath instanceof PropertyPathInterface) {
            throw new PropertyAccessorException (__('This functionality does not support.'));
        }
        return $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * {@inheritdoc}
     * @throws PropertyAccessorException
     */
    public function isReadable(object|array $objectOrArray, string|PropertyPathInterface $propertyPath): bool
    {
        if (is_array($objectOrArray) || $propertyPath instanceof PropertyPathInterface) {
            throw new PropertyAccessorException (__('This functionality does not support.'));
        }
        return $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * @param object $object
     * @param string $property
     * @param callable $command
     * @return mixed
     */
    protected function propertyAccess(object $object, string $property, callable $command): mixed
    {
        $classes = $this->getClasses($object);

        foreach ($classes as $class) {
            if (!property_exists($class, $property)) {
                continue;
            }

            return Closure::bind(
                $command,
                $object,
                $class
            )(
                $property
            );
        }
    }

    /**
     * @param object $object
     * @param string $property
     * @return bool
     */
    protected function propertyExists(object $object, string $property): bool
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
     * @return Generator
     */
    protected function getClasses(object $object): Generator
    {
        yield get_class($object) => get_class($object);
        yield from class_parents($object);
    }
}
