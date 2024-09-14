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

class Fredx
{

    protected $vars = [];

    protected $includes = [];

    protected $modules;

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

        $output = $this->prep($output, $data); // process strings, constants and variables

        $output = $this->exec($output, $data); // execute @functions

        return $output;
    }

    function prep($output, $data) {
        // step 1, replace all @alphatwelve( with chr(1) + ALPHA_TWELVE
        // Find all possible @functions and replace them with ¢FUNCTIONS
        // I guess the purpose of this is to enforce @ALPHA_TWELVE()
        // and ignore anything

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

    function exec($output, $data) {

        // original - hello @say('world') yay

        $split = explode('¢', $output,2); // hello ] ¢ [SAY('world') yay

        $left = $split[0]; // hello ]

        $right = $split[1] ?? false; // [SAY('world') yay

        if ($right) {
            $right = $this->exec($right, $data); // [SAY('world') yay
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
                $command = $data->body;
                break;
            case 'EXAMPLE':
                $command = $data->example_fred_var;
                break;
            case 'EDIT':
                $command = "<button class='fred-edit btn btn-success btn-sm' rel='$tail'>Edit</button>";
                break;
            case 'BLACKRUSH':
                $command = Self::BLACKRUSH;
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
                        if ($name . 'post' == $head) {
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
        $pattern = '/^[a-zA-Z]_+$/';

        if (strlen($string) <= 12 && preg_match($pattern, $string)) { // thanks ChatGPT!
            return true;
        } else {
            return false;
        }
    }

}
