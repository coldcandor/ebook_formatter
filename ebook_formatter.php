<?php

$version = "1.5.5";
$copyright = "2005 Eric Shields";

/**  This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/* Problems and Ideas not written up in Tasks:
 */

// Output a blank line for readability
echo "\n";

// Initialize the variable used in preg_match so the parser will stop 
// complaining
$match = '';

// Initialize the output string to ensure nothing wierd happens
$fileString = '';

// Check that at least the input file is given
if($argc == 1) {
  exit("Usage:  php eBookFormatter.php -i <inputFile> [-o <outputFile>] " . 
       "[-f <firstFunction[,secondFunction[,thirdFunction[,...]]]>] [-v]\n");
} // End if statement

// Command line arguements:
// 
// -i <inputFile>   // Designate the input file or path
// [-o <outputFile>]  // Designate the output file or path
// [-f <firstFunction[,secondFunction[,thirdFunction[,...]]]>] // Function(s) to 
//          preform.  PREFORMED IN THE ORDER LISTED!
// [-v] // Do not append formatter ID at top of file
// [-h] // If 100 is specifies as one of the functions, this tells the program
//         to output the results in HTML format.  If 100 is not present, this 
//         flag is ignored.
//
$oFlag = FALSE;
$fFlags = array();
$vFlag = FALSE;
$hFlag = FALSE;
$ext = 'txt';

// Parse the command line arguements. This should ignore the first arguement,
// which is the program name.
for($i = 1; $i < $argc; $i++) {
  
  preg_match("/-(\w)/", $argv[$i], $match);
  
  switch($match[1]) {
    
    case 'i':
    
      if(!$fileString = file_get_contents($argv[$i + 1])) {
        exit("Fatal Error(eBookFormatter.php):  Could not open input file (" . 
             $argv[$i + 1] . ")!\n");
      } // End if statement
      
      $i++;
    
      break;
    case 'o':
    
      if(!$fp1 = fopen($argv[$i + 1], 'w')) {
        exit("Fatal Error(eBookFormatter.php):  Could not open output file (" . 
             $argv[$i + 1] . ")!\n");
      } // End if statement
      
      $oFlag = TRUE;
      
      $i++;
    
      break;
    case 'f':
    
      $formats = explode(',', $argv[$i + 1]);
      
      foreach($formats as $f) {
        $fFlags[$f] = TRUE;
      } // End foreach loop
      
      $i++;
    
      break;
    case 'v':
    
      $vFlag = TRUE;
    
      break;
    case 'h':
    
      $hFlag = TRUE;
      $ext = 'htm';
    
      break;
    default:
    
      fprintf(STDERR, "\nWarning!  Unrecognized switch (%s), ignoring.\n", 
            $argv[$i]);
      
      break;
  } // End switch statement
  
  unset($match); // Ensure that nothing is repeated or mixed up
  
} // End foreach loop

// Check if this run is to run the testSuite.  The -o switch must be enabled.
if($fFlags[100] && $oFlag) {
  
  testSuite($fp1, $hFlag);
  
} else if($fFlags[100] && !$oFlag) {
  
  fprintf(STDERR, "\nWarning!  TestSuite function selected with no output file!"
                  . "  Outputting to file %s\\\\testOutput.%s.\n\n", getcwd(), 
                  $ext);
                  
  if(!$fp1 = fopen("testOutput.$ext", 'w')) {
    exit("Fatal Error(eBookFormatter.php):  Could not open output file " .
         "(testOutput.$ext)!\n");
  } // End if statement
                  
  testSuite($fp1, $hFlag);

} // End if - else if statments

// Preform the functions selected, in the order listed on the command line
reset($fFlags);
foreach($fFlags as $f) {

  if($f) {
    
    switch(key($fFlags)) {
      case 1:
        $fileString = doParagraphs($fileString);
        break;
      case 2:
        $fileString = removeRTF($fileString);
        break;
      case 3:
        $fileString = fixEllipses($fileString);
        break;
      case 4:
        $fileString = fixSentenceSpacing($fileString);
        break;
      case 5:
        $fileString = splitConversation($fileString);
        break;
      case 6:
        $fileString = fixSingleQuotes($fileString);
        break;
      default:
      
        // Close output file and error out of program.
        if($oFlag) {
          fclose($fp1);
        } // End if statement
        exit("Fatal Error(eBookFormatter.php):  Invalid function selected (" .
             (key($fFlags)) . ")!\n");
        
        break;
        
    } // End switch statement
    
  } // End if statement
  
  next($fFlags);
  
} // End foreach loop

outputToFile($fp1, $fileString, $oFlag, $vFlag, $version, $copyright);

/////////// End main "method"
  
/* The heart of the program, this function will detect and seperate the 
 * paragraphs in the file.  A blank line will be inserted inbetween and the 
 * start of each will be indented.  This method will remove the newlines at the 
 * end of each line.
 */
