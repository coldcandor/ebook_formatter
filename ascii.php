<?php

/**
 * ASCII.php
 *
 * @author Eric Shields
 */

/* Function to convert characters to their ASCII values.  Accepts either a
 * string or an array of chars.  Other inputs will produce unknown results.
 */
function char_to_ASCII($test_string, $reject_array) {

  if(!is_array($reject_array)) {
    exit("Error(ASCII.php):  Passed object is not an array!");
  } // End if statement

  for($j = 0; $j < count($reject_array); $j++) {

    $ASCII = '';

    if(count($test_string) == 1) {
      $test_string = (string)$test_string;
      $length = strlen($test_string);
    } else {
      $length = count($test_string);
      for($i = 0; $i < $length; $i++) {
        $test_string[$i] = (string)$test_string[$i];
      }
    } // End if else statement

    for($i = 0; $i < $length; $i++) {

      switch($reject_array[$j]) {

        case 'normal':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] < 9 ||
            ($temp[$i] > 9 && $temp[$i] < 13) ||
            ($temp[$i] > 13 && $temp[$i] < 32) ||
             $temp[$i] > 126) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-normal':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] == 9 ||
             $temp[$i] == 13 ||
            ($temp[$i] > 32 && $temp[$i] < 127)) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'print':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] < 32 ||
             $temp[$i] == 127) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-print':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] > 31 && $temp[$i] < 127) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'word':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] < 48 ||
            ($temp[$i] > 57 && $temp[$i] < 65) ||
            ($temp[$i] > 90 && $temp[$i] < 97) ||
             $temp[$i] > 122) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-word':

          $temp[$i] = ord($test_string[$i]);

          if(($temp[$i] > 47 && $temp[$i] < 57) ||
            ($temp[$i] > 64 && $temp[$i] < 91) ||
            ($temp[$i] > 96 && $temp[$i] < 123)) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'number':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] < 48 ||
             $temp[$i] > 57) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-number':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] > 47 && $temp[$i] < 58) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'letter':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] < 65 ||
            ($temp[$i] > 90 && $temp[$i] < 97) ||
             $temp[$i] > 122) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-letter':

          $temp[$i] = ord($test_string[$i]);

          if(($temp[$i] > 64 && $temp[$i] < 91) ||
            ($temp[$i] > 96 && $temp[$i] < 123)) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'extended':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] < 128) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-extended':

          $temp[$i] = ord($test_string[$i]);

          if($temp[$i] > 127) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'symbol':

          $temp[$i] = ord($test_string[$i]);
          echo $temp[$i];

          if($temp[$i] < 33 ||
            ($temp[$i] > 47 && $temp[$i] < 58) ||
            ($temp[$i] > 64 && $temp[$i] < 91) ||
            ($temp[$i] > 96 && $temp[$i] < 123) ||
             $temp[$i] == 127) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;
        case 'non-symbol':

          $temp[$i] = ord($test_string[$i]);

          if(($temp[$i] > 32 && $temp[$i] < 48) ||
            ($temp[$i] > 57 && $temp[$i] < 65) ||
            ($temp[$i] > 90 && $temp[$i] < 97) ||
            ($temp[$i] > 122 && $temp[$i] < 127) ||
             $temp[$i] > 127) {
              $ASCII[] = $temp[$i];
          } // End if statment

          break;

        case 'none':

          $ASCII[] = ord($test_string[$i]);

          break;
        default:

          if(preg_match("/^\d+$/", $reject_array[$j])) {
            $temp[$i] = ord($test_string[$i]);

            if($temp[$i] != $reject_array[$j]) {
              $ASCII[] = $temp[$i];
            } // End if statement

          } else {

            echo "Error(ASCII.php):  Invalid parameter (", $reject_array[$j], ")!\n";

          } // End if - else statement

          break;

      } // End switch statement

    } // End for loop

    if(count($reject_array) > 1) {
      $test_string = ASCII_to_char($ASCII);
    } // End if statement

  } // End for loop

  if(!isset($ASCII)) {
    $ASCII = '';
  }

  return $ASCII;

} // End function ASCII_test()

/* Function to convert a series of ASCII values in an array back to the
 * represented characters.
 */
function ASCII_to_char($array) {

  if(!is_array($array)) {
    exit("Error:  Passed object is not an array!");
  } // End if statement

  while($curr = current($array)) {

    $retval[] = chr($curr);
    next($array);

  } // End while loop

  return $retval;

} // End function ACSII_to_char

?>