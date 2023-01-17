<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types = 1);

namespace pvc\struct\lists\err;


use pvc\err\stock\LogicException;

/**
 * Class ListDeleteException
 */
class NonExistentKeyException extends LogicException {}