<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Serializer\Tests\Fixtures\Attributes;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: [
    'first' => AbstractDummyFirstChild::class,
    'second' => AbstractDummySecondChild::class,
    'third' => AbstractDummyThirdChild::class,
], defaultType: 'third')]
abstract class AbstractDummy
{
    public $foo;

    public function __construct($foo = null)
    {
        $this->foo = $foo;
    }
}
