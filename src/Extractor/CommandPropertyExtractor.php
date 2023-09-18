<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor;

use LogicException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\Attribute\Description;
use WebChemistry\ConsoleExtras\Attribute\Option;

final class CommandPropertyExtractor
{

	/**
	 * @return array{ CommandArgument[], CommandOption[] }
	 */
	public function extract(object $object): array
	{
		$reflectionProperties = (new ReflectionClass($object))
			->getProperties();

		return [
			$this->extractArguments($reflectionProperties),
			$this->extractOptions($reflectionProperties),
		];
	}

	/**
	 * @param ReflectionProperty[] $reflectionProperties
	 * @return CommandArgument[]
	 */
	private function extractArguments(array $reflectionProperties): array
	{
		$arguments = [];

		foreach ($reflectionProperties as $reflection) {
			$attribute = $this->getAttribute($reflection, Argument::class);

			if (!$attribute) {
				continue;
			}

			if ($reflection->isPrivate() || $reflection->isStatic()) {
				throw new LogicException(sprintf('Argument %s must be public or protected and non-static.', $reflection->getName()));
			}

			$description = $this->getAttribute($reflection, Description::class);
			$nullable = $reflection->getType()?->allowsNull() === true;
			$optional = $reflection->hasDefaultValue() || $nullable;
			$types = array_map(
				fn (ReflectionNamedType $type) => $type->getName(),
				$this->getTypes($reflection->getType()),
			);

			$arguments[] = new CommandArgument(
				$reflection->getName(),
				$reflection->getName(),
				!$optional,
				$description?->description ?? '',
				$reflection->isDefault() ? $reflection->getDefaultValue() : null,
				$types,
				$nullable,
			);
		}

		return $arguments;
	}

	/**
	 * @param ReflectionProperty[] $reflectionProperties
	 * @return CommandOption[]
	 */
	private function extractOptions(array $reflectionProperties): array
	{
		$options = [];

		foreach ($reflectionProperties as $reflection) {
			$attribute = $this->getAttribute($reflection, Option::class);

			if (!$attribute) {
				continue;
			}

			if ($reflection->isPrivate() || $reflection->isStatic()) {
				throw new LogicException(sprintf('Option %s must be public or protected and non-static.', $reflection->getName()));
			}

			$description = $this->getAttribute($reflection, Description::class);
			$nullable = $reflection->getType()?->allowsNull() === true;
			$types = array_map(
				fn (ReflectionNamedType $type) => $type->getName(),
				$this->getTypes($reflection->getType()),
			);
			$switch = $types === ['bool'];

			if ($switch) {
				$optional = true;

				if (!$attribute->negatable && $reflection->hasDefaultValue()) {
					throw new LogicException(sprintf('Option %s must not have default value.', $reflection->getName()));
				}

			} else {
				if (!$reflection->hasDefaultValue() && !$nullable && !in_array('bool', $types, true)) {
					throw new LogicException(sprintf('Option %s must be nullable or boolean.', $reflection->getName()));
				}

				$optional = $reflection->hasDefaultValue() || in_array('bool', $types, true);
			}

			$options[] = new CommandOption(
				$reflection->getName(),
				$reflection->getName(),
				$attribute->shortcut,
				$switch,
				!$optional,
				$attribute->negatable,
				$description?->description ?? '',
				$reflection->isDefault() ? $reflection->getDefaultValue() : null,
				$types,
				$nullable,
			);
		}

		return $options;
	}

	/**
	 * @template TAttribute of object
	 * @param ReflectionProperty $reflection
	 * @param class-string<TAttribute> $attributeName
	 * @return TAttribute|null
	 */
	private function getAttribute(ReflectionProperty $reflection, string $attributeName): ?object
	{
		$attrs = $reflection->getAttributes($attributeName);

		return $attrs ? $attrs[0]->newInstance() : null;
	}

	/**
	 * @return ReflectionNamedType[]
	 */
	private function getTypes(?ReflectionType $type): array
	{
		if ($type instanceof ReflectionIntersectionType) {
			throw new LogicException('Intersection type is not supported.');
		}

		if ($type instanceof ReflectionUnionType) {
			$types = [];

			foreach ($type->getTypes() as $type) {
				$types = [...$types, ...$this->getTypes($type)];
			}

			return $types;
		}

		if ($type === null) {
			return [];
		}

		if ($type instanceof ReflectionNamedType) {
			return [$type];
		}

		throw new LogicException(sprintf('Type %s is not supported.', $type::class));
	}

}
