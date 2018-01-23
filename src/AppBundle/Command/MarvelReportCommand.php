<?php
namespace AppBundle\Command;

use Chadicus\Marvel\Api\Entities\Character;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chadicus\Marvel\Api\Client;

class MarvelReportCommand extends Command
{
    private $publicApiKey = '1a5f90fbac2949acd8c9751e22d9f4c9';
    private $privateApiKey = '3684cae00d9a8bc6ecb0d6fd8f60e8a1897104c9';

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
        $client        = new Client($this->privateApiKey, $this->publicApiKey);
        $characterName = $input->getArgument('character');

        $dataWrapper = $client->search('characters', ['name' => $characterName]);

        /** @var Character[] $characters */
        $characters = $dataWrapper->getData()->getResults();

        if (!$characters) {
            $dataWrapper = $client->search('characters', ['nameStartsWith' => $characterName]);

            /** @var Character[] $characters */
            $characters = $dataWrapper->getData()->getResults();

            $output->writeln('Multiple Character Match:');
            foreach ($characters as $thisCharacter) {
                $output->writeln($thisCharacter->name);
            }

            return;
        }

        $output->writeln([
            "Character: {$input->getArgument('character')}",
            "Data Type: {$input->getArgument('dataType')}",
            "Character Result: {$characters[0]->name}"
        ]);

    }
}