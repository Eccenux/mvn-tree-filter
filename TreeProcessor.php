<?php

class State {
	public const Start = 'START';
	public const InList = 'IN-LIST';
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
		$input = fopen($inputFile, "r") or die("Unable to open tree file!");

		// Open output file for writing
		$output = fopen($outputFile, "w") or die("Unable to open output file!");
		$outSkip = fopen($outputFile.".skipped", "w") or die("Unable to open output file!");

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
				echo "$header\n";
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
				fwrite($output, "<ul>\n");
			} elseif ($depth < $prevDepth) {
				fwrite($output, str_repeat("</ul>\n", $prevDepth - $depth));
			}

			fwrite($output, "<li>" . htmlspecialchars($name) . "</li>\n");
			$prevDepth = $depth;
		}

		// finalize
		// if ($state == State::InList) {
		// 	fwrite($output, "</ul>\n");
		// }
		fwrite($output, str_repeat("</ul>\n", $prevDepth + 1));

		// Close files
		fclose($input);
		fclose($output);
		fclose($outSkip);
	}

	/** Main process. */
	public function appendFilter($outputFile) {
		// Open output file for writing
		$output = fopen($outputFile, "a+") or die("Unable to open output file!");
		fwrite($output, "<script>\n");
		fwrite($output, file_get_contents('./assets/ReArray.js'));
		fwrite($output, "\n</script>\n");
		fwrite($output, "<script>\n");
		fwrite($output, file_get_contents('./assets/ViewFilter.js'));
		fwrite($output, "\n</script>\n");
		fwrite($output, "<script>\n");
		fwrite($output, file_get_contents('./assets/filter_init.js'));
		fwrite($output, "\n</script>\n");
	}
}
