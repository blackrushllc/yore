<?php
/*

 ▄▄▄▄    ██▓    ▄▄▄       ▄████▄   ██ ▄█▀ ██▀███   █    ██   ██████  ██░ ██
▓█████▄ ▓██▒   ▒████▄    ▒██▀ ▀█   ██▄█▒ ▓██ ▒ ██▒ ██  ▓██▒▒██    ▒ ▓██░ ██▒
▒██▒ ▄██▒██░   ▒██  ▀█▄  ▒▓█    ▄ ▓███▄░ ▓██ ░▄█ ▒▓██  ▒██░░ ▓██▄   ▒██▀▀██░
▒██░█▀  ▒██░   ░██▄▄▄▄██ ▒▓▓▄ ▄██▒▓██ █▄ ▒██▀▀█▄  ▓▓█  ░██░  ▒   ██▒░▓█ ░██
░▓█  ▀█▓░██████▒▓█   ▓██▒▒ ▓███▀ ░▒██▒ █▄░██▓ ▒██▒▒▒█████▓ ▒██████▒▒░▓█▒░██▓
░▒▓███▀▒░ ▒░▓  ░▒▒   ▓▒█░░ ░▒ ▒  ░▒ ▒▒ ▓▒░ ▒▓ ░▒▓░░▒▓▒ ▒ ▒ ▒ ▒▓▒ ▒ ░ ▒ ░░▒░▒
▒░▒   ░ ░ ░ ▒  ░ ▒   ▒▒ ░  ░  ▒   ░ ░▒ ▒░  ░▒ ░ ▒░░░▒░ ░ ░ ░ ░▒  ░ ░ ▒ ░▒░ ░
 ░    ░   ░ ░    ░   ▒   ░        ░ ░░ ░   ░░   ░  ░░░ ░ ░ ░  ░  ░   ░  ░░ ░
 ░          ░  ░     ░  ░░ ░      ░  ░      ░        ░           ░   ░  ░  ░
      ░                  ░

8    8
8    8 eeeee eeeee  eeee
8eeee8 8  88 8   8  8
  88   8   8 8eee8e 8eee
  88   8   8 88   8 88
  88   8eee8 88   8 88ee

Yore Console

Purpose:
1. Create a console command to zip/unzip Yore data and push to whereever.
Then this where-ever becomes the data source which will tun your application on any instance of Yore that has the
required modules installed
2. Tests Tests Tests



*/
# This thing does everything
use App\Controller;

require '../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

//$y = $controller = new Controller(true);

//doCli();

function doCli()
{
    $prompt = "Yore> ";
    while (true) {
        $input = readline($prompt);
        if ($input === 'quit') {
            break;
        }

        try {
            // Example usage:
            $input = "(1 + 2) * 3 - 4 / 2";
            $input = "abs(-5) + iif(0) + iif(3)";
            $input = "abs(-5.5) + iif(0) + iif(3) + (3.14 * 2) - (-1.25)";
            // Wow, ChatGPT write me a recursive descent parser with functions
            $result = parse_expression($input);
            echo "Result: " . $result . "\n";  // Output: Result: 7
            //eval($input);
        } catch (Throwable $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
        }
    }
}



