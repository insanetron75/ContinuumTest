<?php
namespace AppBundle\Command;

use Chadicus\Marvel\Api\Entities\Character;
use Chadicus\Marvel\Api\Entities\EntityInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Chadicus\Marvel\Api\Client;
use DateTime;

class MarvelReportCommand extends Command
{
    /** @var Client $client */
    private $client;
    private $publicApiKey = '1a5f90fbac2949acd8c9751e22d9f4c9';
    private $privateApiKey = '3684cae00d9a8bc6ecb0d6fd8f60e8a1897104c9';

    private $outputPath = __DIR__ . '/../../../bin/output';

    private $dataType;
    private $characterName;

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
        $this->client  = new Client($this->privateApiKey, $this->publicApiKey);
        $this->setCharacterName($input->getArgument('character'));
        $this->setDataType($input->getArgument('dataType'));

        $characters = $this->getCharacters('name');
        if (!$characters) {
            $characters = $this->getCharacters('nameStartsWith');
            $output->writeln('Multiple Character Match:');
            foreach ($characters as $thisCharacter) {
                $output->writeln($thisCharacter->name);
            }

            return;
        }

        if ($this->getDataType() === 'stories' || $this->getDataType() === 'series') {
            $stories = $this->getItemsManually($characters[0]);
            $this->writeStoriesCSV($stories['data']['results']);
        } else {
            $items = $this->getItems($characters[0]);
            $this->writeCSV($items);
        }

        $output->writeln([
            "Character: {$input->getArgument('character')}",
            "Data Type: {$input->getArgument('dataType')}",
            "Character Result: {$characters[0]->name}"
        ]);

    }

    /**
     * @param string $criteria
     *
     * @return array|EntityInterface[]
     */
    protected function getCharacters($criteria = 'name')
    {
        $dataWrapper = $this->client->search('characters', [$criteria => $this->getCharacterName()]);

        /** @var Character[] $characters */
        return $dataWrapper->getData()->getResults();
    }

    /**
     * @param Character $character
     *
     * @return EntityInterface[]
     */
    protected function getItems(Character $character)
    {
        $comicWrapper = $this->client->search($this->getDataType(), [
            'characters' => $character->id,
            'limit'      => 40
        ]);

        return $comicWrapper->getData()->getResults();
    }

    /**
     * @param EntityInterface[] $items
     */
    protected function writeCSV($items)
    {
        $rowArray   = [];
        $rowArray[] = $this->buildCSVHeaders();
        foreach ($items as $item) {
            /** @var DateTime $date */
            $date       = $this->getDate($item);
            $rowArray[] = [
                $this->getCharacterName(),
                $this->getDataType(),
                $item->title,
                $item->description,
                $date->format('d/m/Y')
            ];
        }
        $fileName = 'marvel_report_' . date('d_m_y') . "_{$this->getDataType()}.csv";
        $csvFile  = "{$this->outputPath}/$fileName";
        $handle   = fopen($csvFile, 'w');
        foreach ($rowArray as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    protected function buildCSVHeaders()
    {
        return [
            'Character',
            'Data Type',
            'Item Name',
            'Item Description',
            'Date Published'
        ];
    }

    protected function getDate($item)
    {
        if ($this->getDataType() === 'comics') {
            return $item->dates[0]->date;
        } elseif ($this->getDataType() === 'events') {
            return $item->start;
        }

        return null;
    }

    /**
     * Unfortunately stories and series don't work with this bundle so we have to do it manually...sigh
     *
     * @param Character $character
     *
     * @return array
     */
    protected function getItemsManually(Character $character)
    {
        $context         = stream_context_create(array('http' => array('header' => 'Accept: application/json')));
        $baseUrl         = "http://gateway.marvel.com/v1/public/";
        $query['apikey'] = $this->publicApiKey;
        $query['ts']     = time();
        $query['hash']   = md5("{$query['ts']}{$this->privateApiKey}{$this->publicApiKey}");
        $cacheKey        = urlencode($this->getDataType()) . '?characters=' . $character->id . '&limit=40&' . http_build_query($query);
        $url             = $baseUrl . $cacheKey;

        return json_decode(file_get_contents($url, false, $context), true);
    }

    /**
     * @param array  $items
     */
    protected function writeStoriesCSV(array $items)
    {
        $fileName = 'marvel_report_' . date('d_m_y') . "_{$this->getDataType()}.csv";
        $csvFile  = "{$this->outputPath}/$fileName";
        $handle   = fopen($csvFile, 'w');
        fputcsv($handle, $this->buildCSVHeaders());

        foreach ($items as $row) {
            $rowArray = [
                $this->getCharacterName(),
                'story',
                $row['title'],
                $row['description'],
                $row['modified'],
            ];
            fputcsv($handle, $rowArray);
        }
        fclose($handle);
    }

    /**
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType
     */
    public function setDataType(string $dataType)
    {
        $this->dataType = $dataType;
    }

    /**
     * @return string
     */
    public function getCharacterName()
    {
        return $this->characterName;
    }

    /**
     * @param string $characterName
     */
    public function setCharacterName(string $characterName)
    {
        $this->characterName = $characterName;
    }


}