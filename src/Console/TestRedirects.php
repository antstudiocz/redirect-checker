<?php

namespace Ant\RedirectChecker\Console;

use Ant\RedirectChecker\Checker;
use Nette\Utils\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestRedirects extends \Symfony\Component\Console\Command\Command
{

	/** @var Checker @inject */
	public $checker;

	protected function configure()
	{
		$this->setName('app:redirect-checker:run');
		$this->setDescription('Checks list of redirects');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		try {
			list($result, $data) = $this->checker->run();
			foreach ($data as $resultRow) {
				$row = $this->style(Strings::firstUpper($resultRow[0]), $resultRow[0]);
				$output->writeln($row);
				$output->writeln("\tFrom: " . $resultRow[1]['from']);
				$output->writeln("\tTo: " . $resultRow[1]['to']);
				$output->writeln("\tHops count: " . $resultRow[1]['hopsCount']);
				if ($resultRow[0] !== Checker::PASSED || $resultRow[1]['hopsCount'] > 1) {
					$output->writeln("\tHops: " . implode(' -> ', $resultRow[1]['hops']));
				}
				$output->writeln('');
			}

			$output->writeln('============================');
			$output->writeln('Final result: ' . $this->style(Strings::firstUpper($result), $result));

			return 0; // zero return code means everything is ok
		} catch (\Exception $exc) {
			$output->writeln('<error>' . $exc->getMessage() . '</error>');
			return 1; // non-zero return code means error
		}
	}

	protected function style($text, $state)
	{
		switch ($state) {
			case Checker::FAILED:
				$out = "<error>{$text}</error>";
				break;
			case Checker::WARNING:
				$out = "<comment>{$text}</comment>";
				break;
			default:
				$out = "<info>{$text}</info>";
		}
		return $out;

	}

}
