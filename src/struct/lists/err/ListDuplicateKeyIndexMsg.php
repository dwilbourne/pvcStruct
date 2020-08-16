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
class ListDuplicateKeyIndexMsg extends ErrorExceptionMsg
{
    /**
     * ListDuplicateKeyIndexMsg constructor.
     * @param int|string $keyIndex
     */
    public function __construct($keyIndex)
    {
        $msgVars = [$keyIndex];
        $msgText = 'error:  duplicate key / index (value of each = %s).';
        parent::__construct($msgVars, $msgText);
    }
}
