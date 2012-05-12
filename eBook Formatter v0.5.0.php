<?php

include 'E:\CO-OP\Summer 2005\Work files\ASCII.php';

$fileArray = file($argv[1]);

//echo '<pre>';
//print_r($fileArray);
//echo "</pre>\n";

//$ASCII_array = array("word");
//echo '<pre>';
//print_r(char_to_ASCII($fileArray[5], $ASCII_array));
//echo "</pre>\n";

reset($fileArray);
while(current($fileArray)) {
	if(preg_match("/^[\n\t\s{5,}]/", current($fileArray))) {
		$tags[] = key($fileArray) - 1;
	}
//	if(preg_match("/^[\n]/", current($fileArray))) {
//		$tags[] = key($fileArray);
//	}
	next($fileArray);
}

//print_r($tags);

reset($fileArray);
while(current($fileArray)) {
	if(!array_search(key($fileArray), $tags)) {
		$fileArray[key($fileArray)] = preg_replace("/[\n\r]+/", '', current($fileArray));
	} else {
		$fileArray[key($fileArray)] = current($fileArray) . "\n";
	}
	next($fileArray);
}

reset($fileArray);
while(current($fileArray)) {
	if(preg_match("/^\s*$/", current($fileArray))) {
		$fileArray[key($fileArray)] = preg_replace("/[\s\S]*/", '', current($fileArray));
	}
	next($fileArray);
}

echo '<pre>';
print_r($fileArray);
echo "</pre>\n";

$fileString = '';
foreach($fileArray as $v) {
	$fileString .= ' ' . $v;
}

$cwd = getcwd();

$fp1 = fopen("$cwd\output2.rtf", 'w');

fputs($fp1, $fileString);

$fileArray = file("$cwd\output2.rtf");

echo '<pre>';
print_r($fileArray);
echo "</pre>\n";

//echo '{\rtf1\ansi\ansicpg1252\deff0\deflang1033\deflangfe1033{\fonttbl{\f0\froman\fprq2\fcharset0 Times New Roman;}}
//{\*\generator Msftedit 5.41.15.1507;}\viewkind4\uc1\pard\qj\f0\fs24 ';

//echo '<pre>';
//print_r($fileString);
//echo "</pre>\n";

//echo '\par
//}
//';

?>
