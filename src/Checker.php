<?php

namespace Ant\RedirectChecker;

use Nette\Neon\Neon;

class Checker
{

	const PASSED = 'ok';
	const WARNING = 'warning';
	const FAILED = 'failed';

	private $configFile;

	public function __construct($configFile = '')
	{
		$this->configFile = __DIR__ . '/../../../../' . $configFile;
	}

	public function run()
	{
		return $this->checkListFromNeon($this->configFile);
	}

	/**
	 * Will check list from associative array of rules.
	 * Index => origin url, Value => needed url
	 *
	 * @param [] $list
	 *
	 * @return array [OVERALL STATUS, [[SINGLE STATUS, [additional single info]]]]
	 */
	public function checkList($list)
	{
		$out = self::PASSED;
		$responses = [];
		foreach ($list as $originUrl => $finalUrl) {
			list($result, $response) = $this->check($originUrl, $finalUrl);
			switch ($result) {
				case self::WARNING:
					if ($out == self::PASSED) {
						$out = $result;
					}
					break;
				case self::FAILED:
					if ($out !== self::FAILED) {
						$out = $result;
					}
					break;
			}
			$responses[] = [$result, $response];
		}
		return [$out, $responses];
	}

	/**
	 * Checks single url
	 *
	 * @param string $originUrl Full url with http://
	 * @param string $finalUrl Full url with http://
	 * @param integer $maxFollowRedirects
	 *
	 * @return array [STATUS, [additional info for output]]
	 */
	public function check($originUrl, $finalUrl, $maxFollowRedirects = 10)
	{
		$out = self::FAILED;
		$client = \EasyRequest::create($originUrl, 'GET', ['follow_redirects' => $maxFollowRedirects]);
		$client->send();
		$outList = $list = $client->getRedirectedUrls();

		if (empty($list)) {
			if ($originUrl == $finalUrl) {
				$out = self::PASSED;
			}
		} elseif (in_array($finalUrl, $list)) {
			if (array_pop($list) == $finalUrl) {
				$out = self::PASSED;
			} else {
				$out = self::WARNING;
			}
		}

		array_shift($outList);
		return [$out, ['from' => $originUrl, 'to' => $finalUrl, 'hops' => $outList, 'hopsCount' => count($outList)]];
	}

	/**
	 * Will check list of rules from a neon file.
	 *
	 * @param string $filefilename
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function checkListFromNeon($filefilename)
	{
		if (!file_exists($filefilename)) {
			throw new \Exception("File '$filefilename' not found.");
		}
		$configuration = Neon::decode(file_get_contents($filefilename));
		if (!isset($configuration['redirects']) || empty($configuration['redirects'])) {
			throw new \Exception("Section 'redirects' not found or empty in configuration file: '{$filefilename}'");
		}
		return $this->checkList($configuration['redirects']);
	}

}
