<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\node\factory;

use pvc\struct\lists\factory\ListOrderedFactoryInterface;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\iface\node\TreenodeOrderedInterface;

/**
 * Class TreenodeOrderedFactory
 */
class TreenodeOrderedFactory
{
    /**
     * @var ListOrderedFactoryInterface
     */
    protected ListOrderedFactoryInterface $listFactory;

    /**
     * @function setListFactory
     * @param ListOrderedFactoryInterface $factory
     */
    public function setListFactory(ListOrderedFactoryInterface $factory): void
    {
        $this->listFactory = $factory;
    }

    /**
     * @function getListFactory
     * @return ListOrderedFactoryInterface
     */
    public function getListFactory(): ListOrderedFactoryInterface
    {
        return $this->listFactory;
    }

    /**
     * @function makeTreenode
     * @return TreenodeOrderedInterface
     */
    public function makeTreenode(int $nodeid): TreenodeOrderedInterface
    {
        $list = $this->listFactory->makeList();
        return new TreenodeOrdered($nodeid, $list);
    }
}
