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
Copyright (C) 2024, Blackrush LLC, All Rights Reserved
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

/app/Controller.php - This is the main website program. All HTML endpoints are processed by this

*/


 ######   #######  ##     ## ##     ##    ###    ##    ## ########
##    ## ##     ## ###   ### ###   ###   ## ##   ###   ## ##     ##
##       ##     ## #### #### #### ####  ##   ##  ####  ## ##     ##
##       ##     ## ## ### ## ## ### ## ##     ## ## ## ## ##     ##
##       ##     ## ##     ## ##     ## ######### ##  #### ##     ##
##    ## ##     ## ##     ## ##     ## ##     ## ##   ### ##     ##
 ######   #######  ##     ## ##     ## ##     ## ##    ## ########

namespace App;

class Command extends Library {

    /**
     * @var mixed|string
     */
    public $page;

    public $data, $json;

    public $header, $footer, $html, $js = [], $css = [], $output;

    public $env;

    // public vars in parent class $params, $site, $name, $arg1, $arg2, $arg3, $is_debug = true, $is_remote = false, $is_module=false;
    /**
     * @var false|mixed
     */
    private mixed $dry;

    /**
     *
     */
    public function __construct($cli = true) {

        //parent::__construct($cli); // Library does not have a __construct method

        $this->init();

    }

    ######  ########  ########    ###    ######## ########
   ##    ## ##     ## ##         ## ##      ##    ##
   ##       ##     ## ##        ##   ##     ##    ##
   ##       ########  ######   ##     ##    ##    ######
   ##       ##   ##   ##       #########    ##    ##
   ##    ## ##    ##  ##       ##     ##    ##    ##
    ######  ##     ## ######## ##     ##    ##    ########
    /**
     * @param $dry
     * @return void
     */
    public function create($dry = false) {

        $this->dry = $dry;

        if ($this->dry) {
            echo "Dry run mode - no changes will be made.\n";
        }

        // create domain interactive
        echo "This is the Command class\n";

        echo "\nEnter the NEW domain name (example.com): ";
        $handle = fopen ("php://stdin","r");
        $domain = trim(fgets($handle));
        fclose($handle);
        if(empty($domain)){
            echo "No domain name entered. Exiting.\n";
            return;
        }

        echo "You entered: $domain\n";
        // validate domain name
        if(!filter_var('http://'.$domain, FILTER_VALIDATE_URL)){
            echo "Invalid domain name. Exiting.\n";
            return;
        }
        // check if domain already exists
        if($this->domainExists($domain)){
            echo "Domain already exists. Exiting.\n";
            return;
        }
        // create domain
        if($this->addDomain($domain)){
            echo "Domain $domain created successfully.\n";
        } else {
            echo "Failed to create domain $domain.\n";
        }

        // Display a list of folder names that exist inder ../pages/_domains as a numbered list and ask user to select one to copy or press ENTER to create a blank site
        $domain_folders = array_filter(glob(__DIR__ . '/../pages/_domains/*'), 'is_dir');
        $domain_names = array_map('basename', $domain_folders);
        echo "\nAvailable domain templates:\n";
        foreach($domain_names as $index => $name){
            echo ($index + 1) . ". $name\n";
        }
        echo "\nEnter the number of the domain template to copy or press ENTER to create a blank site: ";
        $handle = fopen ("php://stdin","r");
        $input = trim(fgets($handle));
        fclose($handle);

        // Display the selected template or blank site message and ask the user to confirm with Y or n

        echo "\nYou selected: ";
        if(!empty($input) && is_numeric($input) && isset($domain_names[$input - 1])){
            $template = $domain_names[$input - 1];
            echo "$template\n";
        } else {
            echo "Blank site\n";
        }
        echo "Confirm? (Y/n): ";
        $handle = fopen ("php://stdin","r");
        $confirm = trim(fgets($handle));
        fclose($handle);
        if(strtolower($confirm) != 'y' && !empty($confirm)){
            echo "Cancelled. Exiting.\n";
            return;
        }

        if(!empty($input) && is_numeric($input) && isset($domain_names[$input - 1])){
            $template = $domain_names[$input - 1];
            // copy template to new domain folder
            $source = __DIR__ . '/../pages/_domains/' . $template;
            $destination = __DIR__ . '/../pages/_domains/' . strtolower($domain);
            $this->recurseCopy($source, $destination);
            echo "Domain $domain created from template $template successfully.\n";
        } else {
            echo "Creating blank site for domain $domain.\n";
            $destination = __DIR__ . '/../pages/_domains/' . strtolower($domain);
            // create a blank index.php file in the new domain folder
            $index_file = $destination . '/index.php';
            file_put_contents($index_file, "<?php\n// This is the index file for $domain\n");
            echo "Blank site created successfully.\n";
        }

        // Check to see if there is a folder named "modules" under the NEW domain folder
        $modules_folder = __DIR__ . '/../pages/_domains/' . strtolower($domain) . '/modules';
        echo "\nChecking for modules folder at $modules_folder\n";
        if(is_dir($modules_folder)){
            echo "\nFound modules folder.\n";
            // Iterate through each subfolder under ../pages/_domains/modules and find each instance of settings.json

            $global_modules_folder = __DIR__ . '/../pages/_domains/' . strtolower($domain) . '/modules';
            echo "\nChecking for modules in global modules folder: $global_modules_folder\n";
            //Checking for modules in global modules folder: /var/www/yore/app/../pages/_domains/modules
            $module_folders = array_filter(glob($global_modules_folder . '/*'), 'is_dir');
            var_dump($module_folders);
            foreach($module_folders as $module_folder){
                echo "\nChecking for settings.json in $module_folder\n";
                $settings_file = $module_folder . '/settings.json';
                if(file_exists($settings_file)){
                    echo "\nFound settings.json in module " . basename($module_folder) . "\n";
                    // copy settings.json to the new domain folder under modules
                    if(!is_dir($modules_folder)){
                        if (!$this->dry) {
                            echo "mkdir $modules_folder\n";
                            mkdir($modules_folder, 0775, true);
                        }
                        else {
                            echo "(dry run) mkdir $modules_folder\n";
                        }
                    }
                    // Read settings.json and decode it
                    $settings = json_decode(file_get_contents($settings_file), true);
                    // Iterate through the settings and prompt the user to confirm or change each value
                    foreach($settings as $key => $value){
                        echo "\n$key: $value\n";
                        echo "Enter new value or press ENTER to keep current value: ";
                        $handle = fopen ("php://stdin","r");
                        $new_value = trim(fgets($handle));
                        fclose($handle);
                        if(!empty($new_value)){
                            $settings[$key] = $new_value;
                        }
                    }
                    // Write the settings to the new domain folder
                    $new_settings_file = $modules_folder . '/' . basename($module_folder) . '/settings.json';
                    if (!$this->dry) {
                        echo "file_put_contents $new_settings_file\n";
                        file_put_contents($new_settings_file, json_encode($settings, JSON_PRETTY_PRINT));
                    }
                    else {
                        echo "(dry run) file_put_contents $new_settings_file\n";
                    }

                    echo "Module settings for " . basename($module_folder) . " copied to $new_settings_file\n";

                }  else {
                    echo "No settings.json found in module " . basename($module_folder) . "\n";
                }
            }
        }
        echo "Done.\n";
    }

