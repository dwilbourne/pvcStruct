<?php

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\NodeSearchableInterface;

/**
 * @extends SearchAbstract<NodeSearchableInterface>
 */
class SearchAncestors extends SearchAbstract
{
    public function next(): void
    {
        if ($this->current()?->getParent() == null || $this->atMaxLevels()) {
            $this->invalidate();
        } else {
            $this->setCurrent($this->current()->getParent());
            $this->setCurrentLevel(Direction::MOVE_UP->value);
        }
    }
}