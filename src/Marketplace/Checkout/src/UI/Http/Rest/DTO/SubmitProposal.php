<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Http\Rest\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class SubmitProposal
{
    /**
     * Declared starting time.
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\DateTime(format="Y-m-d\TH:i:sP")
     */
    public string $workedFrom;

    /**
     * Declared ending time.
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     * @Assert\DateTime(format="Y-m-d\TH:i:sP")
     */
    public string $workedUntil;

    /**
     * Declared break time.
     *
     * @Assert\Type(type="int")
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    public int $minutesBreak;

    /**
     * Declared Compensation.
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    public string $compensation;
}
