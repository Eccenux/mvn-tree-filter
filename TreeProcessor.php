<?php
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
		$input = fopen($inputFile, "r") or die("Unable to open tree file!");

		// Open output file for writing
		$output = fopen($outputFile, "w") or die("Unable to open output file!");
		$outSkip = fopen($outputFile.".skipped", "w") or die("Unable to open output file!");

		$prevDepth = 0;
		fwrite($output, "<ul>\n");

		// Read the file line by line
		while (!feof($input)) {
			$line = fgets($input);

			// Skip "Downloading from ..." etc
			if (!$line || $line[0] !== '[') {
				fwrite($outSkip, $line);
				continue;
			}

			// Cleanup line
			$clean = preg_replace('/^\[INFO\]\s*/', '', trim($line));
			if ($clean === '') continue;

			// Count depth
			preg_match_all('/(\|  )/', $clean, $matches);
			$depth = count($matches[0]);

			// Remove tree chars
			$name = preg_replace('/^(\|  )*(\+|-|\\\\)-\s*/', '', $clean);

			// Open/close based on depth
			if ($depth > $prevDepth) {
				fwrite($output, "<ul>\n");
			} elseif ($depth < $prevDepth) {
				fwrite($output, str_repeat("</ul>\n", $prevDepth - $depth));
			}

			fwrite($output, "<li>" . htmlspecialchars($name) . "</li>\n");
			$prevDepth = $depth;
		}

		// finalize
		fwrite($output, str_repeat("</ul>\n", $prevDepth + 1));

		// Close files
		fclose($input);
		fclose($output);
		fclose($outSkip);
	}

	/** Transform and save. */
	private static function parseLine($outputFile, $line) {
	}

	/** Transform and save. */
	private static function saveLine($outputFile, $line) {
		fwrite($outputFile, $line);
	}
}
