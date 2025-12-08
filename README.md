# Tree Processor

This PHP class processes a Maven text-tree and outputs an HTML with a dynamic filter (JS).

## Quick start

You can use `run.php` for processing multiple files.
Just create `.data.php` that returns an array. Example:
```php
<?php
return array(
	"../dep-tree--2025-11-27T10.12.34 -- deps up.tex",
	"../dep-tree--2025-12-07T12.34.56 -- minor.tex",
);
```

## Dev Usage

1. **Include the TreeProcessor class**: Make sure to include the TreeProcessor class in your PHP script.

2. **Instantiate the TreeProcessor**: Create an instance of the TreeProcessor class by providing the package name as an optional argument. If no package name is provided, then nothing is collapsed.

3. **Call the processTree() method**: Invoke the processTree() method to execute the processing logic. This method reads the input file, removes lines related to the specified package, and saves the result to the output file.

## Example using TreeProcessor

```php
<?php
require_once 'TreeProcessor.php';

// Input file
$tree = "../dep-tree--2025-12-08T15.53.30 -- log4j up.tex";
// Output file
$out = preg_replace("dep-tree", "dep-short-tree", $tree);

// Instantiate the TreeProcessor with a custom package name
$processor = new TreeProcessor("pl.mol");

// Process the Maven text-tree file
$processor->processTree($tree, $out);

echo "Done. Output written to $out.";

```

## Example using from shell

Running from `cmd`:
```bash
c:\Programy\Serwerowate\PHPs\PHP83\php.exe run.php "..\dependency-tree--2024-02-07T13.17.28.tex"
```
