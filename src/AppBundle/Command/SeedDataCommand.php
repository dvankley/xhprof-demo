<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SeedDataCommand extends Command {
	protected function configure()
	{
		$this
			// the name of the command (the part after "bin/console")
			->setName('seed:data')

			// the short description shown while running "php bin/console list"
			->setDescription('Populates demo data')

			// the full command description shown when running the command with
			// the "--help" option
			->setHelp('RTFC')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ...
		$output->writeln('Thing');
	}
}
