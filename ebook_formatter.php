<?php

//include 'E:\CO-OP\Summer 2005\Work files\ASCII.php';

$fileArray = file($argv[1]);

// Establish tags - essentially new paragraghs
reset($fileArray);
while(current($fileArray)) {
	if(preg_match("/^[\n\t\s{2,}]/", current($fileArray))) {
		$tags[] = key($fileArray);
	}
	next($fileArray);
}

// Eliminate blank lines
reset($fileArray);
while(current($fileArray)) {
	$fileArray[key($fileArray)] = preg_replace("/^\s*?$/", '', current($fileArray));
	next($fileArray);
} // End while loop                           

// Setup output file
$cwd = getcwd();
$fp1 = fopen("$cwd\output2.rtf", 'w');

// Strip newlines from each line and form paragraphs as one line each (no 
// newlines), trim any whitespace from the start and end of each paragraph, and 
// output the paragraph to a file.
reset($fileArray);
while(current($fileArray)) {
	
	$fileString = '';
	while(current($fileArray) && !array_search(key($fileArray), $tags)) {
		$fileString .= preg_replace("/[\n\r]+/", ' ', current($fileArray));
		next($fileArray);
	} // End while loop

	$fileString = preg_replace("/^\s*(.*)\s*$/", "$1\n\n", $fileString);
	
	fputs($fp1, $fileString);
	
	next($fileArray);
	
} // End while loop

fclose($fp1);

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

fclose($fp1);

?>







