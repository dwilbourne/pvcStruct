<?php

namespace pvcTests\struct\integration_tests\fixture\tree\nodeid_factory;

class NodeIdFactory
{
    /**
     * @var NodeIdFactory
     */
    protected static NodeIdFactory $instance;

    /**
     * @var non-negative-int
     */
    protected static int $nextNodeId;

    /**
     * @return non-negative-int
     */
    public static function getNextNodeId(): int
    {
        if (!isset(self::$instance)) {
            self::$instance = new NodeIdFactory();
            self::$nextNodeId = 0;
        }
        return self::$instance::$nextNodeId++;
    }
}