function parse_expression($input) {
    $index = 0;
    $len = strlen($input);

    // Helper function to consume whitespace
    function consume_whitespace(&$input, &$index, $len) {
        while ($index < $len && ctype_space($input[$index])) {
            $index++;
        }
    }

    // Recursive function to parse a primary factor (number, function, or parenthesis expression)
    function parse_primary(&$input, &$index, $len) {
        consume_whitespace($input, $index, $len);

        // Check for function names
        if (substr($input, $index, 3) === 'abs') {
            $index += 3; // Consume 'abs'
            consume_whitespace($input, $index, $len);
            if ($input[$index] !== '(') {
                throw new Exception("Expected '(' after function name");
            }
            $index++; // Consume '('
            $value = parse_expression_internal($input, $index, $len); // Parse the argument
            consume_whitespace($input, $index, $len);
            if ($input[$index] !== ')') {
                throw new Exception("Expected ')' after function argument");
            }
            $index++; // Consume ')'
            return abs($value); // Return the absolute value
        }

        if (substr($input, $index, 3) === 'iif') {
            $index += 3; // Consume 'iif'
            consume_whitespace($input, $index, $len);
            if ($input[$index] !== '(') {
                throw new Exception("Expected '(' after function name");
            }
            $index++; // Consume '('
            $value = parse_expression_internal($input, $index, $len); // Parse the argument
            consume_whitespace($input, $index, $len);
            if ($input[$index] !== ')') {
                throw new Exception("Expected ')' after function argument");
            }
            $index++; // Consume ')'
            return $value == 0 ? 0 : 1; // Return 0 if value is 0, otherwise return 1
        }

        // Check for parentheses
        if ($input[$index] === '(') {
            $index++; // Consume '('
            $value = parse_expression_internal($input, $index, $len); // Recursively parse expression inside parentheses
            consume_whitespace($input, $index, $len);
            if ($input[$index] === ')') {
                $index++; // Consume ')'
                return $value;
            } else {
                throw new Exception("Mismatched parentheses");
            }
        }

        // Parse number (including negative numbers and decimals)
        $startIndex = $index;
        if ($input[$index] === '-') {
            $index++; // Consume negative sign
        }
        $hasDecimal = false;
        while ($index < $len && (ctype_digit($input[$index]) || $input[$index] === '.')) {
            if ($input[$index] === '.') {
                if ($hasDecimal) {
                    throw new Exception("Unexpected second decimal point");
                }
                $hasDecimal = true;
            }
            $index++;
        }

        if ($startIndex === $index || ($input[$index-1] === '-')) {
            throw new Exception("Expected number");
        }

        $numberStr = substr($input, $startIndex, $index - $startIndex);
        return (float)$numberStr;
    }

    // Parse multiplication and division
    function parse_term(&$input, &$index, $len) {
        $value = parse_primary($input, $index, $len);

        while (true) {
            consume_whitespace($input, $index, $len);
            if ($index >= $len) break;

            if ($input[$index] === '*') {
                $index++; // Consume '*'
                $value *= parse_primary($input, $index, $len);
            } elseif ($input[$index] === '/') {
                $index++; // Consume '/'
                $value /= parse_primary($input, $index, $len);
            } else {
                break;
            }
        }

        return $value;
    }

    // Parse addition and subtraction
    function parse_expression_internal(&$input, &$index, $len) {
        $value = parse_term($input, $index, $len);

        while (true) {
            consume_whitespace($input, $index, $len);
            if ($index >= $len) break;

            if ($input[$index] === '+') {
                $index++; // Consume '+'
                $value += parse_term($input, $index, $len);
            } elseif ($input[$index] === '-') {
                $index++; // Consume '-'
                $value -= parse_term($input, $index, $len);
            } else {
                break;
            }
        }

        return $value;
    }

    return parse_expression_internal($input, $index, $len);
}

//// Example usage:
//$expression = "abs(-5.5) + iif(0) + iif(3) + (3.14 * 2) - (-1.25)";
//$result = parse_expression($expression);
//echo "Result: " . $result . "\n";  // Output: Result: 12.03


// Here's your Sting Parser Bro:

function extractQuotedStrings(&$array, $string) {
    $pattern = '/(["\'])(.*?)(?<!\\\\)\1/'; // Regex to match single and double quoted strings
    $matches = [];

    // Find all quoted strings
    preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);

    // Check for unclosed quoted string
    $openQuotes = substr_count($string, '"') % 2 !== 0 || substr_count($string, "'") % 2 !== 0;
    if ($openQuotes) {
        return false; // Return false if any quote is unclosed
    }

    // Loop through each matched quoted string
    foreach ($matches[0] as $match) {
        $key = uniqid(); // Create a unique key
        $array[$key] = $match[0]; // Add quoted string to the array with the key
        $string = str_replace($match[0], $key, $string); // Replace quoted string in original string with key
    }

    return $string; // Return modified string
}

