<?php

return static function (mixed $stream, \Psr\Container\ContainerInterface $valueTransformers, \Symfony\Component\JsonEncoder\Decode\LazyInstantiator $instantiator, array $options): mixed {
    $providers['array<string,Symfony\Component\JsonEncoder\Tests\Fixtures\Model\ClassicDummy>'] = static function ($stream, $offset, $length) use ($options, $valueTransformers, $instantiator, &$providers) {
        $data = \Symfony\Component\JsonEncoder\Decode\Splitter::splitDict($stream, $offset, $length);
        $iterable = static function ($stream, $data) use ($options, $valueTransformers, $instantiator, &$providers) {
            foreach ($data as $k => $v) {
                yield $k => $providers['Symfony\Component\JsonEncoder\Tests\Fixtures\Model\ClassicDummy']($stream, $v[0], $v[1]);
            }
        };
        return \iterator_to_array($iterable($stream, $data));
    };
    $providers['Symfony\Component\JsonEncoder\Tests\Fixtures\Model\ClassicDummy'] = static function ($stream, $offset, $length) use ($options, $valueTransformers, $instantiator, &$providers) {
        $data = \Symfony\Component\JsonEncoder\Decode\Splitter::splitDict($stream, $offset, $length);
        return $instantiator->instantiate(\Symfony\Component\JsonEncoder\Tests\Fixtures\Model\ClassicDummy::class, static function ($object) use ($stream, $data, $options, $valueTransformers, $instantiator, &$providers) {
            foreach ($data as $k => $v) {
                match ($k) {
                    'id' => $object->id = \Symfony\Component\JsonEncoder\Decode\NativeDecoder::decodeStream($stream, $v[0], $v[1]),
                    'name' => $object->name = \Symfony\Component\JsonEncoder\Decode\NativeDecoder::decodeStream($stream, $v[0], $v[1]),
                    default => null,
                };
            }
        });
    };
    return $providers['array<string,Symfony\Component\JsonEncoder\Tests\Fixtures\Model\ClassicDummy>']($stream, 0, null);
};
