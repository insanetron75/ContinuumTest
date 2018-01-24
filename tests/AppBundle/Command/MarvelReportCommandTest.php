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
            'character' => 'spider-man',
            'dataType' => 'comics'
        ]);

        $output = $commandTester->getDisplay();
        $this->assertContains('Character Result: Spider-Man', $output);

        $commandTester->execute([
            'command' => $command->getName(),
            'character' => 'spider-man',
            'dataType' => 'series'
        ]);
        $this->assertContains('Character Result: Spider-Man', $output);

        $commandTester->execute([
            'command' => $command->getName(),
            'character' => 'spider-man',
            'dataType' => 'events'
        ]);
        $this->assertContains('Character Result: Spider-Man', $output);

        $commandTester->execute([
            'command' => $command->getName(),
            'character' => 'spider-man',
            'dataType' => 'stories'
        ]);
        $this->assertContains('Character Result: Spider-Man', $output);
    }
}