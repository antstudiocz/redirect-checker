<?php

namespace Ant\RedirectChecker\DI;

use Adeira\CompilerExtension;
use Ant\RedirectChecker\Console\TestRedirects;

class RedirectCheckerExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$this->addConfig(__DIR__ . '/services.neon');

		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('command.testRedirects'))
			->setClass(TestRedirects::class)
			->addTag(\Kdyby\Console\DI\ConsoleExtension::TAG_COMMAND);
	}

}
