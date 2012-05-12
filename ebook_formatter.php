<?php

//include 'E:\CO-OP\Summer 2005\Work files\ASCII.php';

$fileArray = file($argv[1]);

$pre_string = '{\rtf1\ansi\ansicpg1252\deff0\deflang1033\deflangfe1033{\fonttbl{\f0\froman\fprq2\fcharset0 Times New Roman;}}
{\*\generator Msftedit 5.41.15.1507;}\viewkind4\uc1\pard\qj\f0\fs24 ';

$post_string = "\n}}";

//echo '<pre>';
//print_r($fileArray);
//echo "</pre>\n";

//$ASCII_array = array("word");
//echo '<pre>';
//print_r(char_to_ASCII($fileArray[5], $ASCII_array));
//echo "</pre>\n";

//$text1 = <<<TXT1
//\par
//TXT1;
//$text2 = <<<TXT2
//\tab
//TXT2;
//$text3 = <<<TXT3
//\'92
//TXT3;
//$text4 = <<<TXT4
//\'93
//TXT4;
//$text5 = <<<TXT5
//\'94
//TXT5;

// Remove rtf formatting
//reset($fileArray);
//while(current($fileArray)) {
//	preg_replace("/$text1/", '', current($fileArray));
//	preg_replace($text2, '', current($fileArray));
//	preg_replace($text3, '"', current($fileArray));
//	preg_replace($text4, '"', current($fileArray));
//	preg_replace($text5, "\'", current($fileArray));
//	next($fileArray);
//} // End while loop
//reset($fileArray);
//while(current($fileArray)) {
//	preg_replace("/^[\\\{\}].*/", '', current($fileArray));
//	next($fileArray);
//} // End while loop

//print_r($fileArray);

//echo "1\n";

// Establish tags - essentially new paragraghs
reset($fileArray);
while(current($fileArray)) {
	if(preg_match("/^[\n\t\s{2,}]/", current($fileArray))) {
		$tags[] = key($fileArray);
	}
//	if(preg_match("/^[\n]/", current($fileArray))) {
//		$tags[] = key($fileArray);
//	}
	next($fileArray);
}

//echo "2\n";

// Eliminate blank lines
reset($fileArray);
while(current($fileArray)) {
	$fileArray[key($fileArray)] = preg_replace("/^\s*?$/", '', current($fileArray));
	next($fileArray);
} // End while loop                           

//echo "3\n";

// Setup output file
$cwd = getcwd();
$fp1 = fopen("$cwd\output2.rtf", 'w');

//fputs($fp1, $pre_string);

//echo "4\n";

// Strip newlines from each line and form paragraphs as one line each (no 
// newlines), trim any whitespace from the start and end of each paragraph, and 
// output the paragraph to a file.
reset($fileArray);
while(current($fileArray)) {
	
	$fileString = '';
	while(current($fileArray) && !array_search(key($fileArray), $tags)) {
		$fileString .= preg_replace("/[\n\r]+/", ' ', current($fileArray));
		//echo "4.25\n";
		next($fileArray);
	} // End while loop
	//$fileString .= current($fileArray) . " \n";
	
	//echo "4.5\n";

	$fileString = preg_replace("/^\s*(.*)\s*$/", "$1\n\n", $fileString);
	
	//echo $fileString . "\n";
	
	//fputs($fp1, "\n" . '\par ' . $fileString);
	fputs($fp1, $fileString);
	
	//foreach($tags as $v) {
	//	$fileArray[$v] = '@@@@@' . $fileArray[$v];
	//} // End foreach loop

	//if(!preg_match("/^[\t\s{2,}]/", next($fileArray))) {
	//	$fileArray[key($fileArray)] = "" . current($fileArray);
	//} // End if statement
	
	next($fileArray);
	
} // End while loop

//echo "5\n";

//fputs($fp1, $post_string);

//reset($fileArray);
//while(current($fileArray)) {
//
//	$fileString = '';
//	while(current($fileArray) && !array_search(key($fileArray), $tags)) {
//		if(preg_match("/^\s*$/", current($fileArray))) {
//			$fileArray[key($fileArray)] .= preg_replace("/[\s\S]*/", '', current($fileArray));
//		}
//		next($fileArray);
//	} // End while loop
//	
//} // End while loop

$fileArray = file("$cwd\output2.rtf");

//echo '<pre>';
//print_r($fileArray);
//echo "</pre>\n";

?>







