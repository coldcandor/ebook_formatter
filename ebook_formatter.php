<?php

$version = "1.4.0";
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

// Initialize the variable used in preg_match so the parser will stop complaining
$match = '';

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
//
$oFlag = FALSE;
$fFlags = array();
$vFlag = FALSE;

// Parse the command line arguements. This should ignore the first arguement,
// which is the program name.
for($i = 1; $i < $argc; $i++) {
  
  preg_match("/-(\w)/", $argv[$i], $match);
  
  switch($match[1]) {
    
    case 'i':
    
      if(!$inputString = file_get_contents($argv[$i + 1])) {
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
    default:
    
      fprintf(STDERR, "\nWarning!  Unrecognized switch (%s), ignoring.\n", $argv[$i]);
      
      break;
  } // End switch statement
  
  unset($match); // Ensure that nothing is repeated or mixed up
  
} // End foreach loop

// Initialize output string
if(!$vFlag) {
  $fileString = "{Automatically formatted using eBookFormatter v" . $version . "\n"
                . " Copyright " . $copyright . "}\n\n";
} else {
  
  $fileString = '';
  
} // End if - else statement

// Create the line array
$fileArray = preg_split("/[\n\r]+/", $inputString);

// Check if the file split into lines correctly
foreach($fileArray as $v) {
  if(preg_match("/[\n\r]+/", $v)) {
    exit("Fatal Error(eBookFormatter.php):  Split did not preform correctly!\n");
  } // End if statement
} // End foreach loop

// Preform the functions selected, in the order listed on the command line
reset($fFlags);
foreach($fFlags as $f) {

  if($f) {
    
    switch(key($fFlags)) {
      case 1:
        type1();
        break;
      case 2:
        removeRTF();
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
  $match = '';
  
  // Eliminate blank lines
  reset($fileArray);
  while(current($fileArray)) {
    
    // If it's not a tab, newline, carriage return, or space, keep the line
    if(preg_match("/[^\x09\x0A\x0D\x20]/", current($fileArray), $match)) {
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
    if(preg_match("/(\.|-|\x97|\!|\?)[ ]*(\x60|\'|\"|)[ \r]*$/", current($fileArray))) {
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
      $currentString = preg_replace("/^\s*(.+?)\s*$/", "\t$1\n\n", $currentString);
      
      // Append current string to output string
      $fileString .= $currentString;
      
      $currentString = 
          preg_replace("/^\s*(.+?)\s*$/", "$1 ", current($fileArray));

    } // End if - else statements
    
    next($fileArray);
    
  } // End while loop
  
  // Trim whitespace and format paragraph
  $currentString = preg_replace("/^\s*(.)/", "\t$1", $currentString);
  $currentString = preg_replace("/(.)\s*$/", "$1", $currentString);

  $fileString .= $currentString;
  $fileString = fixUp($fileString);
  
} // End function type1()

/* Remove RTF style formatting.
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
  
  // Fix double (or more) spaces, except after periods
  $broken = preg_replace("/([^\.])[ ]{2,}/", "$1 ", $broken);    
      
  // Break apart conversations that take place on one line (e.g. ...' '... or
  // ..." "...)
  $broken = preg_replace("/(\'|\")[ ]+(\'|\")/", "$1\n\n\t$2", $broken);
    
  // Fix ellipses
  $broken = preg_replace("/[ ]?\.[ ]?\.[ \.]{0,5}/", '...', $broken);
  
  // Fix multiple spaces (not good gramatical form, but works fine for reading)
//  $broken = preg_replace("/[ ]{2,}/", ' ', $broken);

  // Replace single quotes with doubles, except for cases like "don't"
//  $broken = preg_replace("/([\x09\x20])\x27/", "$1\x22", $broken);
//  $broken = preg_replace("/\x27([\x20\x0A])/", "\x22$1", $broken);

  // Fix places where ellipses and quotes have no spacing before the next word
//  $fixed = preg_replace("/(\.\.\.|[ ]*\'|[ ]*\")(\w)/", "$1  $2", $broken);
        
  return $broken;
//  return $fixed;
  
} // End function fixUp()
        
/* Output the final product, $fileString, to the file opened earlier.
 */
function outputToFile() {
  
  global $fp1;
  global $fileString;
  global $oFlag;

  if($oFlag) {
    fputs($fp1, $fileString);
    fclose($fp1);
  } else {
    echo $fileString;
  } // End if - else statement
  
} // End function outputToFile()

?>