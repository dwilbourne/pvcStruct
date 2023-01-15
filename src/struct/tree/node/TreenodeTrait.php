<?php

declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\tree\err\_ExceptionFactory;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeIdAndParentIdCannotBeTheSameException;

/**
 * Trait TreenodeTrait
 * @template NodeValueType
 */
trait TreenodeTrait
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
	 * @param int $nodeid
	 * @throws InvalidNodeIdException
	 */
	public function setNodeId(int $nodeid): void
	{
		/**
		 * nodeid must be valid
		 */
		if (!$this->validateNodeId($nodeid)) {
			throw _ExceptionFactory::createException(InvalidNodeIdException::class, [$nodeid]);
		}

		/**
		 * cannot be the same as parentid.  Use "===" because getParentId can return null which gets cast to zero if
		 * doing a standard equality test
		 */
		if ($this->getParentId() === $nodeid) {
			throw _ExceptionFactory::createException(NodeIdAndParentIdCannotBeTheSameException::class, [$nodeid]);
		}
		$this->nodeid = $nodeid;
	}

	/**
     * @function getNodeId
     * @return int|null
     */
    public function getNodeId(): ? int
    {
        return $this->nodeid ?? null;
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
         * parentIds can be null, so only verify the nodeid is valid if it is not null
         */
        if (!is_null($parentId) && !$this->validateNodeId($parentId)) {
            throw _ExceptionFactory::createException(InvalidNodeIdException::class, [$parentId]);
        }

	    /**
	     * nodeid and parentid cannot be the same.  Use strict "===" because getNodeId can return null which gets
	     * cast to zero using standard equality test
	     */
        if ($this->getNodeId() === $parentId) {
            throw _ExceptionFactory::createException(NodeIdAndParentIdCannotBeTheSameException::class, [$parentId]);
        }
        $this->parentid = $parentId;
    }

	/**
	 * isRoot encapsulates the logic for determining whether a node might be a root node.
	 *
	 * A root node has a null parentid., but we don't want to use the getter getParentId because the getter will return
	 * null even if the parent is not initialized.  This implementation forces you to explicitly set the parentid
	 * to null in order for the node to qualify as a root node.
	 *
	 * There might be a cleaner way to do it via reflection or a try / catch.  I spent a few minutes with reflection
	 * but the ReflectionProperty constructor complained about not having an object as the first argument ($this
	 * does not work - cuz this is a trait?).  In the end, I am punting.  PHP emits an error (level = WARNING)
	 * and returns false.
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
     * @param int $treeid
     */
    public function setTreeId(int $treeid): void
    {
	    if (!$this->validateNodeId($treeid)) {
		    throw _ExceptionFactory::createException(InvalidTreeidException::class, [$treeid]);
	    }
        $this->treeid = $treeid;
    }

    /**
     * @function getTreeId
     * @return int|null
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
     * @param mixed[] $row
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @throws InvalidParentNodeException
     */
    public function hydrate(array $row): void
    {
        $this->setNodeId($row['nodeid']);
        $this->setParentId($row['parentid']);
        $this->setTreeId($row['treeid']);
        $this->setValue($row['value']);
    }

    /**
     * @function dehydrate
     * @return mixed[]
     */
    public function dehydrate(): array
    {
        return [
            'nodeid' => $this->getNodeId(),
            'parentid' => $this->getParentId(),
            'treeid' => $this->getTreeId(),
            'value' => $this->getValue()
        ];
    }

	/**
	 * Compares two nodes for equality.  The strict parameter allows you to choose between comparing whether the two
	 * arguments are the same instance or different instances but have the same property values.  Because
	 * TreenodeOrdered has the hydrationIndex property, there is no chance that an unordered node and an ordered node
	 * could come up equal, even under a loose comparison.
	 *
	 * Because TreenodeOrdered extends Treenode, we can type the node parameter with TreenodeInterface (e.g. we do
	 * not need a union type).
	 *
	 * equals
	 * @param TreenodeInterface<NodeValueType>|null $node
	 * @param bool $strict
	 * @return bool
	 */
	public function equals(TreenodeInterface $node = null, bool $strict = false) : bool
	{
		return ($strict ? ($this === $node) : ($this == $node));
	}

}
