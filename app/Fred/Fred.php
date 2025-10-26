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

namespace App\Fred;

class Fred
{

    protected $vars = [];

    protected $includes = [];

    protected $modules;
    
    protected $data;

    const BLACKRUSH = "
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
";

    public function __construct()
    {


    }

    public function view($output, $data = [], $modules = false) {

        $this->modules = $modules; // Modules may contain Fred extensions that we will look for
        
        $this->data = $data;

        $output = $this->prep($output); // process strings, constants and variables

        $output = $this->exec($output); // execute @functions
        //$output = $this->processMacroString($output); // execute @functions

        return $output;
    }

    function prep($output) {

        if (empty($output)) return $output;
        // step 1, replace all @aphatwelve( with chr(1) + APHATWELVE
        // Find all possible @functions and replace them with ¢FUNCTIONS

        $continue = true;

        do {

            $strung = $this->getSubstringBetweenSpecialChars($output, '@', '(');

            if (!$strung) {
                $continue = false;
                break;
            }

            if ($this->isAlphaStringLessThan24($strung)) {

                $output = str_replace('@' . $strung . '(', '¢' . strtoupper($strung) . '(', $output);

            } else {

                $output = str_replace('@' . $strung . '(', '£¥' . $strung . '(', $output);

            }

        } while($continue);

        // Step 2, Lets insert any #variables we may have defined

        foreach ($this->vars as $var => $val) {
            $output = str_replace('#' . $var, $val, $output);
        }


        return str_replace('£¥', '@', $output);
    }

