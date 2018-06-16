<?php

namespace App\Command;

use App\Services\DataCollectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DataCollectCommand extends Command
{
    protected static $defaultName = 'data:collect';

    protected function configure()
    {
        $this->setDescription('for data collection');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        //start date
        $year = '2014';
        $month = '04';
        $day='16';

        $fileLocation = __DIR__;

        $address = 'nothing';

        $dataCollectManager = new DataCollectManager($year, $month, $day, $fileLocation, $address);
        $dataCollectManager->getData($io);

        $io->success('data collection finished');
    }
}
