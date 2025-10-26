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
 Copyright (C) 2026, Blackrush LLC, All Rights Reserved
Created by Erik Olson, Tarpon Springs, Florida
For more information, visit BlackrushDrive.com

MIT License

Copyright (c) 2025 Erik Lee Olson for Blackrush, LLC

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


*/


/*

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
use App\Command;
use App\Controller;
use App\Crud\Crud;
use Modules\Orm\Core\DB;
use Modules\Orm\Drivers\PdoMySqlDriver;
use Modules\Orm\Drivers\PdoSqliteDriver;



// Autoload function
spl_autoload_register(function ($class_name) {
    // Define the base directory for the "App" namespace
    $base_dir = __DIR__ . '/../app/';

    // Check if the class is within the "App" namespace
    $namespace = 'App\\';
    if (strncmp($namespace, $class_name, strlen($namespace)) === 0) {
        // Remove the namespace prefix
        $relative_class = substr($class_name, strlen($namespace));

        // Replace the namespace separator with the directory separator
        $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }

    // Map Modules\LCC\Import\ (PSR-4) to modules\LCC\import\src\
    $prefix = 'Modules\\LCC\\Import\\';
    $baseDir = __DIR__ . '/../modules/LCC/import/src/';
    if (strncmp($class_name, $prefix, strlen($prefix)) !== 0) return;
    $relative = substr($class_name, strlen($prefix));
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) require $file;
});


# If we're gonna be using Evo Comm Tech
#use Olsonhost\Ect\Init;

# This gives us the power of many!!
if (file_exists('../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

//require '../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

// LCC importer/reconciler dispatcher
if (isset($argv) && isset($argv[1]) && strpos($argv[1], 'lcc:') === 0) {


# Setup the ORM database connections here
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = (int)(getenv('DB_PORT') ?: 3306);
    $name = getenv('DB_NAME') ?: 'yore';
    $user = getenv('DB_USER') ?: 'heidi';
    $pass = getenv('DB_PASS') ?: 'Mermaid7!!';

    if (true) { // ($mode === 'mysql' || ($host && $name)) {
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
        $pdo = new PDO($dsn, (string)$user, (string)$pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        DB::register('default', new PdoMySqlDriver($pdo), true);

    }

    $cmd = $argv[1];
    $sub = $argv[2] ?? null;
    $path = $argv[3] ?? null;
    $flags = [];
    foreach ($argv as $a) {
        if (strpos($a, '--') === 0) {
            if (strpos($a, '=') !== false) {
                [$k,$v] = explode('=', substr($a,2), 2);
                $flags[$k] = $v;
            } else {
                $flags[substr($a,2)] = true;
            }
        }
    }
    $dry = isset($flags['dry-run']) || isset($flags['dry']);
    $force = isset($flags['force']);
    $chunk = isset($flags['chunk']) ? (int)$flags['chunk'] : 3000;

    try {
        if ($cmd === 'lcc:import') {
            if (!$sub || !$path) { fwrite(STDERR, "Usage: php cli.php lcc:import <livestream|ledger|breaks|weekly> <path> [--dry-run] [--force] [--chunk=3000]\n"); exit(1);}            
            switch (strtolower($sub)) {
                case 'livestream':
                    $imp = new \Modules\LCC\Import\Import\LivestreamImporter();
                    $res = $imp->import($path, ['dry'=>$dry,'force'=>$force,'chunk'=>$chunk]);
                    break;
                case 'ledger':
                    $imp = new \Modules\LCC\Import\Import\LedgerImporter();
                    $res = $imp->import($path, ['dry'=>$dry,'force'=>$force,'chunk'=>$chunk]);
                    break;
                case 'breaks':
                    $imp = new \Modules\LCC\Import\Import\BreaksImporter();
                    $res = $imp->import($path, ['dry'=>$dry,'force'=>$force,'chunk'=>$chunk]);
                    break;
                case 'weekly':
                    // Optional - not implemented fully; reuse LivestreamImporter for now
                    $imp = new \Modules\LCC\Import\Import\LivestreamImporter();
                    $res = $imp->import($path, ['dry'=>$dry,'force'=>$force,'chunk'=>$chunk]);
                    break;
                default:
                    fwrite(STDERR, "Unknown import type: {$sub}\n");
                    exit(2);
            }
            echo json_encode(['summary'=>$res], JSON_PRETTY_PRINT) . "\n";
            exit(0);
        }
        if ($cmd === 'lcc:reconcile') {
            $from = $flags['from'] ?? null;
            $to = $flags['to'] ?? null;
            $rec = new \Modules\LCC\Import\Import\Reconciler();
            $updated = $rec->link($from, $to);
            $outDir = __DIR__ . '/../storage/reports';
            $date = date('Ymd');
            $out = $outDir . "/pnl_{$date}.csv";
            $rec->report($out, $from, $to);
            echo json_encode(['linked_rows'=>$updated,'report'=>$out], JSON_PRETTY_PRINT) . "\n";
            exit(0);
        }
        if ($cmd === 'lcc:freshen') {
            $opts = ['dry' => $dry, 'force' => $force, 'chunk' => $chunk];
            $summaries = [];

            // Livestream
            $imp = new \Modules\LCC\Import\Import\LivestreamImporter();
            $summaries['livestream'] = $imp->import(false, $opts);

            // Ledger
            $imp = new \Modules\LCC\Import\Import\LedgerImporter();
            $summaries['ledger'] = $imp->import(false, $opts);

            // Breaks
            $imp = new \Modules\LCC\Import\Import\BreaksImporter();
            $summaries['breaks'] = $imp->import(false, $opts);

            // Weekly
            if (class_exists('Modules\\LCC\\Import\\Import\\WeeklyImporter')) {
                $imp = new \Modules\LCC\Import\Import\WeeklyImporter();
                $summaries['weekly'] = $imp->import(false, $opts);
            } else {
                // Fallback to LivestreamImporter configured type? We'll reuse livestream; weekly rows will be skipped there.
                $summaries['weekly'] = ['note' => 'WeeklyImporter not present'];
            }

            echo json_encode(['summary' => $summaries], JSON_PRETTY_PRINT) . "\n";
            exit(0);
        }
    } catch (\Throwable $e) {
        fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
        exit(1);
    }
}

doCli();

// Example usage:
//$exampleString = "Hello @dash(test123(nested)) World";
//echo "PARSE:" . $exampleString . "\n";
//$x = processMacroString($exampleString);
//
//var_dump($x);

////echo "\n\n\n";

// Example usage:
/////$exampleString = file_get_contents('/var/www/yore/pages/_domains/chsapp.com/signin/views/nurse.html');

////$exampleString = "Blah @chs('nurse') Blah (this)";
//echo "PARSE:" . $exampleString . "\n";
////$x = processMacroString($exampleString);




function doCli()
{
    $y = $controller = new Command(true);

    $prompt = "Yore> ";
    while (true) {

        // If a command line argument was passed, use it as the input and then clear it
        if (isset($GLOBALS['argv'][1]) && $GLOBALS['argv'][1] != '') {
            $input = $GLOBALS['argv'][1];
            $GLOBALS['argv'][1] = '';
            echo $prompt . $input . PHP_EOL;
        } else {
            // Display the prompt and get user input
            $input = readline($prompt);
        }

        if ($input === 'quit') {
            break;
        }

        if ($input == 'create') {
            $y->create();
            continue;
        }
        if ($input == 'create dry') {
            $y->create(true);
            continue;
        }

        if (strpos($input, 'orm:test') === 0) {
            // Usage:
            //   orm:test                -> auto (MySQL via env, fallback to SQLite)
            //   orm:test mysql          -> force MySQL using env DB_*
            //   orm:test sqlite [path]  -> force SQLite (optional path)
            try {
                $parts = preg_split('/\s+/', trim((string)$input));
                $mode  = isset($parts[1]) ? strtolower($parts[1]) : null;

                $registered = false;
                $using = '';

                if ($mode === 'sqlite') {
                    $path = $parts[2] ?? (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'dev.sqlite');
                    $dir  = dirname($path);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                    $pdo = new PDO('sqlite:' . $path);
                    DB::register('default', new PdoSqliteDriver($pdo), true);
                    $using = 'sqlite(' . $path . ')';
                    $registered = true;
                } else {
                    $host = getenv('DB_HOST') ?: 'localhost';
                    $port = (int)(getenv('DB_PORT') ?: 3306);
                    $name = getenv('DB_NAME') ?: 'yore';
                    $user = getenv('DB_USER') ?: 'heidi';
                    $pass = getenv('DB_PASS') ?: 'Mermaid7!!';

                    if ($mode === 'mysql' || ($host && $name)) {
                        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
                        $pdo = new PDO($dsn, (string)$user, (string)$pass, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]);
                        DB::register('default', new PdoMySqlDriver($pdo), true);
                        $using = 'mysql(' . $host . '/' . $name . ')';
                        $registered = true;
                    }
                }

                if (!$registered) {
                    // Fallback to SQLite dev file
                    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'dev.sqlite';
                    $dir  = dirname($path);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                    $pdo = new PDO('sqlite:' . $path);
                    DB::register('default', new PdoSqliteDriver($pdo), true);
                    $using = 'sqlite(' . $path . ')';
                }

                // Smoke test via raw SELECT
                $rows = DB::raw('SELECT 1 AS ok');
                $ok = is_array($rows) && isset($rows[0]['ok']) && (string)$rows[0]['ok'] === '1';
                echo "ORM connection registered: {$using}\n";
                echo $ok ? "Connectivity: OK (SELECT 1)\n" : "Connectivity: Unexpected result from SELECT 1\n";

                // Optional: try a tiny count on a common table if it exists
                try {
                    // MySQL and SQLite compatible existence probe
                    $hasOrders = false;
                    try {
                        // Try a COUNT directly; if table missing, this throws
                        $probe = DB::raw('SELECT COUNT(*) AS c FROM orders');
                        if (is_array($probe) && isset($probe[0]['c'])) {
                            $hasOrders = true;
                            echo 'orders count: ' . $probe[0]['c'] . "\n";
                        }
                    } catch (Throwable $te) {
                        // ignore; table likely absent
                    }
                    if (!$hasOrders) {
                        echo "Tip: Create a table (e.g., 'orders') to test model queries.\n";
                    }
                } catch (Throwable $e2) {
                    // Silent; not critical for connectivity test
                }

                echo "Done.\n";
            } catch (Throwable $e) {
                echo 'ORM test failed: ' . $e->getMessage() . "\n";
            }
            continue;
        }

        if ($input === 'crud') {
            // If the 2nd command line argument was passed, use it as the table name

            if (isset($GLOBALS['argv'][2]) && $GLOBALS['argv'][2] != '') {
                $input = $GLOBALS['argv'][2];
                $GLOBALS['argv'][2] = '';
                echo "Site (unprefixed table name): " . $input . PHP_EOL;
            } else {
                // Display the prompt and get user input
                $input = readline("Site (unprefixed table name): "); // if ($input == '') $input = 'users';
            }
            if ($input == '') continue;
            $site = strtolower(trim($input));

            // If the 3RD command line argument was passed, use it as the table name
            if (isset($GLOBALS['argv'][3]) && $GLOBALS['argv'][3] != '') {
                $input = $GLOBALS['argv'][3];
                $GLOBALS['argv'][3] = '';
                echo "Module Name (prefix and theme): " . $input . PHP_EOL;
            } else {
                // Display the prompt and get user input
                $input = readline("Module Name (prefix and theme) <lcc>: "); if ($input == '') $input = 'lcc';
            }
            if ($input == '') continue;
            $prefix = strtolower(trim($input));

            $input = readline("Table <" . $prefix .'_' . $site . ">: "); if ($input == '') $input = $prefix .'_' . $site;
            if ($input == '') continue;
            $table = strtolower(trim($input));

            $input = readline("Domain Name <app.luxecardclub.com>: "); if ($input == '') $input = 'app.luxecardclub.com';
            if ($input == '') continue;
            $domain = strtolower(trim($input));

            $C = new Crud($controller, $domain, $table, $site, $prefix);

            echo "Done " . PHP_EOL;
            exit;

        }



        try {
            // Example usage:
            // Wow, ChatGPT wrote me a recursive descent parser with functions
                //$input = "(1 + 2) * 3 - 4 / 2";
                //$input = "abs(-5) + iif(0) + iif(3)";
                //$input = "abs(-5.5) + iif(0) + iif(3) + (3.14 * 2) - (-1.25)";

                //$result = parse_expression($input);
                //echo "Result: " . $result . "\n";  // Output: Result: 7
            eval($input);
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
