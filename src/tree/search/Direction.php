<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvc\struct\tree\search;

enum Direction: int
{
    case MOVE_DOWN = -1;
    case DONT_MOVE = 0;
    case MOVE_UP = 1;
}
