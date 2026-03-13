<?php
/**
 * Created by PhpStorm.
 * User: benni
 * Date: 25.12.16
 * Time: 17:39
 */

namespace ProcessWire\Migrations\Command;


use ProcessWire\Migrations\Colors\CliStyles;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends Command
{
	use MigrationsModule;
	use MigrationsTable;

	protected function configure (): void
	{
		$this
			->setName('rollback')
			->setDescription('Rollback')
			->setHelp("Rollback all migrations starting from the latest migrated one to the newest.")
			->addArgument('what', InputArgument::OPTIONAL, 'What to rollback?');
		;
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute (InputInterface $input, OutputInterface $output): int
	{
		$io = new CliStyles($input, $output);
		$io->header();
		$files = $this->migrations->selectRollbackMigrations($input->getArgument('what'));

		if(!$files->count) {
			$io->writeln('Nothing to rollback.');
			return 0;
		}

		$io->writeln(sprintf('%d files to rollback. Starting…', $files->count));

		foreach ($files as $file) {
			$this->renderTable($io, $files);

			$this->migrations->rollback($files->find("filename=$file->filename"));

			$io->clear($files->count + 5);
		}

		$this->renderTable($io, $files);

		return 0;
	}
}