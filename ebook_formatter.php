<?php

//include 'E:\CO-OP\Summer 2005\Work files\ASCII.php';

//print_r($argv);

$fileArray = file($argv[1]);
$cwd = getcwd();
$fp1 = fopen("$cwd\\$argv[2]", 'w');

// Remove whitespace from each line, place a starting tab on each paragraph, and
// enter a newline between paragraphs.
reset($fileArray);
while(current($fileArray)) {
  $fileArray[key($fileArray)] = preg_replace("/^\s*/", "\t", current($fileArray));
  $fileArray[key($fileArray)] = preg_replace("/\s*$/D", "\n", current($fileArray));
  fputs($fp1, current($fileArray));
	next($fileArray);
} // End while loop

?>