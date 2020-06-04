<?php

declare(strict_types=1);

namespace Acme\Marketplace\Checkout\UI\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Acme\Marketplace\Checkout\Application\NoShow\MarkInactiveCheckoutsAsNoShow;

final class MarkInactiveCheckoutsAsNoShowCommand extends Command
{
    protected static $defaultName = 'acme:marketplace:checkout:mark-inactive-checkout-as-no-show';

    private MarkInactiveCheckoutsAsNoShow $asNoShow;

    public function __construct(MarkInactiveCheckoutsAsNoShow $asNoShow)
    {
        $this->asNoShow = $asNoShow;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Marks inactive Checkouts as NoShow');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->asNoShow->allInactiveCheckouts();

        return 0;
    }
}
