<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\Domain\Proposal;

use DateTimeImmutable;

abstract class Proposal
{
    private ProposalContent $content;

    protected string $proposedBy;

    private DateTimeImmutable $proposedAt;

    final protected function __construct(
        ProposalContent $content,
        string $proposedBy,
        DateTimeImmutable $proposedAt = null
    ) {
        $this->content = $content;
        $this->proposedBy = $proposedBy;
        $this->proposedAt = $proposedAt ?? new DateTimeImmutable();
    }

    final public static function fromValues(
        ProposalContent $content,
        string $proposedBy,
        DateTimeImmutable $proposedAt
    ): self {
        return new static($content, $proposedBy, $proposedAt);
    }

    public function proposedAt(): DateTimeImmutable
    {
        return $this->proposedAt;
    }

    public function content(): ProposalContent
    {
        return $this->content;
    }

    public function proposedBy(): string
    {
        return $this->proposedBy;
    }

    public function equalsTo(Proposal $other): bool
    {
        return
            $this->proposedBy === $other->proposedBy &&
            $this->content->equalsTo($other->content);
    }
}
