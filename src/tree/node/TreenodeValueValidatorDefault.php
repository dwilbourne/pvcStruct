<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvc\struct\tree\node;

use pvc\interfaces\msg\MsgInterface;
use pvc\interfaces\validator\ValidatorInterface;

/**
 * Class TreenodeValueValidatorDefault
 */
class TreenodeValueValidatorDefault implements ValidatorInterface
{

    /**
     * getMsg
     * @return MsgInterface|null
     */
    public function getMsg(): ?MsgInterface
    {
        return null;
    }

    /**
     * validate
     * @param mixed $data
     * @return bool
     */
    public function validate(mixed $data): bool
    {
        return true;
    }
}