function doParagraphs($ickyString) {
  
  $tags = array();
  $keep = FALSE;
  $match = '';
  
  // Create the line array
  $fileArray = preg_split("/[\n\r]+/", $ickyString);
  
  // Check if the file split into lines correctly
  foreach($fileArray as $v) {
    if(preg_match("/[\n\r]+/", $v)) {
      exit("Fatal Error(eBookFormatter.php):  Split did not preform" . 
      "correctly!\n");
    } // End if statement
  } // End foreach loop

  // Eliminate blank lines
  reset($fileArray);
  while(current($fileArray)) {
    
    // If it's not a tab, newline, carriage return, or space, keep the line
    if(preg_match("/[^\x09\x0A\x0D\x20]*/", current($fileArray), $match)) {
         $keep = TRUE;
    } // End if statement
      
    // Deal with the line accordingly
    if($keep == FALSE) {
      
      // Kill element
      unset($fileArray[key($fileArray)]);
      
    } else {
      
      // Keep element
      next($fileArray);
      
    } // End if statement
    
    $keep = FALSE;
    
  } // End while loop                           
  
  // Copy the array to a new one so the indexing is normal again
  foreach($fileArray as $v) {
    $reducedArray[] = $v;
  } // End foreach loop
  
  // Destroy the old array
  unset($fileArray);
  
  // Copy back to a new version of the origional array to preserve variable name
  foreach($reducedArray as $v) {
    $fileArray[] = $v;
  } // End foreach loop  
  
  // Free up the resources from the temp array
  unset($reducedArray);
  
  // Establish tags - essentially new paragraghs.
  reset($fileArray);
  while(current($fileArray)) {
    
    // Check for evidence of a new paragraph: A starting tab
    if(preg_match("/^\t+/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement
    
    // Check for additional new paragraph markings: Multiple starting spaces
    if(preg_match("/^[ ]{3,}/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement

    // Check for additional new paragraph markings:  Period, dash, or other 
    //          punctuation before a quote at end of line
    if(preg_match("/(\.|-|\x97|\!|\?)[ ]*(\x60|\'|\"|)[ \r]*$/", 
            current($fileArray))) {
      $tags[] = key($fileArray) + 1;
    } // End if statement

    // Check for additional new paragraph markings:  Quote at start of line
    if(preg_match("/^(\x60|\'|\")/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement
    
    // Check for additional new paragraph markings:  Numerated line
    if(preg_match("/^\d+\./", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement    

    next($fileArray);
    
  } // End while loop
  
  // Strip newlines from each line and form paragraphs as one line each (no 
  // newline characters), trim any whitespace from the start and end of each 
  // paragraph, and output the paragraph to a file.
  reset($fileArray);
  $currentString = '';
  while(current($fileArray)) {
    
    // Strip newlines
    if(!in_array(key($fileArray), $tags)) {
      
      // Trim whitespace and format this section of the line
      $currentString .= 
          preg_replace("/^\s*(.+?)\s*$/", "$1 ", current($fileArray));
      
    // Complete paragraph  
    } else {
      
      // Trim whitespace and format paragraph
      $currentString = preg_replace("/^\s*(.+?)\s*$/", "\t$1\n\n", 
                $currentString);
      
      // Append current string to output string
      $cleanString .= $currentString;
      
      $currentString = 
          preg_replace("/^\s*(.+?)\s*$/", "$1 ", current($fileArray));

    } // End if - else statements
    
    next($fileArray);
    
  } // End while loop
  
  // Trim whitespace and format paragraph
  $currentString = preg_replace("/^\s*(.)/", "\t$1", $currentString);
  $currentString = preg_replace("/(.)\s*$/", "$1", $currentString);

  $cleanString .= $currentString;
  
  return $cleanString;
    
} // End function type1()

/* Remove RTF style formatting.  This function preserrves line terminators.
 */
function removeRTF($rtfString) {
  
  $terminator = '';
  
  // The first line terminator should be consistant throughout the file, save
  // it to add back to each line later
  preg_match("/[\n\r]+/", $rtfString, $terminator);
  
  // Create the line array
  $fileArray = preg_split("/[\n\r]+/", $rtfString);
  
  // Check if the file split into lines correctly
  foreach($fileArray as $v) {
    if(preg_match("/[\n\r]+/", $v)) {
      exit("Fatal Error(eBookFormatter.php):  Split did not preform 
            correctly!\n");
    } // End if statement
  } // End foreach loop
  
  reset($fileArray);
  while(current($fileArray)) {
    
    // Get strings that the regular expression parser will accept
    $tab = preg_quote('\tab');
    $par = preg_quote('\par');
    
    // Remove \tab, \par, and any lines that start with \, }, or {
    $fileArray[key($fileArray)] = 
        preg_replace("/($par |$tab )/", '', current($fileArray));
    $fileArray[key($fileArray)] = 
        preg_replace("/[\{\}].*$/", '', current($fileArray));
    $fileArray[key($fileArray)] = 
        preg_replace("/^\\\\.*$/", '', current($fileArray));

    // Add current string to final one and preserve line terminators
    $ptString .= current($fileArray) . $terminator[0];
    
    next($fileArray);
    
  } // End while loop
  
  return $ptString;
  
} // End function removeRTF()

/* Correct the spacing and number of periods in ellipses.  All will be converted
 * to ...  This should affect nothing else.
 */
function fixEllipses($broken) {

  // Fix ellipses
  $fixed = preg_replace("/[ ]?\.[ ]?\.[ \.]{0,5}/", '...  ', $broken);
  
  return $fixed;
  
} // End function fixEllipses()

/* Fix the several related spacing errors, such as more than one space between
 * words or less than 2 spaces after a punctuation mark.
 */
function fixSentenceSpacing($broken) {
  
  $pieces = '';

  // Replace all instances of 2 or more spaces with 1
  $broken = preg_replace("/[ ]{2,}/", " ", $broken);
  
  $fileArray = preg_split("/([\.\!\?\:-][ ]?[\'\"]?[ \w\n\r]?)/", $broken, -1, 
            PREG_SPLIT_DELIM_CAPTURE);
  
  // Fix spacing after punctuation marks
  for($i = 0; $i < count($fileArray); $i++) {
    
    preg_match("/(\.|\!|\?|\:)( )?(\'|\")?([ \w\n\r]*)/", $fileArray[$i], 
              $pieces);
  
    if(preg_match("/[\n\r]+/",$pieces[4])) {
      
      // If there's a quote and a space occurs after it, assume the quote is 
      // part of the current sentence and insert 2 spaces after it
      $fileArray[$i] = preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?([ \w\n\r]*)/", 
                "$1$3$4", $fileArray[$i]);
      
    } else if(preg_match("/\w/",$pieces[4]) && 
              !preg_match("/ \w/",$pieces[4])) {
      
      // If there's a quote and a space occurs after it, assume the quote is 
      // part of the current sentence and insert 2 spaces after it
      $fileArray[$i] = preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?([ \w\n\r]*)/", 
                "$1$3  $4", $fileArray[$i]);
      
    } else if(preg_match("/ \w/",$pieces[4])) {
      
      // If there's a quote and a space occurs after it, assume the quote is 
      // part of the current sentence and insert 2 spaces after it
      $fileArray[$i] = preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?([ \w\n\r]*)/", 
                "$1  $3$4", $fileArray[$i]);

    } // End if - else - if statements      
    
  } // End for loop
  
  foreach($fileArray as $v) {
    
    $fixed .= $v;
    
  } // End foreach loop
    
  return $fixed;
  
} // End function fixSentenceSpacing()

/* Often a conversation will have multiple speakers within a single paragraph 
 * without stating a switch in whose speaking.  I view this as a bad thing and a 
 * formatting error.  This function will insert a newline between them.
 */
function splitConversation($broken) {

  // Break apart conversations that take place on one line (e.g. ...' '... or
  // ..." "...)
  $fixed = preg_replace("/(\'|\")[ ]+(\'|\")/", "$1\n$2", $broken);
  
  return $fixed;
  
} // End function splitConversation()

/* Some files will have single quotes where there should be doubles, this 
 * function will fix those places.  NOTE:  Occurances of quotes within quotes 
 * (to denote someone quoting someone else, for example) will be replaced with 
 * double quotes.
 */
function fixSingleQuotes($broken) {

  // Replace single quotes with doubles, except for cases like "don't"
  $broken = preg_replace("/([\x09\x20])\x27/", "$1\x22", $broken);
  $fixed = preg_replace("/\x27([\x20\x0A])/", "\x22$1", $broken);
  
  return $fixed;
  
} // End function fixSingleQuotes()

/* This function tests each individual feature of the program and outputs a 
 * summary either to an html document or a plain text document, based on the -h
 * option.  This function exits the code.
 */
function testSuite($fp, $hFlag) {
  
  fclose($fp);
  
  exit("TestSuite finished, exiting program.");
  
} // End function testSuite()

/* Output the final product, $fileString, to the file opened earlier or to 
 * Standard Out.
 */
function outputToFile($fp1, $fileString, $oFlag, $vFlag, $version, $copyright) {
  
// Append the version info to the start of the file
if(!$vFlag && !preg_match("/{Automatically formatted using eBookFormatter v/", 
          $fileString)) {
  $fileString = "{Automatically formatted using eBookFormatter v" . $version . 
            "\n" . " Copyright " . $copyright . "}\n\n" . $fileString;
} // End if statement

  if($oFlag) {
    fputs($fp1, $fileString);
    fclose($fp1);
  } else {
    echo $fileString;
  } // End if - else statement
  
} // End function outputToFile()

?>