    function exec($output) {

        if (empty($output)) return $output;

        // original - hello @say('world') yay

        $split = explode('¢', $output,2); // hello ] ¢ [SAY('world') yay

        $left = $split[0]; // hello ]

        $right = $split[1] ?? false; // [SAY('world') yay

        if ($right) {
            $right = $this->exec($right); // [SAY('world') yay
        } else {
            return $output; // nothing left to do
        }

        $fun = explode(')', $right,2);

        $right = $fun[1];

        $command = $fun[0]; // string(21) "ASSET('images/nl.jpg'"

        $expr = explode('(', $command );

        $head = $expr[0];

        $tail = $expr[1];

        $tail = $this->parseCsvString($tail);

        if ((gettype($tail) == 'array') and (count($tail) == 1)) $tail = $tail[0];

        // Upper Case Fred Directives are reserved for Fred's built-in functions, lower case are for module extensions
        // and used for Blade directives that are not Fred-specific.

        // Fred directives that do not conflict with Blade or module extensions are allowed to be mixed case, but should be avoided if possible.

        switch($head) {
            case 'AUTHOR':
                $command = 'Erik Olson for Blackrush, LLC';
                break;
            case 'MEMORY':
            case 'SESSION':
                $command = $_SESSION[$tail] ?? '';
                break;
            case 'ASSET':
                $command = '/themes/blackrush/' . $tail;
                break;
            case 'BODY':
                $command = $this->data->body;
                break;
            case 'EXAMPLE':
                $command = $this->data->example_fred_var;
                break;
            case 'EDIT':
                $command = "<button class='fred-edit btn btn-success btn-sm' rel='$tail'>Edit</button>";
                break;
            case 'BLACKRUSH':
                $command = self::BLACKRUSH;
                break;
            case 'REQUEST':
                $command = $_REQUEST[$tail] ?? '';
                break;

            case 'EMPTY':

                if (empty($tail[0]))
                    $command = $tail[1];
                else
                    $command = $tail[0];

                break;
            case 'BYTES':
                $command = $this->formatBytes($tail);
                break;
            case 'FORM':
                $command="<form name='fredform1' class='fred_form' method='$tail'><table class='fred_form'>";
                break;
            case 'ENDFORM':
                $command="</table></form>";
                break;
            case 'SUBMIT':
                $command="<tr><td></td><td><input type='submit' value='$tail'></td></tr>";
                break;
            case 'EMAIL':
                $name = $tail[0] ?? '';
                $placeholder = $tail[1] ?? '';
                $title=$tail[2] ?? '';
                $value = $tail[3] ?? '';
                $command = "<tr><th>$title</th><td><input type='email' name='$name' id='$name' placeholder='$placeholder' value='$value'></td></tr>";
                break;
            case 'DATE':
                $name = $tail[0] ?? '';
                $placeholder = $tail[1] ?? '';
                $title=$tail[2] ?? '';
                $value = $tail[3] ?? '';
                $command = "<tr><th>$title</th><td><input type='date' name='$name' id='$name' placeholder='$placeholder' value='$value'></td></tr>";
                break;
            case 'INPUT':
                $name = $tail[0] ?? '';
                $placeholder = $tail[1] ?? '';
                $title=$tail[2] ?? '';
                $value = $tail[3] ?? '';
                $command = "<tr><th>$title</th><td><input type='text' name='$name' id='$name' placeholder='$placeholder' value='$value'></td></tr>";
                break;
            case 'NUMBER':
                $name = $tail[0] ?? '';
                $placeholder = $tail[1] ?? '';
                $title=$tail[2] ?? '';
                $value = $tail[3] ?? '';
                $command = "<tr><th>$title</th><td><input type='number' name='$name' id='$name' placeholder='$placeholder' value='$value'></td></tr>";
                break;
            case 'IMAGE':
                $name = $tail[0] ?? '';
                $placeholder = $tail[1] ?? '';
                $title=$tail[2] ?? '';
                $value = $tail[3] ?? '';
                if ($value) {
                    $command = "<tr><th>$title</th><td><img class='pop-image' style='height:100px' id='$name' alt='$placeholder' src='$value'/></td></tr>";
                } else {
                    $command = "";
                }
                break;
            case 'TEXTAREA':
                $name = $tail[0] ?? '';
                $placeholder = $tail[1] ?? '';
                $title=$tail[2] ?? '';
                $value = $tail[3] ?? '';
                $command = "<tr><th>$title</th><td><textarea class='textarea_$name' name='$name' id='$name' placeholder='$placeholder'>$value</textarea></td></tr>";
                break;
            case 'CHECKBOX':
                $name = $tail[0] ?? '';
                $title=$tail[1] ?? '';
                $value = $tail[2] ?? '';
                $checked = empty($value) ? '':'checked';
                $command = "<tr><th>$title</th><td><input type='checkbox' $checked name='$name' id='$name'></td></tr>";
                break;
            default:

                // SO.. we need to see if any $module classes have a Fred function
                // or an array?  An array of functions??
                $command = null;
                $head = strtolower($head);
                $method = 'fred_' . $head;
                $temp = '';
                if (gettype($this->modules) == 'array') {
                    foreach ($this->modules as $name => $module) {
                        // Check if a module_post(value) exists name of $head and generate a hidden form field for it
                        $name = strtolower($name);
                        if ($name . '_post' == $head) {
                            $command = "<input type='hidden' name='{$name}_post' value='$tail'>";
                            break;
                        }

                        // Check if the fred_ method exists in the module
                        $temp .= "$name:";
                        if (method_exists($module, $method)) {
                            // Call the method and pass $tail as the argument
                            $temp .= "EXISTS";
                            $command = $module->$method($tail);

                            // Just in case the output contains Fred
                            $command = $this->prep($command);
                            $command = $this->exec($command);

                            // If more than 1 module has a fred_ function then we're going to call them all!
                            break; // Actually no lets not but maybe warn?
                        } else {
                            $temp .= "NOT1!";
                        }
                    }
                }

                if ($command === null) $command = '?' . $head . '?' . $method . ':' .  $temp;

        }



        $output = $left . $command . $right;

        return $output;
    }


    function getSubstringBetweenSpecialChars($string, $char1, $char2) { // thanks ChatGPT!
        $start = strpos($string, $char1);
        if ($start === false) {
            return false;
        }

        $end = strpos($string, $char2, $start + 1);
        if ($end === false) {
            return false;
        }

        $length = $end - $start - 1;

        return substr($string, $start + 1, $length);
    }

    function isAlphaStringLessThan24($string) {
        $pattern = '/^[a-zA-Z_]+$/';

        if (strlen($string) <= 24 && preg_match($pattern, $string)) { // thanks ChatGPT!
            return true;
        } else {
            return false;
        }
    }



