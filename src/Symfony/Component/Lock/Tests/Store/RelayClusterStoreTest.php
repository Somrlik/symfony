<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Store;

use Relay\Cluster as RelayCluster;
use Symfony\Component\Lock\Tests\Store\AbstractRedisStoreTestCase;

/**
 * @requires extension relay
 *
 * @group integration
 */
class RelayClusterStoreTest extends AbstractRedisStoreTestCase
{
    protected function setUp(): void
    {
        $relayCluster = $this->getRedisConnection();

        foreach ($relayCluster->_masters() as $hostAndPort) {
            $relayCluster->flushdb($hostAndPort);
        }
    }

    public static function setUpBeforeClass(): void
    {
        if (!class_exists(RelayCluster::class)) {
            self::markTestSkipped('The Relay\Cluster class is required.');
        }

        if (false === getenv('REDIS_CLUSTER_HOSTS')) {
            self::markTestSkipped('REDIS_CLUSTER_HOSTS env var is not defined.');
        }
    }

    protected function getRedisConnection(): RelayCluster
    {
        return new RelayCluster('', explode(' ', getenv('REDIS_CLUSTER_HOSTS')));
    }
}
