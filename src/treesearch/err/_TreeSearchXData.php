<?php
/** @noinspection SpellCheckingInspection */

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @noinspection PhpCSValidationInspection
 */

declare(strict_types=1);

namespace pvc\struct\treesearch\err;

use pvc\err\XDataAbstract;

class _TreeSearchXData extends XDataAbstract
{

    public function getLocalXCodes(): array
    {
        return [
            SetMaxSearchLevelsException::class => 1003,
            StartNodeUnsetException::class => 1016,
        ];
    }

    public function getXMessageTemplates(): array
    {
        return [
            SetMaxSearchLevelsException::class => 'Max levels to search must be > 0, actual supplied = ${badLevels}.',
            StartNodeUnsetException::class => 'start node must be set before searching or resetting the search.',
        ];
    }
}
