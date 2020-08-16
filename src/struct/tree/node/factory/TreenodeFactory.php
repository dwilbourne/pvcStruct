<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\tree\node\factory;

use pvc\struct\tree\iface\factory\TreenodeFactoryInterface;
use pvc\struct\tree\iface\node\TreenodeInterface;
use pvc\struct\tree\node\Treenode;
use pvc\validator\base\ValidatorInterface;

/**
 * Class TreenodeFactory
 */
class TreenodeFactory implements TreenodeFactoryInterface
{
    /**
     * @var ValidatorInterface
     */
    protected ValidatorInterface $nodeidValidator;

    /**
     * @function setNodeIdValidator
     * @param ValidatorInterface $validator
     */
    public function setNodeIdValidator(ValidatorInterface $validator): void
    {
        $this->nodeidValidator = $validator;
    }

    /**
     * @function getNodeidValidator
     * @return ValidatorInterface
     */
    public function getNodeidValidator(): ValidatorInterface
    {
        return $this->nodeidValidator;
    }

    /**
     * @function makeTreenode
     * @return TreenodeInterface
     */
    public function makeTreenode(int $nodeid): TreenodeInterface
    {
        return new Treenode($nodeid);
    }
}