// Example usage:
#$myArray = [];
#$myString = 'This is a "test string" and this is \'another one\'';
#$result = extractQuotedStrings($myArray, $myString);

#echo $result; // Outputs: This is a 651a3d34dbbc4 and this is 651a3d34dbbe7
#print_r($myArray);

// Outputs:
// Array
// (
//     [651a3d34dbbc4] => "test string"
//     [651a3d34dbbe7] => 'another one'
// )

// Here's your Fred parser, BRO:

function doIt($head, $tail) {

    return "[ RESULT OF do $head with $tail ]";

}

function processMacroString($string) {

    //    // Regex to match @keyword() or @keyword(alphanumeric content)
//    $pattern1 = '/^(.*?)@(\w+)\((.*?)\)(.*)$/';
//
//
//    // Match pattern that accounts for nested parentheses
//    $pattern = '/^(.*?)@(\w+)\((.*)\)(.*)$/s';

    $pattern = '/^(.*?)@(\w+)\(([^)]*)\)(.*)$/s';

    // SO this fixed my problem (discussed with chatgpt on 9/12) but it still hoses stuff past the @chs_nurse() ...

    // I think one thing to do is only Fredify the HTML in the body but also

    // See what was going wrong on the nurse signin page after the chs_nurse(), what hosed the options() fredspression

    if (preg_match($pattern, $string, $matches)) {
        $beforeAt = $matches[1];           // Everything before '@'
        $keyword = $matches[2];            // Keyword after '@'
        $insideParentheses = $matches[3];  // The content between parentheses (might contain nested ones)
        $afterParentheses = $matches[4];   // Everything after the closing ')'

        // We need to handle the content inside the parentheses for nesting
        //$insideParentheses = matchBalancedParentheses($insideParentheses);

        // Display the four strings
        echo "Before @: $beforeAt\n";
        echo "Keyword: $keyword\n";
        echo "Inside (): $insideParentheses\n";
        echo "After (): $afterParentheses\n";

        // Recursively process content inside parentheses if not empty
        if (!empty($insideParentheses)) {
            //echo "\n-- Recursively processing inside parentheses --\n";
            $insideParentheses = processMacroString($insideParentheses);  // Recursive call
        }

        return $beforeAt . doIt($keyword, $insideParentheses) . $afterParentheses;
    } else {
        // No macro pattern found
        echo "No macro found, string: $string\n";

        return $string;
    }
}

function matchBalancedParentheses($str) {
    $openParentheses = 0;
    $result = '';
    $foundOpening = false;

    for ($i = 0; $i < strlen($str); $i++) {
        if ($str[$i] === '(') {
            if ($openParentheses === 0) {
                $foundOpening = true;
            }
            $openParentheses++;
        } elseif ($str[$i] === ')') {

            $openParentheses--;
            if ($openParentheses === 0 && $foundOpening) {
                $result .= $str[$i];
                break;  // We've found the matching closing parenthesis
            }
        }
        $result .= $str[$i];
    }

    return $result;
}

// Example usage:
//$exampleString = "Hello @dash(test123(nested)) World";
//echo "PARSE:" . $exampleString . "\n";
//$x = processMacroString($exampleString);
//
//var_dump($x);

echo "\n\n\n";

// Example usage:
$exampleString = file_get_contents('/var/www/yore/pages/_domains/chsapp.com/signin/views/nurse.html');

$exampleString = "Blah @chs('nurse') Blah (this)";
//echo "PARSE:" . $exampleString . "\n";
$x = processMacroString($exampleString);
