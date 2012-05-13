<?php

include 'ASCII.php';

/* IDEA: Add a GUI
 * TODO: Add ASCII
 * TODO: Formatted quotes (like poems, songs, letter readings, T.O.C., etc)
 * IDEA: Detect mismatched quotes
 * IDEA: Detect seperators (like a line of dashes, ****, or multiple blank lines)
 * IDEA: Remove email-style reply inserts (like > at the start of each line)
 * TODO: Possibly implement line length method of detecting paragraphs
 */

// Output a blank line for readability
echo "\n";

// Input error checking and file setup.
if(count($argv) != 4 && $argv[3] != 4) {
  exit("Usage:  php eBookFormatter.php <Input File> <Output File> <function>\n" . 
       "If function 4 is selected, enter ASCII arguements as per ASCII.php as well.\n");
} else if(!$fileArray = file($argv[1])) {
  exit("Fatal Error:  Could not open input file ($argv[1])!\n\n");
} else if(!$fp1 = fopen($argv[2], 'w')) {
  exit("Fatal Error:  Could not open output file ($argv[2])!\n\n");
} // End if - else if statements

// Initialize output string
$fileString = '';

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
    
    break;
  default:
  
    // Close output file and error out of program.
    fclose($fp1);  
    exit("Error:  Function select value must be in the range of 1 to 4\n\n");
    
    break;
    
} // End switch statement

outputToFile();

/////////// End main "method"
  
function type1() {
  
  global $fileArray;
  global $fileString;
  $tags = array();
  $keep = FALSE;
  
  // Eliminate blank lines
  reset($fileArray);
  while(current($fileArray)) {
    
    // Convert the line to ASCII numbers for easier dealings
    $ASCIIarray = char_to_ASCII(current($fileArray), array(1));
    
    // Check each ASCII character in the line
    reset($ASCIIarray);
    while(current($ASCIIarray)) {
      
      // If it's not a tab, newline, carriage return, or space, keep the line
      if(current($ASCIIarray) != 32 && current($ASCIIarray) != 10 && 
         current($ASCIIarray) != 13 && current($ASCIIarray) != 9) {
           $keep = TRUE;
           break;
      } // End if statement
      
      next($ASCIIarray);
      
    } // End while loop
    
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
  
  // Establish tags - essentially new paragraghs.
  reset($fileArray);
  while(current($fileArray)) {
    
    // Check for evidence of a new paragraph: A starting tab
    if(preg_match("/^\t+/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement
    
    // Check for additional new paragraph markings: Multiple starting spaces
    if(preg_match("/^ {3,}/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement

    // Check for additional new paragraph markings:  - | -" | - " | -' | - '
    if(preg_match("/-[ ]*(\'|\"|)[ \r]*$/", current($fileArray))) {
      $tags[] = key($fileArray) + 1;
    } // End if statement
    
    // Check for additional new paragraph markings:  . | ." | . " | .' | . '
    if(preg_match("/\.[ ]*(\'|\"|)[ \r]*$/", current($fileArray))) {
      $tags[] = key($fileArray) + 1;
    } // End if statement

    // Check for additional new paragraph markings:  Quote at start of line
    if(preg_match("/^(\'|\")/", current($fileArray))) {
      $tags[] = key($fileArray);
    } // End if statement

    // Break apart conversations that take place on one line (e.g. ...' '... or
    // ..." "...)
    $fileArray[key($fileArray)] = 
        preg_replace("/(\'|\")[ ]+(\'|\")/", "$1\n\n\t$2", current($fileArray));
      
    // Fix ellipses
    $fileArray[key($fileArray)] = 
        preg_replace("/[ ]?\.[ ]?\.[ \.]{0,5}/", '...', current($fileArray));

    // Fix places where ellipses and quotes have no spacing before the next word
    $fileArray[key($fileArray)] = 
        preg_replace("/(\.\.\.)(\w)/", "$1  $2", current($fileArray));
        
    next($fileArray);
    
  } // End while loop
  
  // Strip newlines from each line and form paragraphs as one line each (no 
  // newline characters), trim any whitespace from the start and end of each 
  // paragraph, and output the paragraph to a file.
  reset($fileArray);
  while(current($fileArray)) {
    
    //print_r($tags);
    
    // Strip newlines
    while(current($fileArray) && !in_array(key($fileArray), $tags)) {
      $currentString .= preg_replace("/[\n\r]+?$/", ' ', current($fileArray));
      next($fileArray);
    } // End while loop
    
    // Trim whitespace and format paragraph
    $currentString = preg_replace("/^\s*/", "\t", $currentString);
    $currentString = preg_replace("/\s*$/D", "\n", $currentString);
    
    // Append current string to output string
    $fileString .= $currentString;
    
    $currentString = preg_replace("/[\n\r]+?$/", ' ', current($fileArray));
       
    next($fileArray);
    
  } // End while loop
  
  // Trim whitespace and format paragraph
  $currentString = preg_replace("/^\s*/", "\t", $currentString);
  $currentString = preg_replace("/\s*$/D", "", $currentString);

  $fileString .= $currentString;
  
} // End function type1()

function type2() {
  
  global $fileArray;
  global $fileString;
  
  // Remove whitespace from each line, place a starting tab on each paragraph,
  // and enter a newline between paragraphs.
  reset($fileArray);
  while(current($fileArray)) {
    
    // Find and replace whitespace
    $fileArray[key($fileArray)] = preg_replace("/^\s*/", "\t", current($fileArray));
    $fileArray[key($fileArray)] = preg_replace("/\s*$/D", "\n", current($fileArray));
    
    // Append current string to output string
    $fileString .= current($fileArray);
    
    next($fileArray);
    
  } // End while loop
  
} // End function type2()

function removeRTF() {
  
  global $fileArray;
  global $fileString;
  
  reset($fileArray);
  while(current($fileArray)) {
    $tab = preg_quote('\tab');
    $par = preg_quote('\par');
    echo preg_quote('\\'), "\n";
    $fileArray[key($fileArray)] = 
        preg_replace("/($par |$tab )/", '', current($fileArray));
    $fileArray[key($fileArray)] = 
        preg_replace("/[\{\}].*$/", '', current($fileArray));
    $fileArray[key($fileArray)] = 
        preg_replace("/^\\\\.*$/", '', current($fileArray));

    $fileString .= current($fileArray);
    
    next($fileArray);
  } // End while loop
  
} // End function removeRTF()

function ASCII() {
  
  global $fileArray;
  global $fp1;
  
  // Preform ASCII conversion using values of all program arguements beyond 4  
  
} // End function ASCII()

function detectQuoteErrors() {

  global $fileArray;
  global $fp1;


} // End function fixQuotes()

function detectSeperators() {
  
  global $fileArray;
  global $fp1;
  
  
} // End function detectSeperators()

function outputToFile() {
  
  global $fp1;
  global $fileString;

  fputs($fp1, $fileString);
  fclose($fp1);
  
} // End function outputToFile()

?>