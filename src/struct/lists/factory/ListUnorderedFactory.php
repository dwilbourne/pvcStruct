<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\lists\factory;

use pvc\struct\lists\ListUnordered;
use pvc\validator\base\ValidatorInterface;

/**
 * Class ListUnorderedFactory
 */
class ListUnorderedFactory implements ListUnorderedFactoryInterface
{

    /**
     * @function makeList
     * @return ListUnordered
     */
    public function makeList(): ListUnordered
    {
        return new ListUnordered();
    }
}
