<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\JsonEncoder\Tests\Mapping\Decode;

use PHPUnit\Framework\TestCase;
use Symfony\Component\JsonEncoder\Exception\InvalidArgumentException;
use Symfony\Component\JsonEncoder\Mapping\Decode\AttributePropertyMetadataLoader;
use Symfony\Component\JsonEncoder\Mapping\PropertyMetadata;
use Symfony\Component\JsonEncoder\Mapping\PropertyMetadataLoader;
use Symfony\Component\JsonEncoder\Tests\Fixtures\Model\DummyWithNameAttributes;
use Symfony\Component\JsonEncoder\Tests\Fixtures\Model\DummyWithValueTransformerAttributes;
use Symfony\Component\JsonEncoder\Tests\Fixtures\ValueTransformer\DivideStringAndCastToIntValueTransformer;
use Symfony\Component\JsonEncoder\Tests\Fixtures\ValueTransformer\StringToBooleanValueTransformer;
use Symfony\Component\JsonEncoder\Tests\ServiceContainer;
use Symfony\Component\JsonEncoder\ValueTransformer\ValueTransformerInterface;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

class AttributePropertyMetadataLoaderTest extends TestCase
{
    public function testRetrieveEncodedName()
    {
        $loader = new AttributePropertyMetadataLoader(new PropertyMetadataLoader(TypeResolver::create()), new ServiceContainer(), TypeResolver::create());

        $this->assertSame(['@id', 'name'], array_keys($loader->load(DummyWithNameAttributes::class)));
    }

    public function testRetrieveValueTransformer()
    {
        $loader = new AttributePropertyMetadataLoader(new PropertyMetadataLoader(TypeResolver::create()), new ServiceContainer([
            DivideStringAndCastToIntValueTransformer::class => new DivideStringAndCastToIntValueTransformer(),
            StringToBooleanValueTransformer::class => new StringToBooleanValueTransformer(),
        ]), TypeResolver::create());

        $this->assertEquals([
            'id' => new PropertyMetadata('id', Type::string(), [], [DivideStringAndCastToIntValueTransformer::class]),
            'active' => new PropertyMetadata('active', Type::string(), [], [StringToBooleanValueTransformer::class]),
            'name' => new PropertyMetadata('name', Type::string(), [], [\Closure::fromCallable('strtolower')]),
            'range' => new PropertyMetadata('range', Type::string(), [], [\Closure::fromCallable(DummyWithValueTransformerAttributes::concatRange(...))]),
        ], $loader->load(DummyWithValueTransformerAttributes::class));
    }

    public function testThrowWhenCannotRetrieveValueTransformer()
    {
        $loader = new AttributePropertyMetadataLoader(new PropertyMetadataLoader(TypeResolver::create()), new ServiceContainer(), TypeResolver::create());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('You have requested a non-existent value transformer service "%s". Did you implement "%s"?', DivideStringAndCastToIntValueTransformer::class, ValueTransformerInterface::class));

        $loader->load(DummyWithValueTransformerAttributes::class);
    }

    public function testThrowWhenInvaliValueTransformer()
    {
        $loader = new AttributePropertyMetadataLoader(new PropertyMetadataLoader(TypeResolver::create()), new ServiceContainer([
            DivideStringAndCastToIntValueTransformer::class => true,
            StringToBooleanValueTransformer::class => new StringToBooleanValueTransformer(),
        ]), TypeResolver::create());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('The "%s" value transformer service does not implement "%s".', DivideStringAndCastToIntValueTransformer::class, ValueTransformerInterface::class));

        $loader->load(DummyWithValueTransformerAttributes::class);
    }
}
