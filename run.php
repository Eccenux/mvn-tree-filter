<?php
require_once 'TreeProcessor.php';

//
// Read from args[1] XOR `.data.php`
//

// Check if the file path is provided as a command line argument
if ($argc >= 2) {
	$trees = array($argv[1]);
}
// Read inputs from a file
if (empty($trees)) {
	$trees = include '.data.php';
}
// Configuration
if (file_exists('.config.php')) {
	$config = @include '.config.php';
} else {
	$config = [];
}
$config['_base'] ??= '../in';
$config['_out'] ??= '../out';

//
// Transform to HTML
//
@mkdir($config['_out'], 0777, true);
$processor = new TreeProcessor();
foreach ($trees as $fileName) {
	// Files
	$treePath = $config['_base'] . $fileName;
	$outPath = $config['_out'] . $fileName . ".html";

	// Process tree
	$processor->processTree($treePath, $outPath);
	$processor->appendFilter($outPath);

	echo "\nOutput written to $outPath";
}