    /**
     * @param string $domain
     * @return bool
     */
    private function domainExists(string $domain)
    {
        // Check if a folder matching the lower-cased domain name already exists under ../pages/_domains
        $domain_folder = __DIR__ . '/../pages/_domains/' . strtolower($domain);
        if(is_dir($domain_folder)){
            return true;
        }
        return false;
    }

    /**
     * @param string $domain
     * @return bool
     */
    private function addDomain(string $domain)
    {
        // Create a folder matching the lower-cased domain name under ../pages/_domains
        $domain_folder = __DIR__ . '/../pages/_domains/' . strtolower($domain);
        if (!$this->dry) {
            echo "mkdir $domain_folder\n";
            if (!mkdir($domain_folder, 0775, true)) {
                return false;
            }
        } else {
            echo "(dry run) mkdir $domain_folder\n";
        }
        return true;
    }

    /**
     * @param string $source
     * @param string $destination
     * @return void
     */
    private function recurseCopy(string $source, string $destination)
    {
        $dir = opendir($source);
        if (!$this->dry) {
            echo "mkdir $destination\n";
            mkdir($destination);
        } else {
            echo "(dry run) mkdir $destination\n";
        }
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($source . '/' . $file) ) {
                    $this->recurseCopy($source . '/' . $file, $destination . '/' . $file);
                }
                else {
                    if (!$this->dry) {
                        echo "copy $source/$file to $destination/$file\n";
                        copy($source . '/' . $file, $destination . '/' . $file);
                    } else {
                        echo "(dry run) copy $source/$file to $destination/$file\n";
                    }
                }
            }
        }
        closedir($dir);
    }
}
