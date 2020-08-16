<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\lists\err;

use pvc\msg\ErrorExceptionMsg;

/**
 * Class DuplicateIndexMsg
 */
class ListValidateOffsetMsg extends ErrorExceptionMsg
{
    /**
     * ListValidateOffsetMsg constructor.
     * @param mixed $keyIndex
     */
    public function __construct($keyIndex)
    {
        $msgVars = [$keyIndex];
        $msgText = 'InvalidOffset specified (%s)';
        parent::__construct($msgVars, $msgText);
    }
}
