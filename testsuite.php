<?php

$version = "1.6.0";
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

/* This function tests each individual feature of the program and outputs a
 * summary either to an html document or a plain text document, based on the -h
 * option.  This function exits the code.
 */
function testSuite($fp, $hFlag) {

  $successCount = 0;

  /* *** Setup the tests *** */

  // Test removal of a standard inline terminator
  $tests[] = array(
    'text' => "Read all about\nhis findings",
    'expected' => "\tRead all about his findings",
    'test' => 'Remove Line Terminator',
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  .
  $tests[] = array(
    'text' => "We've waited long enough to find out, after all.\n" .
              "Like it or not,",
    'expected' => "\tWe've waited long enough to find out, after all.\n\n" .
                  "\tLike it or not,",
    'test' => 'Paragraph Detection: .',
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  .'
  $tests[] = array(
    'text' => "'We've waited long enough to find out, after all.'\n" .
              "Like it or not,",
    'expected' => "\t'We've waited long enough to find out, after all.'\n\n" .
                "\tLike it or not,",
    'test' => "Paragraph Detection: .'",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  ."
  $tests[] = array(
    'text' => "\"We've waited long enough to find out, after all.\"\n" .
              "Like it or not,",
    'expected' => "\t\"We've waited long enough to find out, after all.\"\n\n" .
                  "\tLike it or not,",
    'test' => "Paragraph Detection: .\"",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  !
  $tests[] = array(
    'text' => "We've waited long enough to find out, after all!\n" .
              "Like it or not,",
    'expected' => "\tWe've waited long enough to find out, after all!\n\n" .
                  "\tLike it or not,",
    'test' => 'Paragraph Detection: !',
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  -'
  $tests[] = array(
    'text' => "'We've waited long enough to find out, after all-'\n" .
              "Like it or not,",
    'expected' => "\t'We've waited long enough to find out, after all-'\n\n" .
                "\tLike it or not,",
    'test' => "Paragraph Detection: -'",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  ?"
  $tests[] = array(
    'text' => "\"We've waited long enough to find out, after all?\"\n" .
              "Like it or not,",
    'expected' => "\t\"We've waited long enough to find out, after all?\"\n\n" .
                  "\tLike it or not,",
    'test' => "Paragraph Detection: ?\"",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  Line starting with single quote
  $tests[] = array(
    'text' => "We've waited long enough to find out, after all\n" .
              "'Like it or not,'",
    'expected' => "\tWe've waited long enough to find out, after all\n\n" .
                  "\t'Like it or not,'",
    'test' => "Paragraph Detection: ' to start",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  Line starting with double quotes
  $tests[] = array(
    'text' => "We've waited long enough to find out, after all\n" .
              "\"Like it or not,\"",
    'expected' => "\tWe've waited long enough to find out, after all\n\n" .
                  "\t\"Like it or not,\"",
    'test' => "Paragraph Detection: \" to start",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  Numbered list
  $tests[] = array(
    'text' => "1.  This is number 1\n" .
              "2. This is number 2.\n" .
              "3. This is number 3\n",
    'expected' => "\t1.  This is number 1\n\n" .
              "\t2. This is number 2.\n\n" .
              "\t3. This is number 3",
    'test' => "Paragraph Detection: #'ed list",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  Starting tab
  $tests[] = array(
    'text' => "We've waited long enough to find out, after all\n" .
              "\tLike it or not,",
    'expected' => "\tWe've waited long enough to find out, after all\n\n" .
                  "\tLike it or not,",
    'test' => "Paragraph Detection: tab to start",
    'testType' => 'doParagraphs');

  // Test Paragraph detection:  Line starting with double quotes
  $tests[] = array(
    'text' => "We've waited long enough to find out, after all\n" .
              "    Like it or not,",
    'expected' => "\tWe've waited long enough to find out, after all\n\n" .
                  "\tLike it or not,",
    'test' => "Paragraph Detection: spaces to start",
    'testType' => 'doParagraphs');

  // Test ellipses correction: _...
  $tests[] = array(
    'text' => " ...",
    'expected' => "...",
    'test' => "Ellipses: _...",
    'testType' => 'fixEllipses');

  // Test ellipses correction: _._._._
  $tests[] = array(
    'text' => " . . . ",
    'expected' => "... ",
    'test' => "Ellipses: _._._._",
    'testType' => 'fixEllipses');

  // Test ellipses correction: ...._.
  $tests[] = array(
    'text' => ".... .",
    'expected' => "...",
    'test' => "Ellipses: ...._.",
    'testType' => 'fixEllipses');

  // Test ellipses correction: _.._
  $tests[] = array(
    'text' => " .. ",
    'expected' => "... ",
    'test' => "Ellipses: _.._",
    'testType' => 'fixEllipses');

  // Test ellipses correction: ._.__
  $tests[] = array(
    'text' => ". .  ",
    'expected' => "...  ",
    'test' => "Ellipses: ._.__",
    'testType' => 'fixEllipses');

  // Test ellipses correction: ..._.
  $tests[] = array(
    'text' => "... .",
    'expected' => "...",
    'test' => "Ellipses: ..._.",
    'testType' => 'fixEllipses');

  // Test sentence spacing correction: .\"\n
  $tests[] = array(
    'text' => "the end of sentence 1.\"\nThe start of sentence 2",
    'expected' => "the end of sentence 1.\"\nThe start of sentence 2",
    'test' => "Sen. Spacing: .\"\\n",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: ._\"\n
  $tests[] = array(
    'text' => "the end of sentence 1. \"\nThe start of sentence 2",
    'expected' => "the end of sentence 1.\"\nThe start of sentence 2",
    'test' => "Sen. Spacing: ._\"\\n",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: .\"_\n
  $tests[] = array(
    'text' => "the end of sentence 1.\" \nThe start of sentence 2",
    'expected' => "the end of sentence 1.\"\nThe start of sentence 2",
    'test' => "Sen. Spacing: .\"_\\n",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: ._\"_\n
  $tests[] = array(
    'text' => "the end of sentence 1. \" \nThe start of sentence 2",
    'expected' => "the end of sentence 1.\"\nThe start of sentence 2",
    'test' => "Sen. Spacing: ._\"_\\n",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: .\"\w
  $tests[] = array(
    'text' => "the end of sentence 1.\"The start of sentence 2",
    'expected' => "the end of sentence 1.\"  The start of sentence 2",
    'test' => "Sen. Spacing: .\"\\w",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: .\"_\w
  $tests[] = array(
    'text' => "the end of sentence 1.\" The start of sentence 2",
    'expected' => "the end of sentence 1.\"  The start of sentence 2",
    'test' => "Sen. Spacing: .\"_\\w",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: .\w
  $tests[] = array(
    'text' => "the end of sentence 1.The start of sentence 2",
    'expected' => "the end of sentence 1.  The start of sentence 2",
    'test' => "Sen. Spacing: .\\w",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: ._\w
  $tests[] = array(
    'text' => "the end of sentence 1. The start of sentence 2",
    'expected' => "the end of sentence 1.  The start of sentence 2",
    'test' => "Sen. Spacing: ._\\w",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: .\n
  $tests[] = array(
    'text' => "the end of sentence 1.\nThe start of sentence 2",
    'expected' => "the end of sentence 1.\nThe start of sentence 2",
    'test' => "Sen. Spacing: .\\n",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: ._\n
  $tests[] = array(
    'text' => "the end of sentence 1. \nThe start of sentence 2",
    'expected' => "the end of sentence 1.\nThe start of sentence 2",
    'test' => "Sen. Spacing: ._\\n",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: ._\"\w
  $tests[] = array(
    'text' => "the end of sentence 1. \"The start of sentence 2",
    'expected' => "the end of sentence 1.  \"The start of sentence 2",
    'test' => "Sen. Spacing: ._\"\\w",
    'testType' => 'fixSentenceSpacing');

  // Test sentence spacing correction: ._\"_\w
  $tests[] = array(
    'text' => "the end of sentence 1. \" The start of sentence 2",
    'expected' => "the end of sentence 1.\"  The start of sentence 2",
    'test' => "Sen. Spacing: ._\"_\\w",
    'testType' => 'fixSentenceSpacing');

  // Test conversation splitting: ...' '...
  $tests[] = array(
    'text' => "the end of sentence 1.' 'The start of sentence 2",
    'expected' => "the end of sentence 1.'\n'The start of sentence 2",
    'test' => "Conv. Splitting: ...' '...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ..." "...
  $tests[] = array(
    'text' => "the end of sentence 1.\" \"The start of sentence 2",
    'expected' => "the end of sentence 1.\"\n\"The start of sentence 2",
    'test' => "Conv. Splitting: ...\" \"...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ...''...
  $tests[] = array(
    'text' => "the end of sentence 1.''The start of sentence 2",
    'expected' => "the end of sentence 1.'\n'The start of sentence 2",
    'test' => "Conv. Splitting: ...''...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ...""...
  $tests[] = array(
    'text' => "the end of sentence 1.\"\"The start of sentence 2",
    'expected' => "the end of sentence 1.\"\n\"The start of sentence 2",
    'test' => "Conv. Splitting: ...\"\"...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ...' "...
  $tests[] = array(
    'text' => "the end of sentence 1.' \"The start of sentence 2",
    'expected' => "the end of sentence 1.'\n\"The start of sentence 2",
    'test' => "Conv. Splitting: ...' \"...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ..." '...
  $tests[] = array(
    'text' => "the end of sentence 1.\" 'The start of sentence 2",
    'expected' => "the end of sentence 1.\"\n'The start of sentence 2",
    'test' => "Conv. Splitting: ...\" '...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ...'"...
  $tests[] = array(
    'text' => "the end of sentence 1.'\"The start of sentence 2",
    'expected' => "the end of sentence 1.'\n\"The start of sentence 2",
    'test' => "Conv. Splitting: ...'\"...",
    'testType' => 'splitConversation');

  // Test conversation splitting: ..."'...
  $tests[] = array(
    'text' => "the end of sentence 1.\"'The start of sentence 2",
    'expected' => "the end of sentence 1.\"\n'The start of sentence 2",
    'test' => "Conv. Splitting: ...\"'...",
    'testType' => 'splitConversation');

  // Test single quote correction: Sentence End
  $tests[] = array(
    'text' => "the end of sentence 1.'  The start of sentence 2",
    'expected' => "the end of sentence 1.\"  The start of sentence 2",
    'test' => "Single Quote: Sentence End",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: Paragraph End
  $tests[] = array(
    'text' => "the end of sentence 1.'\nThe start of sentence 2",
    'expected' => "the end of sentence 1.\"\nThe start of sentence 2",
    'test' => "Single Quote: Paragraph End",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: Sentence Start
  $tests[] = array(
    'text' => "the end of sentence 1.  'The start of sentence 2",
    'expected' => "the end of sentence 1.  \"The start of sentence 2",
    'test' => "Single Quote: Sentence Start",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: Paragraph Start
  $tests[] = array(
    'text' => "the end of sentence 1.\n'The start of sentence 2",
    'expected' => "the end of sentence 1.\n\"The start of sentence 2",
    'test' => "Single Quote: Paragraph Start",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: After a comma
  $tests[] = array(
    'text' => "the end of sentence 1,' a person said.",
    'expected' => "the end of sentence 1,\" a person said.",
    'test' => "Single Quote: After comma",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: Contraction, internal
  $tests[] = array(
    'text' => "the end of sentence 1 can't be the start of sentence 2",
    'expected' => "the end of sentence 1 can't be the start of sentence 2",
    'test' => "Single Quote: Contraction 1",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: Contraction, end
  $tests[] = array(
    'text' => "the end of sentence 1 can' be the start of sentence 2",
    'expected' => "the end of sentence 1 can' be the start of sentence 2",
    'test' => "Single Quote: Contraction 2",
    'testType' => 'fixSingleQuotes');

  // Test single quote correction: Contraction, start
  $tests[] = array(
    'text' => "the end of sentence 1 'an be the start of sentence 2",
    'expected' => "the end of sentence 1 'an be the start of sentence 2",
    'test' => "Single Quote: Contraction 3",
    'testType' => 'fixSingleQuotes');

  /* *** Run the tests *** */

  // If HTML ouput is selected
  if($hFlag) {

    // Prep the output HTML table
    fprintf($fp,
    "<!DOCTYPE HTML>
    <html>
    <head>
      <LINK href=\"testSuite.css\" rel=\"stylesheet\" type=\"text/css\">
      <title>eBookFormatter Built-in Test Suite</title>
    </head>
    <body>
    <span class=\"tableTitle\">Tests for doParagraphs() </span>
    <table border=\"1\" class=\"tableBase\">
    <tr>
     <td>No.</td>
     <td>Test</td>
     <td>Expected Output</td>
     <td>Actual Output</td>
     <td>Match?</td>
    </tr>");

    // Run the tests and output the results
    foreach ($tests as $t => $d) {

      // Create one table for each function
      if($t != 0 && $d['testType'] != $tests[$t - 1]['testType']) {

        fprintf($fp,
        "</table>
        <br /><br />
        <span class=\"tableTitle\">Tests for " . $d['testType'] . "()</span>
        <table border=\"1\" class=\"tableBase\">
        <tr>
         <td>No.</td>
         <td>Test</td>
         <td>Expected Output</td>
         <td>Actual Output</td>
         <td>Match?</td>
        </tr>");

      } // End if statement

      // Prep the current row
      fprintf($fp, " <tr>\n");
      fprintf($fp, "  <td>%d</td>\n", $t + 1);
      fprintf($fp, "  <td>%s</td>\n", $d['test']);

      // Output the expected result to the table
      fprintf($fp, "  <td><pre>%s</pre></td>\n", $d['expected']);

      // Run the test
      $result = runTest($d['testType'], $d['text']);

      // Output the actual result
      fprintf($fp, "  <td><pre>%s</pre></td>\n", $result);

      // Compare the expected output to the actual output and set the match
      // color appropriately
      if($result != $d['expected']) {
        fprintf($fp, "  <td bgcolor=\"red\">&nbsp;</td>\n");
      } else {
        fprintf($fp, "  <td bgcolor=\"green\">&nbsp;</td>\n");
        $successCount++;
      } // End if - else statements

      // Conclude the table row
      fprintf($fp, " </tr>\n");

    } // End foreach loop

    // Conclude the table and output extra spaces for readability
    fprintf($fp, "</table>\n<br />\n<span class=\"conclusion\">Totals: %d of %d tests passed.</span>\n</body>\n</html>\n",
            $successCount, count($tests));
    fprintf($fp, "<br /><br /><br /><br /><br /><br />&nbsp");

  // If plain text ouptut is selected
  } else {

    // Write the header
    fprintf($fp, "eBookFormatter Built-in Test Suite\n\n\n");

    foreach($tests as $t => $d) {

      // Output the test number and name
      fprintf($fp, "Test #%d:  %s\n\n", $t + 1, $d['test']);

      // Output the expected result
      fprintf($fp, "Expected Result:\t\t%s\n\n", $d['expected']);

      // Run the test
      $result = runTest($d['testType'], $d['text']);

      // Output the actual result
      fprintf($fp, "Actual Result:\t\t%s\n\n", $result);

      // Compare the actual output with the expected
      if($result != $d['expected']) {
        fprintf($fp, "*** Failure! ***\n\n");
      } else {
        fprintf($fp, "Success!\n\n\n");
        $successCount++;
      } // End if - else statement

    } // End foreach loop

    fprintf($fp, "\n\n*-*-* Test Totals: %d of %d tests passed.*-*-*\n\n\n",
            $successCount, count($tests));

  } // End if - else statement

  fclose($fp);

  exit("\nTestSuite finished, exiting program.\n");

} // End function testSuite()

/* Select and run the appropriate function for the current testSuite test.
 */
function runTest($testType, $text) {

  switch($testType) {
    case 'doParagraphs':
      $result = doParagraphs($text);
      break;
    case 'removeRTF':
      $result = removeRTF($text);
      break;
    case 'fixEllipses':
      $result = fixEllipses($text);
      break;
    case 'fixSentenceSpacing':
      $result = fixSentenceSpacing($text);
      break;
    case 'splitConversation':
      $result = splitConversation($text);
      break;
    case 'fixSingleQuotes':
      $result = fixSingleQuotes($text);
      break;
    default:
      $result = "Invalid test type selected!";
      break;
  } // End switch statement

  return $result;

} // End function functionSelect()

?>