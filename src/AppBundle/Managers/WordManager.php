<?php

namespace AppBundle\Managers;

class WordManager {
	protected $words = [];
	protected $wordCount = 0;

	function __construct()
	{
		$handle = fopen(__DIR__ . '/../../../app/Resources/words.txt', "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				$this->words[] = $line;
			}
			$this->wordCount = count($this->words);

			fclose($handle);
		} else {
			throw new \RuntimeException('Failed to open words file');
		}
	}

	public function getRandomWord() : string
	{
		return $this->words[rand(0, $this->wordCount - 1)];
	}
}