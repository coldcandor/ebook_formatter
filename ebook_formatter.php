<?php

$version = "2.0.0";
$copyright = "2005 - 2012 Eric Shields";

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

include 'testsuite.php';

/* TODO:
 *   Correct sentence spacing around dashes, commas, and after ellipses
 *   Output testSuite to STDOUT when no -o specified, to match normal runs
 *   Add "puncuation at end of line + blank next line" as paragraph tag
 *   Check for lines consisting exclusively of 1 character, like '-' or '*'
 *   Check for dash-as-part-of-a-word
 *   Echo processing details to user if verbose flag enabled
 *   Detect ASCII art and preserve leading whitespace
 *   Detect lines shorter than the typical
 *   Add option to set newline format (Linux vs. Windows vs. Mac)
 *   Allow batch processing with a designated string appended to each output file
 */

// Output a blank line for readability
echo "\n";

// Check that at least the input file is given
if($argc == 1) {
  exit("Usage:  php ebook_formatter.php -i <inputFile> [-o <outputFile>] " .
       "[-f <firstFunction[,secondFunction[,thirdFunction[,...]]]>] [-v] " .
       "[-h]\n");
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

// Initialize variables
$oFlag = FALSE;
$fFlags = array();
$vFlag = FALSE;
$hFlag = FALSE;
$ext = 'txt';
$fp1 = NULL;
$match = '';
$fileString = '';

// Parse the command line arguements. This should ignore the first arguement,
// which is the program name.
for($i = 1; $i < $argc; $i++) {

  preg_match("/-(\w)/", $argv[$i], $match);

  switch($match[1]) {

    case 'i':

      if(!($fileString = file_get_contents($argv[$i + 1]))) {
        exit("Fatal Error(ebook_formatter.php):  Could not open input file (" .
             $argv[$i + 1] . ")!\n");
      } // End if statement

      $i++;

      break;
    case 'o':

      if(!($fp1 = fopen($argv[$i + 1], 'w'))) {
        exit("Fatal Error(ebook_formatter.php):  Could not open output file (" .
             $argv[$i + 1] . ")!\n");
      } // End if statement

      $oFlag = TRUE;

      $i++;

      break;
    case 'f':

      $formats = explode(',', $argv[$i + 1]);

      foreach($formats as $f) {
        $fFlags[] = $f;
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

// Check if this run is to run the testSuite.
if(in_array('testsuite', $fFlags)) {

	if ($oFlag) {

		testSuite($fp1, $hFlag);

	} else {

		fprintf(STDERR, "\nWarning!  TestSuite function selected with no output file!"
										. "  Outputting to test_output.%s.\n\n", $ext);

		if(!($fp1 = fopen("test_output.$ext", 'w'))) {
			exit("Fatal Error(ebook_formatter.php):  Could not open output file " .
					 "(test_output.$ext)!\n");
		} // End if statement

		testSuite($fp1, $hFlag);

	} // End else statement

} // End if - else if statments

// Preform the functions selected, in the order listed on the command line
foreach($fFlags as $f) {

  switch($f) {
    case 'paragraphs':
      $fileString = doParagraphs($fileString);
      break;
    case 'rtf':
      $fileString = removeRTF($fileString);
      break;
    case 'ellipses':
      $fileString = fixEllipses($fileString);
      break;
    case 'spacing':
      $fileString = fixSentenceSpacing($fileString);
      break;
    case 'split-conversations':
      $fileString = splitConversation($fileString);
      break;
    case 'fix-quotes':
      $fileString = fixSingleQuotes($fileString);
      break;
    default:

      // Close output file and error out of program.
      if($oFlag) {
        fclose($fp1);
      } // End if statement
      exit("Fatal Error(ebook_formatter.php):  Invalid function selected " .
           "($f)!\n");

      break;

  } // End switch statement

} // End foreach loop

outputToFile($fp1, $fileString, $oFlag, $vFlag, $version, $copyright);

/////////// End main "method"

/* The heart of the program, this function will detect and seperate the
 * paragraphs in the file.  This method will remove the newlines at the
 * end of each line and indent each paragraph with a tab.
 */
function doParagraphs($ickyString) {

  $tags = array();
  $match = '';
  $cleanString = '';

  // Create the line array
  $fileArray = preg_split("/[\n\r]+/", $ickyString);

  // Check if the file split into lines correctly
  foreach($fileArray as $v) {
    if(preg_match("/[\n\r]+/", $v)) {
      exit("Fatal Error(ebook_formatter.php):  Split did not preform" .
      "correctly!\n");
    } // End if statement
  } // End foreach loop

  // Eliminate blank lines
  foreach($fileArray as $key => $value) {

    // Remove any line that consists entirely of whitespace
    if(preg_match("/^\s*$/", $value, $match)) {
      unset($fileArray[$key]);
    } // End if - else statement

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
  foreach($fileArray as $key => $value) {

    // Check for evidence of a new paragraph: A starting tab
    if(preg_match("/^\t+/", $value)) {
      $tags[] = $key;
    } // End if statement

    // Check for additional new paragraph markings: Multiple starting spaces
    if(preg_match("/^[ ]{3,}/", $value)) {
      $tags[] = $key;
    } // End if statement

    // Check for additional new paragraph markings:  Period, dash, or other
    //          punctuation before a quote at end of line
    if(preg_match("/(\.|-|\x97|\!|\?|\*|~)[ ]*(\x60|\'|\"|)[ \r]*$/",
            $value)) {
      $tags[] = $key + 1;
    } // End if statement

    // Check for additional new paragraph markings:  Quote at start of line
    if(preg_match("/^(\x60|\'|\")/", $value)) {
      $tags[] = $key;
    } // End if statement

    // Check for additional new paragraph markings:  Numbered list
    if(preg_match("/[\s]*\d+\./", $value)) {
      $tags[] = $key;
    } // End if statement

  } // End while loop

  // Strip newlines from each line and form paragraphs as one line each (no
  // newline characters), and trim any whitespace from the start and end of each
  // paragraph.
  $currentString = '';
  foreach($fileArray as $key => $value) {

    // Strip newlines
    if(!in_array($key, $tags)) {

      // Trim whitespace and add back a single trailing space
      $currentString .=
          preg_replace("/^\s*(.+?)\s*$/", "$1 ", $value);

    // Complete paragraph
    } else {

      // Trim whitespace and format paragraph
      $currentString = preg_replace("/^\s*(.+?)\s*$/", "\t$1\n",
                $currentString);

      // Append current string to output string
      $cleanString .= $currentString;

      $currentString =
          preg_replace("/^\s*(.+?)\s*$/", "$1 ", $value);

    } // End if - else statements

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
      exit("Fatal Error(ebook_formatter.php):  Split did not preform
            correctly!\n");
    } // End if statement
  } // End foreach loop

  foreach($fileArray as $key => $value) {

    // Get strings that the regular expression parser will accept
    $tab = preg_quote('\tab');
    $par = preg_quote('\par');

    // Remove \tab, \par, and any lines that start with \, }, or {
    $fileArray[$key] =
        preg_replace("/($par |$tab )/", '', $value);
    $fileArray[$key] =
        preg_replace("/[\{\}].*$/", '', $value);
    $fileArray[$key] =
        preg_replace("/^\\\\.*$/", '', $value);

    // Add current string to final one and preserve line terminators
    $ptString .= $value . $terminator[0];

  } // End while loop

  return $ptString;

} // End function removeRTF()

/* Correct the spacing and number of periods in ellipses.  All will be converted
 * to ...  This should affect nothing else.
 */
function fixEllipses($broken) {

  // Fix ellipses
  $fixed = preg_replace("/[ ]?\.[ \.]*\./", '...', $broken);

  return $fixed;

} // End function fixEllipses()

/* Fix the several related spacing errors, such as more than one space between
 * words or less than 2 spaces after a punctuation mark.
 */
function fixSentenceSpacing($broken) {

  $pieces = '';
  $fixed = '';

  // Replace all instances of 2 or more spaces with 1
  $broken = preg_replace("/[ ]{2,}/", " ", $broken);

  $fileArray = preg_split("/([\.\!\?\:-][ ]?[\'\"]?[ \w\n\r]{0,3})/", $broken,
              -1, PREG_SPLIT_DELIM_CAPTURE);

  // Fix spacing after punctuation marks
  for($i = 0; $i < count($fileArray); $i++) {

    preg_match("/(\.|\!|\?|\:)( )?(\'|\")?( )?([\w\n\r]{0,3})/", $fileArray[$i],
              $pieces);

    if(isset($pieces[5]) && preg_match("/[\n\r]+/",$pieces[5])) {

      // Any time that it ends in a newline character, ignore all the spaces
      $fileArray[$i] =
                preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?( )?([\w\n\r]{0,3})/",
                "$1$3$5", $fileArray[$i]);

    } else if(isset($pieces[4]) && isset($pieces[5]) && preg_match("/\w/",$pieces[5])) {

      // If it ends in a space followed by a word character, assume it's part
      // of the first sentence.
      $fileArray[$i] =
                preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?( )?([\w\n\r]{0,3})/",
                "$1$3  $5", $fileArray[$i]);

    } else if(isset($pieces[2]) && !isset($pieces[4]) && preg_match("/\w/",$pieces[5])) {

      // If there's a space before the quote and none after, assume it's part
      // of the second sentence.
      $fileArray[$i] =
                preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?( )?([\w\n\r]*)/",
                "$1  $3$5", $fileArray[$i]);

    } else if(!isset($pieces[2]) && !isset($pieces[4]) && isset($pieces[5]) && preg_match("/\w/",$pieces[5])) {

      // If there's no spaces around the quote, assume it's part of the first
      // sentence.
      $fileArray[$i] =
                preg_replace("/(\.|\!|\?|\:)( )?(\'|\")?( )?([\w\n\r]*)/",
                "$1$3  $5", $fileArray[$i]);

    } // End if - else if statements

    $pieces = '';

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
  $fixed = preg_replace("/(\'|\")[ ]*(\'|\")/", "$1\n$2", $broken);

  return $fixed;

} // End function splitConversation()

/* Some files will have single quotes where there should be doubles, this
 * function will fix those places.  NOTE:  Occurances of quotes within quotes
 * (to denote someone quoting someone else, for example) will be replaced with
 * double quotes.
 */
function fixSingleQuotes($broken) {

  // Replace single quotes with doubles, except for cases like "don't"
  $broken = preg_replace("/([ ]*,)\'([ ]*[\w\d])/", "$1\"$2",
            $broken);
  $broken = preg_replace("/([\s]+)\'([\x41-\x5A\d])/", "$1\"$2",
            $broken);
  $fixed = preg_replace("/([ ]*)([\.\!\?\-][ ]*)\'/", "$1$2\"",
            $broken);

  return $fixed;

} // End function fixSingleQuotes()

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