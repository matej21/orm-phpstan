<?php declare(strict_types = 1);

namespace Nextras\OrmPhpStan\Reflection;


use Nextras\Orm\Entity\IEntity;
use PHPStan\Reflection\Annotations\AnnotationsPropertiesClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;


class EntityPropertyReflectionExtension implements PropertiesClassReflectionExtension
{
	/** @var AnnotationsPropertiesClassReflectionExtension */
	private $annotationsExtension;


	public function __construct(AnnotationsPropertiesClassReflectionExtension $annotationsExtension)
	{
		$this->annotationsExtension = $annotationsExtension;
	}


	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		$hasProperty = $this->annotationsExtension->hasProperty($classReflection, $propertyName);
		if (!$hasProperty) {
			return false;
		}

		$interfaces = array_map(function (ClassReflection $interface) {
			return $interface->getName();
		}, $classReflection->getInterfaces());

		$phpDoc = $classReflection->getNativeReflection()->getDocComment();
		$hasRelationship = preg_match('#\$' . $propertyName . '\s(?:[^\n]*)\{[1m]:1.+\}.*$#m', $phpDoc) === 1;
		return in_array(IEntity::class, $interfaces, true) && $hasRelationship;
	}


	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		return new EntityPropertyReflection($this->annotationsExtension->getProperty($classReflection, $propertyName));
	}
}
