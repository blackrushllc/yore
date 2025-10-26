<?php


$targetDir = 'webhook_data';

echo "\nScanning...\n";
$result = [];
$filespec = [];
foreach (scandir($targetDir) as $item) {



    if ($item === '.' || $item === '..') {
        continue;
    }

    $path = $targetDir . DIRECTORY_SEPARATOR . $item;

    if (is_dir($path)) {
        echo "Path: $item\n";
        continue;
    }

    echo "File: $item\n";

    $result[$item] = file_get_contents($path);
    $filespec[$item] = $path;
}


echo "\nUploading...\n";

// Database credentials
$host = 'localhost';
$dbname = 'yore';
$user = 'heidi';
$pass = 'Mermaid7!!';


$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


foreach ($result as $file => $data) {

    $data = json_decode($data, true);
    $path = $filespec[$file];

    if (!$data) {
        rename($path, 'webhook_data/bad/' . $file);
        echo "Bad Data: $file\n";
        continue;
    }

    if (!isset($data['__submission']) and !isset($data['first_name'])){
        rename($path, 'webhook_data/bad/' . $file);
        echo "Not a __submission or first_name in: $file\n";
        continue;
    }

    echo "There is data in: $file\n";

    $mp3File = str_replace('json', 'mp3', $file);
    $mp3 = false;

    try {

        $sub = $data['__submission'] ?? [];
        $transferred_to = null;
        if (isset($data['names'])) $names = $data['names']; else $names = null;
        if (isset($data['checkbox'])) $checkbox = $data['checkbox']; else $checkbox = null;
        if (isset($data['address_1'])) $address = $data['address_1']; else $address = null;
        if (isset($data['language'])) $language = $data['language']; else $language = null;
        if (isset($data['input_text_1'])) $name = $data['input_text_1']; else $name = null;
        if (isset($data['input_text_1'])) $bar = $data['input_text_1']; else $bar = null;
        if (isset($data['input_text_2'])) $acounty = $data['input_text_2']; else $acounty = null;
        if (isset($data['input_text'])) $county = $data['input_text']; else $county = null;
        if (isset($data['input_text'])) $firm = $data['input_text']; else $firm = null;
        if (isset($data['message'])) $message = $data['message']; else $message = null;
        if (isset($data['datetime_1'])) $doa = $data['datetime_1']; else $doa = null;
        if (isset($data['datetime'])) $seen = $data['datetime']; else $seen = null;
        if (isset($data['input_radio'])) $is_fault = $data['input_radio']; else $is_fault = null;
        if (isset($data['input_radio_1'])) $is_seen = $data['input_radio_1']; else $is_seen = null;
        if (isset($data['phone'])) $phone = $data['phone']; else $phone = null;
        if (isset($data['email'])) $email = $data['email']; else $email = null;
        if (isset($data['phone_1'])) $xfer_english = $data['phone_1']; else $xfer_english = null;
        if (isset($data['phone_2'])) $xfer_spanish = $data['phone_2']; else $xfer_spanish = null;
        if (isset($data['file-upload'])) $files = $data['file-upload']; else $files = null;
        if (isset($data['datetime'])) $datetime = $data['datetime']; else $datetime = null;
        if (isset($data['datetime'])) $datetime = $data['datetime']; else $datetime = null;
        if (isset($data['datetime'])) $datetime = $data['datetime']; else $datetime = null;
        if (isset($sub['form_id'])) $form_id = $sub['form_id']; else $form_id = null;
        if (isset($sub['ip'])) $ip = $sub['ip']; else $ip = null;

        $address1 = $address2 = $city = $state = $zip = $image1 = $image2 = $image3 = $image4 = $image5 = $first_name = $last_name = null;

        if (isset($data['first_name'])) $first_name = $data['first_name'];
        if (isset($data['last_name'])) $last_name = $data['last_name'];
        if (isset($data['address'])) $address1 = $data['address'];
        if (isset($data['county'])) $county = $data['county'];
        if (isset($data['city'])) $city = $data['city'];
        if (isset($data['state'])) $state = $data['state'];
        if (isset($data['zip'])) $zip = $data['zip'];
        if (isset($data['zipcode'])) $zip = $data['zipcode'];
        if (isset($data['zip_code'])) $zip = $data['zip_code'];
        if (isset($data['email'])) $email = $data['email'];
        if (isset($data['phone_number'])) $phone = $data['phone_number'];
        if (isset($data['seen_by_medical_professional'])) $is_seen = strtolower($data['seen_by_medical_professional']);
        if (isset($data['at_fault'])) $is_fault = strtolower($data['at_fault']);
        if (isset($data['transferred_to'])) $transferred_to = strtolower($data['transferred_to']);
        $atty_id=0;
        if (strpos($transferred_to, 'krulik') > 0) $atty_id=7;
        if (strpos($transferred_to, 'alper') > 0) $atty_id=6;
        if (strpos($transferred_to, 'lewenz') > 0) $atty_id=6;


        if (isset($data['accident_type'])) $message = $data['accident_type'];
        if (isset($data['date_of_accident'])) {
            $message .= ' ' . $data['date_of_accident'];
            // They are sending us a shitty date
            $form_id = 0;
        }
        if (isset($data['international_phone_number'])) {
            $message .= ' ' . $data['international_phone_number'];
        }

        $city = str_replace(['+'],' ', $city);
        $address1 = str_replace(['+'],' ', $address1);
        $address2 = str_replace(['+'],' ', $address2);
        $first_name = str_replace(['+'],' ', $first_name);
        $last_name = str_replace(['+'],' ', $last_name);

        $phone = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);
        $zip = filter_var($zip, FILTER_SANITIZE_NUMBER_INT);

        if (!empty($first_name) or !empty($last_name)) $name = "$first_name $last_name";

        if (isset($data['call_recording'])) {
            $mp3 = decode_base64($data['call_recording']);
            $mp3 = s3_get_contents($mp3);
        }

        if (empty($mp3)) $mp3File = '';
        if ($mp3 === null) $mp3File = '';

        //var_dump($mp3File); exit;


        /*
        {
        "first_name": "Yahaira",
        "last_name": "answering service care",
        "address": "441 S State Rd 7MargateFL33068",
        "county": "Other",
        "email": "yahaira@answeringservicecare.com",
        "phone_number": "(954) 969-6227;6227",
        "date_of_accident": "11\/7",
        "seen_by_medical_professional": "Yes",
        "at_fault": "No",
        "accident_type": "test",
        "international_phone_number": "+12356892",
        "call_recording": "https:\/\/answering-service-care.s3.us-west-2.amazonaws.com\/uploads\/signalwire\/calls\/recordings\/10657\/288449\/523e735c-0b5c-11f0-92e8-02420aef8ba1-1743115187047235.mp3?X-Amz-Expires=600&X-Amz-Date=20250327T224550Z&X-Amz-Security-Token=IQoJb3JpZ2lu
        }
        */


        if ($names) {
            if (isset($names['first_name'])) $first_name = $names['first_name']; else $first_name = null;
            if (isset($names['last_name'])) $last_name = $names['last_name']; else $last_name = null;
        }

        if ($address) {
            if (isset($address['address_line_1'])) $address1 = $address['address_line_1']; else $address1 = null;
            if (isset($address['address_line_2'])) $address2 = $address['address_line_1']; else $address2 = null;
            if (isset($address['city'])) $city = $address['city']; else $city = null;
            if (isset($address['state'])) $state = $address['state']; else $state = null;
            if (isset($address['zip'])) $zip = $address['zip']; else $zip = null;
        }

        if ($files) {
            if (isset($files[0])) $image1 = $files[0]; else $image1 = null;
            if (isset($files[1])) $image2 = $files[1]; else $image2 = null;
            if (isset($files[2])) $image3 = $files[2]; else $image3 = null;
            if (isset($files[3])) $image4 = $files[3]; else $image4 = null;
            if (isset($files[4])) $image5 = $files[4]; else $image5 = null;
        }



        $leadFields = "
        `atty_id`,
        `name`,
        `address`,
        `city`,
        `state`,
        `zip`,
        `county`,
        `email`,
        `phone`,
        `language`,
        `is_seen`,
        `is_fault`,
        `form_id`,
        `date_of_accident`,
        `date_seen`,
        `transferred_to`,
        `notes`,
        `image_1`,
        `image_2`,
        `image_3`,
        `image_4`,
        `image_5`,
        `ip`,
        `file`,
        `mp3file`,
        
    ";

        $doa = $doa ? DateTime::createFromFormat('m/d/Y', $doa) : null;

        $doa = $doa ? $doa->format('Y-m-d') : null;

        $seen = $seen ? DateTime::createFromFormat('m/d/Y', $seen) : null;

        $seen = $seen ? $seen->format('Y-m-d') : null;

        $leadData = [
            $atty_id,
            $name,
            $address1,
            $city,
            $state,
            $zip,
            $county,
            $email,
            $phone,
            $language,
            $is_seen == 'yes' ? 1 : 0,
            $transferred_to,
            $is_fault == 'yes' ? 1 : 0,
            $form_id,
            $doa,
            $seen,
            $message,
            $image1,
            $image2,
            $image3,
            $image4,
            $image5,
            $ip,
            $file,
            $mp3File,

        ];
        $attyFields = "
        `username`,
        `password`,
        `first_name`,
        `last_name`,
        `firm_name`,
        `address`,
        `addr2`,
        `city`,
        `state`,
        `zip`,
        `county`,
        `email`,
        `phone`,
        `xfer_phone_english`,
        `xfer_phone_spanish`,
        `bar_number`,
        `ip`,
        `is_member`,
        `is_good`
    ";
        $attyData = [
            'user' . uniqid(),
            'password',
            $first_name,
            $last_name,
            $firm,
            $address1,
            $address2,
            $city,
            $state,
            $zip,
            $acounty,
            $email,
            $phone,
            $xfer_english,
            $xfer_spanish,
            $bar,
            $ip,
            2,
            3
        ];

        //// Connect to the database


        switch ($form_id) {

            case 15: // Attorney / English
                echo "Insert Attorney $firm\n";
                $placeHolders = generatePlaceholders($attyData);
                $sql = "INSERT INTO arc_attorneys ($attyFields) VALUES ($placeHolders)";
                $stmt = $pdo->prepare($sql);
                $r = $stmt->execute($attyData);
                rename($path, 'webhook_data/done/' . $file);
                break;

            case 14: // Lead / English

                //var_dump($leadData);exit;
                echo "Insert English Lead $name\n";
                $placeHolders = generatePlaceholders($leadData);
                $sql = "INSERT INTO arc_leads ($leadFields) VALUES ($placeHolders)";
                $stmt = $pdo->prepare($sql);
                $r = $stmt->execute($leadData);
                rename($path, 'webhook_data/done/' . $file);
                break;

            case 10: // Lead / Spanish
                echo "Insert Spanish Lead $name\n";
                $placeHolders = generatePlaceholders($leadData);
                $sql = "INSERT INTO arc_leads ($leadFields) VALUES ($placeHolders)";
                $stmt = $pdo->prepare($sql);
                $r = $stmt->execute($leadData);
                rename($path, 'webhook_data/done/' . $file);
                break;

            case 0: // Lead / Call Center

                //var_dump($leadData);exit;
                echo "Insert Call Center Lead $name\n";
                $placeHolders = generatePlaceholders($leadData);
                $sql = "INSERT INTO arc_leads ($leadFields) VALUES ($placeHolders)";
                //var_dump($sql); var_dump($leadData); exit;
                $stmt = $pdo->prepare($sql);
                $r = $stmt->execute($leadData);
                rename($path, 'webhook_data/cc/' . $file);

                if ($mp3 != false) {
                    file_put_contents ('webhook_data/cc/' . $mp3File, $mp3);
                }

                break;

            default:
                echo "UNKNOWN FORM ID!!\n";
                var_dump($form_id);
                echo "\n";
                rename($path, 'webhook_data/unknown/' . $file);
        }

    } catch(\Exception $e) {
        rename($path, 'webhook_data/error/' . $file);
        echo "\nERROR!\n" . $e->getMessage() . "\n";
    }

}



echo "\nDone\n";


function generatePlaceholders(array $data) {
    return implode(',', array_fill(0, count($data), '?'));
}


function s3_get_contents(string $signedUrl): string|false {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $signedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // In case of redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Optional: timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Optional: verify SSL
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // Treat HTTP errors as failure

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        $data = false;
    }

    curl_close($ch);

    return $data;
}

function decode_base64(string $encoded): string|false {
    $decoded = base64_decode($encoded, false); // true enables strict mode
    if ($decoded === false) {
        // Optionally log or handle the error
        error_log("Invalid Base64 string.");
    }
    return $decoded;
}