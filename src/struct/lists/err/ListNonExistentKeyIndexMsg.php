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
class ListNonExistentKeyIndexMsg extends ErrorExceptionMsg
{
    /**
     * ListNonExistentKeyIndexMsg constructor.
     * @param int|string $keyIndex
     */
    public function __construct($keyIndex)
    {
        $msgVars = [$keyIndex];
        $msgText = 'error:  non-existent key / index specified (value = %s).';
        parent::__construct($msgVars, $msgText);
    }
}
