<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MarvelReportCommand extends Command
{
    protected function configure()
    {
        $this->setName('app:marvel-report');
        $this->setDescription('Generate Marvel Report based on character name and data type');
        $this->setHelp('This command generates a CSV report based on character name and data type' . PHP_EOL
        . 'Usage: marvel-report <name> <dataType>' . PHP_EOL
        . 'Data Types: comics, events, series or stories');

        $this->addArgument('character', InputArgument::REQUIRED, 'The Characters Name');
        $this->addArgument('dataType', InputArgument::REQUIRED, 'DataType: comics, events, series or stories');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $output->writeln([
           "Character: {$input->getArgument('character')}",
           "Data Type: {$input->getArgument('dataType')}"
       ]);
    }
}