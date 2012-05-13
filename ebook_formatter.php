<?php

/*
 * eBookFormatter.php version 1.2.6
 *
 * Copyright 2005 Eric Shields
 */
 
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

include 'ASCII.php';

/* IDEA: Add a GUI
 * IDEA: Add ASCII
 * IDEA: Detect mismatched quotes
 * IDEA: Detect seperators (like a line of dashes, ****, or multiple blank lines)
 * IDEA: Remove email-style reply inserts (like > at the start of each line)
 * IDEA: Option to create \n terminated text at a certain column
 * TODO: Possibly implement line length method of detecting paragraphs
 * TODO: Formatted quotes (like poems, songs, letter readings, T.O.C., etc)
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
  
/* This is the meat of the application.  It will take a raw text file with \n
 * terminated lines (or \n\r, as in windows), strip out the blank lines, remove
 * the terminating newlines, sort out the paragraphs, fix a few common OCR
 * errors (such as badly formed ellipses), indent and space the paragraphs, and
 * spit the result back out to a file.
 */
function type1() {
  
  global $fileArray;
  global $fileString;
  $tags = array();
  $keep = FALSE;
  
  // Eliminate blank lines
  reset($fileArray);
  while(current($fileArray)) {
    
    // Convert the line to ASCII numbers for easier dealings.  If by some
    // bizzare chance, an ASCII code '1' exists in the file, this will ignore
    // it.  (ASCII code '1' is 'NUL' or the NULL character.)
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

    next($fileArray);
    
  } // End while loop
  
  // Strip newlines from each line and form paragraphs as one line each (no 
  // newline characters), trim any whitespace from the start and end of each 
  // paragraph, and output the paragraph to a file.
  reset($fileArray);
  while(current($fileArray)) {
    
    // Trim any whitespace from the start of the line
    $currentString = preg_replace("/^\s*/", "", $currentString);
    
    // Strip newlines
    while(current($fileArray) && !in_array(key($fileArray), $tags)) {
      echo "RUN ME!!!!!!!!!!!!!!!!!!!!!!\n";
      $currentString .= preg_replace("/\s*$/", '', current($fileArray));
      next($fileArray);
    } // End while loop
    
    // Trim whitespace and format paragraph
    $currentString = preg_replace("/^\s*/", "\t", $currentString);
    $currentString = preg_replace("/\s*$/D", "\n", $currentString);
    
    // Append current string to output string
    $fileString .= $currentString;
    
    $currentString = preg_replace("/\s*$/", ' ', current($fileArray));
       
    next($fileArray);
    
  } // End while loop
  
  // Trim whitespace and format paragraph
  $currentString = preg_replace("/^\s*/", "\t", $currentString);
  $currentString = preg_replace("/\s*$/D", "", $currentString);

  $fileString .= $currentString;
  //$fileString = fixUp($fileString);
  
} // End function type1()

/* This function is for files in which each line is not ended by a newline.  In
 * other words, the paragraphs are already determined.  The only purpose of
 * this function is to indent and space the paragraphs.
 */
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

/* Remove RTF style formatting.  In some situations, this will remove bits of
 * real text, such as {<format text>} <real text>.  The whole line will be
 * removed.
 */
function removeRTF() {
  
  global $fileArray;
  global $fileString;
  
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

    // Add current string to final one
    $fileString .= current($fileArray);
    
    next($fileArray);
    
  } // End while loop
  
} // End function removeRTF()

function fixUp($broken) {
  
  // Fix double spaces, except after periods
  $broken = preg_replace("/([^\.])  /", "$1 ", $broken);    
      
  // Break apart conversations that take place on one line (e.g. ...' '... or
  // ..." "...)
  $broken = preg_replace("/(\'|\")[ ]+(\'|\")/", "$1\n\n\t$2", $broken);
    
  // Fix ellipses
  $broken = preg_replace("/[ ]?\.[ ]?\.[ \.]{0,5}/", '...', $broken);

  // Fix places where ellipses and quotes have no spacing before the next word
  $fixed = preg_replace("/(\.\.\.)(\w)/", "$1  $2", $broken);
        
  return $fixed;
        
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

/* Output the final product, $fileString, to the file opened earlier.
 */
function outputToFile() {
  
  global $fp1;
  global $fileString;

  fputs($fp1, $fileString);
  fclose($fp1);
  
} // End function outputToFile()

?>