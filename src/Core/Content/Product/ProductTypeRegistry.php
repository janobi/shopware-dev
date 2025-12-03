<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product;

use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\FieldEnumProviderInterface;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductTypeRegistry implements FieldEnumProviderInterface
{
    /**
     * @param array<string> $types
     */
    public function __construct(public array $types)
    {
    }

    public function addType(string $type): void
    {
        if ($this->hasType($type)) {
            return;
        }

        $this->types[] = $type;
    }

    /**
     * @return array<int, string>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function hasType(string $type): bool
    {
        return \in_array($type, $this->types, true);
    }

    public function isSupported(string $entity, string $fieldName): bool
    {
        return $entity === ProductDefinition::ENTITY_NAME && $fieldName === 'type';
    }

    /**
     * @inheritDoc
     */
    public function getEnumValues(): array
    {
        return $this->getTypes();
    }
}
