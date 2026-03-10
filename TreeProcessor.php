<?php

class State {
	public const Start = 'START';
	public const InList = 'IN-LIST';
}

/**
 * Print error message to STDERR and exit with code 1.
 */
function dieError(string $txt): void {
	fwrite(STDERR, "$txt\n");
	exit(1);
}

/**
 * Maven text-tree processing.
 */
class TreeProcessor {
	private $package;

	public function __construct($package="pl.mol") {
		$this->package = $package; // TODO: collapse by default
	}

	/** Main process. */
	public function processTree($inputFile, $outputFile) {
		// Open input file for reading
		$input = fopen($inputFile, "r") or dieError("[ERROR] Unable to open tree file!");

		// Open output file for writing
		$output = fopen($outputFile, "w") or dieError("[ERROR] Unable to open output file!");
		$outSkip = fopen($outputFile.".skipped", "w") or dieError("[ERROR] Unable to open output file!");

		$prevDepth = 0;
		$state = State::Start;

		// Read the file line by line
		while (!feof($input)) {
			$line = fgets($input);

			// Skip "Downloading from ..." etc
			if (!$line || $line[0] !== '[') {
				fwrite($outSkip, $line);
				continue;
			}

			// Cleanup line
			$clean = rtrim(preg_replace('/^\[INFO\] ?/', '', trim($line)));
			if ($clean === '') continue;

			// State
			if ($state == State::Start && preg_match('/---+ ?dependency:/', $clean) === 1) {
				$state = State::InList;
				continue;
			}
			if (preg_match('/--------<(.+)>------/', $clean, $matches) === 1) {
				$header = trim($matches[1]);
				// echo "$header\n";
				if ($state == State::InList) {
					fwrite($output, str_repeat("</ul>\n", $prevDepth + 1));
					$prevDepth = 0;
				}
				fwrite($output, "<h2>".htmlspecialchars($header)."</h2>\n");
				fwrite($output, "<ul>\n");
				$state = State::Start;
				continue;
			}
			if ($state == State::InList && preg_match('/^-------------------------------+$/', $clean) === 1) {
				$state = State::Start;
				fwrite($output, "</ul>\n");
			}
			if ($state != State::InList) {
				fwrite($outSkip, $line);
				continue;
			}

			// Remove tree chars
			$name = preg_replace('/^(\|  +)*(\+|-|\\\\)-\s*/', '', ltrim($clean));
			/*
				Depth.

				(should probably do tests...)
				"+- abc" is 0 
				"\- abc" is 0
				"   +- abc" is 1
				"   \- abc" is 1
				"+- org" is 0
				"|  +- org" is 1
				"|  |  +- org" is 2
			 */
			$depth = round((strlen($clean) - strlen($name)) / 3);

			// Open/close based on depth
			if ($depth > $prevDepth) {
				fwrite($output, str_repeat("\t", $depth) . "<ul>\n");
			} elseif ($depth < $prevDepth) {
				fwrite($output, str_repeat("\t", $depth+1) . str_repeat("</ul>", $prevDepth - $depth) . "\n");
			}

			fwrite($output, str_repeat("\t", $depth+1) . "<li>" . htmlspecialchars($name) . "\n");
			$prevDepth = $depth;
		}

		// finalize
		// if ($state == State::InList) {
		// 	fwrite($output, "</ul>\n");
		// }
		fwrite($output, str_repeat("</ul>\n", $prevDepth));

		// Close files
		fclose($input);
		fclose($output);
		fclose($outSkip);
	}

	/** Assets to copy. */
	public static $assets = [
		'filters.css',
		'ReArray.js',
		'ViewFilter.js',
		'filter_init.js',
	];

	/** Prepare assets (this can be done once at any time). */
	public function prepareAssets($outDirBase) {
		$outDir = $outDirBase .'/assets';
		if (!is_dir($outDir)) {
			mkdir($outDir, 0777, true);
		}

		$assets = self::$assets;

		foreach ($assets as $asset) {
			if (!copy('./assets/'.$asset, $outDir . '/' . $asset)) {
				echo "Failed to copy $asset\n";
			}
		}
	}
	/** Append assets (needs to be done for each file). */
	public function appendFilter($outputFile) {
		$assets = self::$assets;

		$output = fopen($outputFile, "a+") or dieError("[ERROR] Unable to open output file!");
		foreach ($assets as $asset) {
			if (preg_match('/[.]js$/', $asset) === 1) {
				fwrite($output, "<script src='./assets/$asset'></script>\n");
			} else {
				fwrite($output, "<link rel='stylesheet' type='text/css' href='./assets/$asset'>\n");
			}
		}
		fclose($output);
	}
}
