<?php
namespace Tests\AppBundle\Command;

use AppBundle\Command\MarvelReportCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MarvelReportCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new MarvelReportCommand());
        $command = $application->find('app:marvel-report');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'character' => 'spiderman',
            'dataType' => 'comics'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Character: spiderman', $output);
    }
}