<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version 1.0
 */

namespace pvc\struct\lists\key_validator;

use pvc\err\throwable\exception\pvc_exceptions\InvalidValueMsg;
use pvc\msg\Msg;
use pvc\msg\MsgRetrievalInterface;
use pvc\validator\base\ValidatorInterface;

/**
 * Class ValidatorIntegerNonNegative
 * @package pvc\struct\lists\key_validator
 */
class ValidatorIntegerNonNegative implements ValidatorInterface
{
    /**
     * @var Msg
     */
    protected Msg $errMsg;

    /**
     * validate
     * @param mixed $data
     * @return bool
     */
    public function validate($data): bool
    {
        if (is_integer($data) && $data >= 0) {
            return true;
        }
        $additionalText = 'key provided (%s) must be an integer greater than or equal to 0';
        $this->errMsg = new InvalidValueMsg('list key', $data, $additionalText);
        return false;
    }

    /**
     * getErrMsg
     * @return MsgRetrievalInterface|null
     */
    public function getErrMsg(): ?MsgRetrievalInterface
    {
        return $this->errMsg;
    }
}
