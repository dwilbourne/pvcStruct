<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\payload;

use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\tree\err\InvalidValueException;

/**
 * Class PayloadTrait
 * @template ValueType
 */
trait PayloadTrait
{
    protected ValidatorInterface $validator;

    protected mixed $value;

    /**
     * SetValueValidator
     * @param ValidatorInterface $validator
     */
    public function setValueValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * getValue
     * @return ValueType|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * setValue
     * @param ValueType $value
     */
    public function setValue(mixed $value): void
    {
        if (!$this->GetValueValidator()->validate($value)) {
            throw new InvalidValueException();
        }
        $this->value = $value;
    }

    /**
     * GetValueValidator
     * @return ValidatorInterface
     */
    public function getValueValidator(): ValidatorInterface
    {
        return $this->validator;
    }
}