    function execute($head, $tail) {

        //return "[ RESULT OF do $head with $tail ]"; `

        $ret = null;

        $tail = $this->parseCsvString($tail);

        if (count($tail) == 1) $tail = $tail[0];


        switch(strtoupper($head)) {
            case 'ASSET':
                $ret = '/themes/blackrush/' . $tail;
                break;
            case 'BODY':
                $ret = $this->data->body;
                break;
            case 'EXAMPLE':
                $ret = $this->data->example_fred_var;
                break;
            case 'EDIT':
                $ret = "<button class='fred-edit btn btn-success btn-sm' rel='$tail'>Edit</button>";
                break;
            case 'BLACKRUSH':
                $ret = self::BLACKRUSH;
                break;
            case 'REQUEST':
                $ret = $_REQUEST[$tail] ?? '';
                break;
            case 'SESSION':
                $ret = $_SESSION[$tail] ?? '';
                break;
            case 'EMPTY':
                if (empty($tail[0]))
                    $ret = $tail[1];
                else
                    $ret = $tail[0];

                break;
            case 'BYTES':
                $ret = $this->formatBytes($tail);
                break;

            default:

                // SO.. we need to see if any $module classes have a Fred function
                // or an array?  An array of functions??

                $head = strtolower($head);
                $method = 'fred_' . $head;
                $temp = '';
                if (gettype($this->modules) == 'array') {
                    foreach ($this->modules as $name => $module) {
                        // Check if a module_post(value) exists name of $head and generate a hidden form field for it
                        $name = strtolower($name);
                        if ($name . '_post' == $head) {
                            $ret = "<input type='hidden' name='{$name}_post' value='$tail'>";
                            break;
                        }

                        // Check if the fred_ method exists in the module
                        $temp .= "$name:";
                        if (method_exists($module, $method)) {
                            // Call the method and pass $tail as the argument
                            $temp .= "EXISTS";
                            $ret = $module->$method($tail);

                            // Just in case the output contains Fred
                            $ret = $this->prep($ret);
                            $ret = $this->exec($ret);

                            // If more than 1 module has a fred_ function then we're going to call them all!
                            break; // Actually no lets not but maybe warn?
                        } else {
                            $temp .= "NOT2!";
                        }
                    }
                }

                if ($ret === null) $ret = '?' . $head . '?' . $method . ':' . $temp;
        }
        return $ret;
    }

    function processMacroString($string) {

        // WERE NOT DOING THIS RIGHT NOW


        // Match pattern that accounts for nested parentheses
        //

        //$pattern = '/^(.*?)@(\w+)\((.*)\)(.*)$/s';
        $pattern = '/^(.*?)@(\w+)\(([^)]*)\)(.*)$/s';

        if (preg_match($pattern, $string, $matches)) {
            $beforeAt = $matches[1];           // Everything before '@'
            $keyword = $matches[2];            // Keyword after '@'
            $insideParentheses = $matches[3];  // The content between parentheses (might contain nested ones)
            $afterParentheses = $matches[4];   // Everything after the closing ')'

            // We need to handle the content inside the parentheses for nesting
            //$insideParentheses = $this->matchBalancedParentheses($insideParentheses); // NO? Really? All this does is truncate the TAIL on 1st )??

            // Display the four strings
//            echo "\n<!-- Before @: $beforeAt -->\n";
            echo "\n<!-- Keyword: $keyword-->\n";
            echo "\n<!-- Inside (): $insideParentheses-->\n";
//            echo "\n<!-- After (): $afterParentheses-->\n";

            // Recursively process content inside parentheses if not empty
            if (!empty($insideParentheses)) {
                //echo "\n<!-- -- Recursively processing inside parentheses -- -->\n";
                $insideParentheses = $this->processMacroString($insideParentheses);  // Recursive call
            }

            return $beforeAt . $this->execute($keyword, $insideParentheses) . $afterParentheses;
        } else {
            // No macro pattern found
           // echo "No macro found, string: $string\n";

            return $string;
        }
    }

    function matchBalancedParentheses($str) { // We don't need this ???
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

    function parseCsvString($inputString) {
        // Check if the string contains quotes or commas
        if (strpos($inputString, ',') !== false || strpos($inputString, '"') !== false || strpos($inputString, "'") !== false) {
            // Parse the string as a CSV

            $inputString = str_replace("'", '"', $inputString);
            $parsedArray = str_getcsv($inputString, ',', '"', '\\');
            return $parsedArray;
        } else {
            // If no quotes or commas, return the string itself
            return $inputString;
        }
    }
    function formatBytes($bytes) {
        if ($bytes === 0) return '0B';  // Handle zero case

        $sizes = ['B', 'K', 'M', 'G', 'T', 'P', 'E', 'Z', 'Y'];  // Units for bytes
        $i = floor(log($bytes, 1024));  // Determine the magnitude

        // Format the number with one decimal point and the appropriate unit
        $formattedNumber = round($bytes / pow(1024, $i), 1);

        return $formattedNumber . $sizes[$i];
    }


}
