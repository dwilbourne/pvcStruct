<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);


namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeIdAndParentIdCannotBeTheSameException;

/**
 * Class TreenodeAbstract
 * @template NodeType
 * @template NodeValueType
 * @implements TreenodeAbstractInterface<NodeType, NodeValueType>
 */
class TreenodeAbstract implements TreenodeAbstractInterface
{
    /**
     * unique id for this node
     * @var int
     */
    protected int $nodeid;

    /**
     * id of parent
     * @var int|null
     */
    protected ? int $parentid = -1;

    /**
     * reference to containing tree
     * @var int
     */
    protected int $treeid;

    /**
     * object responsible for validating values
     * @var ValidatorInterface|null
     */
    protected ?ValidatorInterface $valueValidator;

    /**
     * @var NodeValueType
     */
    protected $value;

    /**
     * Treenode constructor.
     * @param int $nodeid
     * @throws InvalidNodeIdException
     */
    public function __construct(int $nodeid)
    {
        $this->setNodeId($nodeid);
    }

    /**
     * all nodeids are integers greater than or equal to 0
     *
     * validateNodeId
     * @param int $nodeid
     * @return bool
     */
    private function validateNodeId(int $nodeid): bool
    {
        return 0 <= $nodeid;
    }

    /**
     * set the id of the node.
     *
     * @function setNodeId
     * @param int $nodeId
     * @throws InvalidNodeIdException
     */
    public function setNodeId(int $nodeId): void
    {
        /**
         * nodeId must be valid
         */
        if (!$this->validateNodeId($nodeId)) {
            throw new InvalidNodeIdException($nodeId);
        }

        /**
         * cannot be the same as parentid.  Use "===" because getParentId can return null which gets cast to zero if
         * doing a standard equality test
         */
        if ($this->getParentId() === $nodeId) {
            throw new NodeIdAndParentIdCannotBeTheSameException($nodeId);
        }
        $this->nodeid = $nodeId;
    }

    /**
     * @function getNodeId
     * @return int
     */
    public function getNodeId():  int
    {
        return $this->nodeid;
    }

    /**
     * @function getParentId
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentid ?? null;
    }

    /**
     * @function setParentId
     * @param int|null $parentId
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     */
    public function setParentId($parentId): void
    {
        /**
         * parentIds can be null, so only verify the nodeId is valid if it is not null
         */
        if (!is_null($parentId) && !$this->validateNodeId($parentId)) {
            throw new InvalidNodeIdException($parentId);
        }

        /**
         * nodeId and parentid cannot be the same.  Use strict "===" because getNodeId can return null which gets
         * cast to zero using standard equality test
         */
        if ($this->getNodeId() === $parentId) {
            throw new NodeIdAndParentIdCannotBeTheSameException($parentId);
        }
        $this->parentid = $parentId;
    }

    /**
     * isRoot encapsulates the logic for determining whether a node might be a root node.
     *
     * @function isRoot
     * @return bool
     */
    public function isRoot() : bool
    {
        return is_null($this->getParentId());
    }

    /**
     * @function setTreeId
     * @param int $treeId
     */
    public function setTreeId(int $treeId): void
    {
        if (!$this->validateNodeId($treeId)) {
            throw new InvalidTreeidException($treeId);
        }
        $this->treeid = $treeId;
    }

    /**
     * @function getTreeId
     * @return int
     */
    public function getTreeId(): ?int
    {
        return $this->treeid ?? null;
    }

    /**
     * @function getValueValidator
     * @return ValidatorInterface|null
     */
    public function getValueValidator(): ?ValidatorInterface
    {
        return $this->valueValidator ?? null;
    }

    /**
     * @function setValueValidator
     * @param ValidatorInterface $validator
     */
    public function setValueValidator(ValidatorInterface $validator): void
    {
        $this->valueValidator = $validator;
    }

    /**
     * @function getValue
     * @return NodeValueType|null
     */
    public function getValue()
    {
        return $this->value ?? null;
    }

    /**
     * @function setValue
     * @param NodeValueType $value
     * @throws InvalidNodeValueException
     */
    public function setValue($value): void
    {
        // validation of the value is optional
        if (isset($this->valueValidator) && !$this->valueValidator->validate($value)) {
            throw new InvalidNodeValueException($value);
        }
        $this->value = $value;
    }

    /**
     * @function hydrate
     * @param array<mixed> $nodeData
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @throws InvalidParentNodeException
     */
    public function hydrate(array $nodeData): void
    {
        /**
         * painful to get the static analyzer happy.  Have to typehint intermediate variables.
         */
        /** @var int $nodeid */
        $nodeid = $nodeData['nodeid'];
        $this->setNodeId($nodeid);

        /** @var int $parentid */
        $parentid = $nodeData['parentid'];
        $this->setParentId($parentid);

        /** @var int $treeid */
        $treeid = $nodeData['treeid'];
        $this->setTreeId($treeid);

        $this->setValue($nodeData['value']);
    }

    /**
     * @function dehydrate
     * @return array<mixed>
     */
    public function dehydrate(): array
    {
        return [
            'nodeId' => $this->getNodeId(),
            'parentid' => $this->getParentId(),
            'treeId' => $this->getTreeId(),
            'value' => $this->getValue()
        ];
    }
}
