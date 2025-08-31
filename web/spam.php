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

function doSpam()
{

    $y = $controller = new Controller(true);

    //$sql = "SELECT * FROM slim.doctors WHERE status = 99 AND email != '' LIMIT 5";
    //$sql = "SELECT * FROM slim.atties WHERE status = 99 AND email != '' and county IN ('palm beach','martin') LIMIT 5";


    //$sql = "SELECT * FROM slim.chiros WHERE status != 99 AND email != '' LIMIT 5";

    $sql = "SELECT email, id FROM slim.chiropractors WHERE email_status = 3 and email != '' LIMIT 7";

    $rows = $y->database->sql($sql)->fetchAll();

    //var_dump($rows);exit;

    $ctr = 0;

    foreach ($rows as $row) {

        $ctr++;

        $body = '';

//        foreach ($row as $var => $val) {
//
//            $body .= "$var: <b>$val</b><br/>\n";
//
//        }

//
//        $body = "<html>
//                <head></head>
//                <body>
//                <center>
//                    <img src=\"cid:callpaulbanner\" alt=\"Better Call Paul logo\">
//                    <h4>Official Launch of Applebaum Accident Group</h4>
//                    <p>Inured in car accident or slip and fall? Need an attorney?</p>
//                    <p><b>You Better Call Paul</b></p>
//                    <p>We're excited to introduce Applebaum Accident Group, your trusted attorney referral
//                    service. We connect you with top personal injury lawyers who will fight to get you the
//                    maximum compensation you deserve</p>
//                    </center>
//                    <ul>
//                    <li>No upfront costs</li>
//                    <li>Get matched with the best attorney for your case</li>
//                    <li>Free consultation - no obligation</li>
//                    </ul>
//                    <center>
//                    <p>Don't waste time searching - let use connect you with the right attorney today!</p>
//                    <h1>(855) CALL-PAUL<br/><small>(855) 255-5728</small><br><small><b>www.855CALLPAUL.com</b></small></h1>
//                    </center>
//                </body>
//             </html>";

        $body = '<html>
                <head></head>
                <body>
<p>Attention all Chiropractor/MRI Facilities,</p>
<p>Below you will find the steps to extend the session time on the ECW kiosk app on the iPad provided.</p>
<p>Extend iPad Screen time out:</p>
<ul>
<li>Step 1. Go to "Settings"
<li>Step 2. Select "Display and Brightness"
<li>Step 3: Scroll down and select "Auto-lock"
<li>Step 4: Select "Never"
 </ul>
<p>After these steps are complete, please follow these steps next:</p>
<ul>
<li>Step 1. Go To "Settings"
<li>Step 2. In the search bar type in "Eclinical Works"
<li>Step 3. Select the "Eclinical Works Icon"
<li>Step 4. Scroll down and select "App Timeout"
<li>Step 5. Scroll Down and select "30 Minutes"
</ul>
<p>Please keep in mind that once you swipe out or close the app you WILL be prompted to log back in for security reasons.</p>
<p>Thank you!</p>
<p>TeleEMC</p>
<p>If you need any assistance please call us at (866) 611- 4362 option #2</p>
                </body>
             </html>';

        //$subject = "Senior Care with In-Home Services: First Health Homecare Servicing Palm Beach County";
        //$subject = "TeleEMC sister company is proud to announce the launch of 855-CALL-PAUL";
        $subject = "ECLINICAL WORKS EXTEND SESSION TIMEOUT INSTRUCTIONS";

        $recipients = [

//            'mechickaboola@gmail.com',
//            'erikolson1965@gmail.com',
//            'blackrushdrive@gmail.com',
//            //'jeffreyapplebaum@gmail.com',
//            'admin@teleemc.com',
//            'jeff@teleemc.com',
//            'mike@teleemc.com',

//            'jappblebaum@bellsouth.net',
//
//            'buckstein001@gmail.com',
//            'mabresolve@aol.com',
//            'valerie@vzucker.com',


            $row['email']
        ];

        //$addEmbeddedImage=['newhomehealth_christmas.png', 'newhomehealthimage', 'newhomehealth_christmas.png', 'base64', 'image/png'];
        //$addEmbeddedImage=['call_paul_banner.png', 'callpaulbanner', 'call_paul_banner.png', 'base64', 'image/png'];

        //$r = $y->mail->send($recipients, $subject, $body, $addEmbeddedImage);
        $r = $y->mail->send($recipients, $subject, $body);

        echo "\n======= {$row['email']} =========\n";

        echo "\n======= $r =========\n";
//exit('xxxxxxxxxx');
        $y->database->sql('UPDATE slim.chiropractors SET email_status=4 WHERE id=? or email=?', [$row['id'],$row['email']]);
        //$y->database->sql('UPDATE slim.atties SET status=100 WHERE id=? or email=?', [$row['id'],$row['email']]);

    }

    echo "\n======= $ctr =========\n";
}


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
});
# If we're gonna be using Evo Comm Tech
#use Olsonhost\Ect\Init;

# This gives us the power of many!!
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}

//require '../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');


doSpam();

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



