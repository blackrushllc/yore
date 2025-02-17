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
        // step 1, replace all @aphatwelve( with chr(1) + APHATWELVE
        // Find all possible @functions and replace them with ¢FUNCTIONS

        $continue = true;

        do {

            $strung = $this->getSubstringBetweenSpecialChars($output, '@', '(');

            if (!$strung) {
                $continue = false;
                break;
            }

            if ($this->isAlphaStringLessThan12($strung)) {

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

        // TODO: handle literals, vars, etc.  Right now just assuming a quoted string and that's all
        $tail = str_replace(['"', "'"], '', $tail); // just remove quotes

        switch($head) {
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
            case 'SESSION':
                $command = $_SESSION[$tail] ?? '';
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
                            // If more than 1 module has a fred_ function then we're going to call them all!

                            break; // Actually no lets not but maybe warn?
                        } else {
                            $temp .= "NOT!";
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

    function isAlphaStringLessThan12($string) {
        $pattern = '/^[a-zA-Z_]+$/';

        if (strlen($string) <= 12 && preg_match($pattern, $string)) { // thanks ChatGPT!
            return true;
        } else {
            return false;
        }
    }



    function execute($head, $tail) {

        //return "[ RESULT OF do $head with $tail ]";

        $ret = null;

        // TODO: handle literals, vars, etc.  Right now just assuming a quoted string and that's all
        $tail = str_replace(['"', "'"], '', $tail); // just remove quotes

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
                            // If more than 1 module has a fred_ function then we're going to call them all!

                            break; // Actually no lets not but maybe warn?
                        } else {
                            $temp .= "NOT!";
                        }
                    }
                }

                if ($ret === null) $ret = '?' . $head . '?' . $method . ':' . $temp;
        }
        return $ret;
    }

    function processMacroString($string) {
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

}
