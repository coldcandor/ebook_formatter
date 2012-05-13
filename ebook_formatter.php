<?php

include 'ASCII.php';

/* IDEA: Add a GUI
 * PROBLEM: Worst case paragraph scenario
 * TODO: Add ASCII
 * TODO: Add RTF formatting removal
 * PROBLEM: Formatted quotes (like poems, songs, letter readings, T.O.C., etc)
 * TODO: Fix things like bad ellipses
 * IDEA: Detect mismatched quotes
 * IDEA: Detect seperators (like a line of dashes, ****, or multiple blank lines)
 */

// Output a blank line for readability
echo "\n";

// Input error checking and file setup.
if(count($argv) != 4 && $argv[3] != 4) {
  exit("Usage:  php eBookFormatter.php <Input File> <Output File> <function>\n" . 
       "If function 4 is selected, enter ASCII arguements as per ASCII.php as well.\n\n");
} else if(!$fileArray = file($argv[1])) {
  exit("Fatal Error:  Could not open input file ($argv[1])!\n\n");
} else if(!$fp1 = fopen($argv[2], 'w')) {
  exit("Fatal Error:  Could not open output file ($argv[2])!\n\n");
} // End if - else if statements

switch($argv[3]) {
  case 1:
    type1();
    break;
  case 2:
    type2();
    break;
  case 3:
    removeRTF();
    break;
  case 4:
    ASCII();
    break;
  default:
  
    // Close output file and error out of program.
    fclose($fp1);  
    exit("Error:  Function select value must be in the range of 1 to 3\n\n");
    
    break;
    
} // End switch statement
  
function type1() {
  
  global $fileArray;
  global $fp1;
  
  // Establish tags - essentially new paragraghs.
  reset($fileArray);
  while(current($fileArray)) {
    
    // Check for evidence of a new paragraph: a blank line, a tab or multiple
    // spaces at the start of a line.  If found, add a tag on that line.
    if(preg_match("/^[\n\t\s{2,}]/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement
    
    next($fileArray);
    
  } // End while loop
  
  // Eliminate blank lines
  reset($fileArray);
  while(current($fileArray)) {
    $fileArray[key($fileArray)] = preg_replace("/^\s*?$/", '', current($fileArray));
    next($fileArray);
  } // End while loop                           
  
  // Strip newlines from each line and form paragraphs as one line each (no 
  // newline characters), trim any whitespace from the start and end of each 
  // paragraph, and output the paragraph to a file.
  reset($fileArray);
  while(current($fileArray)) {
    
    $fileString = '';
    
    // Strip newlines
    while(current($fileArray) && !array_search(key($fileArray), $tags)) {
      $fileString .= preg_replace("/[\n\r]+/", ' ', current($fileArray));
      next($fileArray);
    } // End while loop
  
    // Trim whitespace and format paragraph
    $fileString = preg_replace("/^\s*/", "\t", $fileString);
    $fileString = preg_replace("/\s*$/D", "\n", $fileString);
    
    // Output formatted string to output file
    fputs($fp1, $fileString);
    
    next($fileArray);
    
  } // End while loop
  
  fclose($fp1);
  
} // End function type1()

function type2() {
  
  global $fileArray;
  global $fp1;
  
  // Remove whitespace from each line, place a starting tab on each paragraph,
  // and enter a newline between paragraphs.
  reset($fileArray);
  while(current($fileArray)) {
    $fileArray[key($fileArray)] = preg_replace("/^\s*/", "\t", current($fileArray));
    $fileArray[key($fileArray)] = preg_replace("/\s*$/D", "\n", current($fileArray));
    fputs($fp1, current($fileArray));
    next($fileArray);
  } // End while loop
  
  fclose($fp1);
  
} // End function type2()

function removeRTF() {
  
  global $fileArray;
  global $fp1;
  
  // TODO: Remove RTF formatting
  
} // End function removeRTF()

function ASCII() {
  
  global $fileArray;
  global $fp1;
  
  // TODO: Preform ASCII conversion using values of all program arguements beyond 4  
  
} // End function ASCII()

?>