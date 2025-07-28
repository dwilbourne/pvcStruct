<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvc\struct\treesearch;

use pvc\interfaces\struct\treesearch\VisitStatus;

/**
 * Class VisitationTrait
 */
trait VisitationTrait
{
    /**
     * @var VisitStatus
     */
    protected VisitStatus $visitStatus;

    /**
     * initializeVisitStatus
     */
    public function initializeVisitStatus(): void
    {
        $this->visitStatus = VisitStatus::NEVER_VISITED;
    }

    /**
     * getVisitStatus
     *
     * @return VisitStatus
     */
    public function getVisitStatus(): VisitStatus
    {
        return $this->visitStatus;
    }

    /**
     * setVisitStatus
     *
     * @param  VisitStatus  $status
     */
    public function setVisitStatus(VisitStatus $status): void
    {
        $this->visitStatus = $status;
    }
}
