<?php declare(strict_types = 1);
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvc\struct\lists\factory;

use pvc\struct\lists\ListOrdered;

/**
 * Class ListOrderedFactory
 */
class ListOrderedFactory implements ListOrderedFactoryInterface
{
    public function makeList() : ListOrdered
    {
        return new ListOrdered();
    }
}
