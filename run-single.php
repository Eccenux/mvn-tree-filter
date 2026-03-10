<?php
require_once 'TreeProcessor.php';

/**
 * Parse named CLI parameters.
 *
 * Supported:
 *   -dir <directory> (default ./)
 *   -in  <input file>
 *   -out <output file>
 */
$args = $argv;
array_shift($args);

$params = [
	'dir' => './',
	'in' => '',
	'out' => '',
];

for ($i = 0; $i < count($args); $i++) {
	switch ($args[$i]) {
		case '-dir':
			$params['dir'] = $args[++$i] ?? './';
			break;

		case '-in':
			$params['in'] = $args[++$i] ?? '';
			break;

		case '-out':
			$params['out'] = $args[++$i] ?? '';
			break;
	}
}

if (empty($params['in']) || empty($params['out'])) {
	fwrite(STDERR, "[ERROR] Usage: php run-single.php -in inputFile.tex -out outputFile.html [-dir directory]\n");
	exit(1);
}

//
// Configuration
//
$config = [];
$config['_base'] = $params['dir'];
$config['_out'] = $params['dir'];

//
// Transform to HTML
//
$processor = new TreeProcessor();
$processor->prepareAssets($config['_out']);

// Files
$treePath = rtrim($config['_base'], '/').'/'.$params['in'];
$outPath = rtrim($config['_out'], '/').'/'.$params['out'];

// Process tree
$processor->processTree($treePath, $outPath);
$processor->appendFilter($outPath);

echo "\nOutput written to $outPath\n";