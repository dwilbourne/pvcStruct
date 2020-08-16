<?php declare(strict_types = 1);

namespace pvc\struct\tree\node;

use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\node\err\InvalidNodeIdException;
use pvc\struct\tree\node\err\InvalidNodeValueException;
use pvc\validator\base\ValidatorInterface;

trait TreenodeTrait
{

    /**
     * unique id for this node, immutable
     * @var int
     */
    protected $nodeid;

    /**
     * id of parent
     * @var int|null
     */
    protected $parentid;

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
     * @var mixed
     */
    protected $value;

    private function validateNodeId(int $nodeid) : bool
    {
        return 0 <= $nodeid;
    }

    /**
     * @function getNodeId
     * @return int
     */
    public function getNodeId() : int
    {
        return $this->nodeid;
    }

    /**
     * @function setNodeId
     * @param int $nodeid
     * @throws InvalidNodeIdException
     */
    protected function setNodeId(int $nodeid): void
    {
        if (!$this->validateNodeId($nodeid)) {
            throw new InvalidNodeIdException($nodeid);
        }
        $this->nodeid = $nodeid;
    }

    /**
     * @function getParentId
     * @return int|null
     */
    public function getParentId() : ? int
    {
        return $this->parentid ?? null;
    }

    /**
     * @function setParentId
     * @param int|null $parentId
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     */
    public function setParentId($parentId) : void
    {
        if (!is_null($parentId) && !$this->validateNodeId($parentId)) {
            throw new InvalidNodeIdException($parentId);
        }
        if ($this->nodeid === $parentId) {
            throw new InvalidParentNodeException($parentId);
        }
        $this->parentid = $parentId;
    }

    /**
     * @function setTreeId
     * @param int $treeid
     */
    public function setTreeId(int $treeid): void
    {
        $this->treeid = $treeid;
    }

    /**
     * @function getTreeId
     * @return int|null
     */
    public function getTreeId() : ?int
    {
        return $this->treeid ?? null;
    }

    /**
     * @function getValueValidator
     * @return ValidatorInterface|null
     */
    public function getValueValidator(): ?ValidatorInterface
    {
        return $this->valueValidator;
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
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @function setValue
     * @param mixed $value
     * @throws InvalidNodeValueException
     */
    public function setValue($value): void
    {
        if (isset($this->valueValidator) && !$this->valueValidator->validate($value)) {
            throw new InvalidNodeValueException($value);
        }
        $this->value = $value;
    }

    /**
     * @function hydrate
     * @param array $row
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @throws InvalidParentNodeException
     */
    public function hydrate(array $row): void
    {
        // nodeid is set upon construction
        // $this->setNodeId($row['nodeid']);
        $this->setParentId($row['parentid']);
        $this->setTreeId($row['treeid']);
        $this->setValue($row['value']);
    }

    /**
     * @function dehydrate
     * @return array
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
}
