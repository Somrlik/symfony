<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonEncoder\Mapping\Decode;

use Symfony\Component\JsonEncoder\Exception\InvalidArgumentException;
use Symfony\Component\JsonEncoder\Mapping\PropertyMetadataLoaderInterface;
use Symfony\Component\JsonEncoder\ValueTransformer\StringToDateTimeValueTransformer;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * Transforms string to DateTimeInterface for properties with DateTimeInterface type.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 *
 * @internal
 */
final class DateTimeTypePropertyMetadataLoader implements PropertyMetadataLoaderInterface
{
    public function __construct(
        private PropertyMetadataLoaderInterface $decorated,
    ) {
    }

    public function load(string $className, array $options = [], array $context = []): array
    {
        $result = $this->decorated->load($className, $options, $context);

        foreach ($result as &$metadata) {
            $type = $metadata->getType();

            if ($type instanceof ObjectType && is_a($type->getClassName(), \DateTimeInterface::class, true)) {
                if (\DateTime::class === $type->getClassName()) {
                    throw new InvalidArgumentException('The "DateTime" class is not supported. Use "DateTimeImmutable" instead.');
                }

                $metadata = $metadata
                    ->withType(StringToDateTimeValueTransformer::getJsonValueType())
                    ->withAdditionalToNativeValueTransformer('json_encoder.value_transformer.string_to_date_time');
            }
        }

        return $result;
    }
}
