<?php
declare(strict_types=1);

namespace Symfony\Component\Console\Tests\Fixtures;

use Symfony\Component\Console\Attribute\AsCommand;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsCommandExtended extends AsCommand
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        array $aliases = [],
        bool $hidden = false,
        public ?string $help = null,
        public ?string $addedParam = null
    ) {
        parent::__construct($this->name, $this->description, $aliases, $hidden, $this->help);
    }
}
