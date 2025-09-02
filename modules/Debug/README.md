# Debug
Turning this module on adds lots of debugging features to Yore
+ Adds a debug element to every page with useful information
+ Sets the $this->debug flag to true in the controller
+ Turns on all of the possible Php debugging things
+ Logs a bunch of extra shit
+ Puts some kind of obvious warning on the UI that debugging is on with a quick way to turn it off
+ Relaxes security, maybe adds the ability to log in as users, create temporary users
+ Override notifications or irreversable machinations like issuing credentials or charging fees, alerts etc
+ Enables Asserts or tests and things
+ Allows for an alternate Sandbox data source, storage, logger and other resources if they exist
+ Lets module creators do things like disable context menu override, timeouts, show quiz answers, etc

```

        if ($this->debug) { // TODO: Use the Debug module for stuff like this
            $str_data = json_encode($data, JSON_PRETTY_PRINT);
            $uri = $_SERVER['REQUEST_URI'];
            $debug = // make this a template
                "<button class='btn btn-danger btn-sm debug-info-button' onclick=\"$('.debug-info').toggle()\">Debug</button><br/>
                <textarea class='debug-info' style='width:100%; height:500px; display:none;'>
                Domain: {$this->domain}  Site: {$this->site}   Page Name: {$this->name}   Arg1: {$this->arg1}   Arg2: {$this->arg2}   Arg3: {$this->arg3}
                
                URI: $uri
                
                Data: $str_data
                </textarea>
                
                ";


            $output .= $debug;
        }
```