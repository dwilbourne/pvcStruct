<?php

namespace pvc\struct\tree\node;

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

    public static function getNextNodeId() : int
    {
        if (!isset(self::$instance)) {
            self::$instance = new NodeIdFactory();
            self::$nextNodeId = 0;
        }
        return self::$instance::$nextNodeId++;
    }
}