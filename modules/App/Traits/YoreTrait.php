<?php
namespace Modules\App\Traits;


use App\Controller;

/**
 *
 */
trait YoreTrait
{


    /**
     * @param $value
     * @return void
     */
    function yore_module_post(Controller $controller, $value) {
        // This method is called if a form is being posted, with whatever we wanted passed to us from the form in $value
        // Right after this module has been yore_module_init()'d, this method is called if a form is being
        // posted that referenced this module with @modulename_post('value') in the view, which would translate
        // to "<input type='hidden' name='post_modulename_post' value='value'>", which is meant to trigger this
        // method in this module when the form is posted.  We can do whatever we want to with that, or do nothing

        if ($controller->hasRole(['readonly', 'guest'])) {
            $controller->flash("This user cannot save data");
            return;
        }

        $action = $controller->request('action');

        switch($value) {

            case 'some-form':
                $sql = "UPDATE app_yw_blah SET blah=? WHERE id=?";
                $params = [
                    $_REQUEST['blah'],
                ];
                $this->controller->database->sql($sql, $params);
                $controller->flash("Blah Blah Updated");
                break;

            case 'delfile':
                $file_id = $_REQUEST['file_id'] ?? "no file";

                $sql = "DELETE FROM app_yw_uploaded_files WHERE id=?";

                $stmt = $this->controller->database->sql($sql, [ $file_id ]);

                break;

            case 'upload':

                $id = $this->controller->arg1;

                $user_id = $_SESSION['user_id'];

                if (!$id or !$user_id) {
                    $this->controller->abort(500, 'Invalid ID');
                }

                $reference=$id;
                $category=$_REQUEST["category"] ?? "General"; // TODO: Should we tag/categorize this file somehow?

                $targetDir = __DIR__ . '/uploaded_files/';

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                if (isset($_FILES['fileToUpload'])) {

                    $controller->database->uploadAppFileToDatabase($_FILES['fileToUpload'], $reference, $category);

                    $targetFile = $targetDir . basename($_FILES['fileToUpload']['name']);

                    // Check if file upload encountered any errors
                    if ($_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
                        // Move the uploaded file to the uploaded_files directory
                        if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile)) {
                            $controller->success("The file " . htmlspecialchars(basename($_FILES['fileToUpload']['name'])) . " has been uploaded.");
                        } else {
                            $controller->back("Sorry, there was an error uploading your file.");
                        }
                    } else {
                        $controller->back("Error: " . $_FILES['fileToUpload']['error']);
                    }
                } else {

                    $controller->back("No file was uploaded.");
                }
                break;

            case 'setting':

                $sql = "UPDATE app_yw_settings SET 
                        setting_value=?,
                        description=?,
                        updated_at=NOW()
                        WHERE id=?";

                $params = [
                    $_REQUEST['setting_value'],
                    $_REQUEST['description'],
                    $controller->arg1
                ];

                $this->controller->database->sql($sql, $params);

                $controller->flash("Setting Updated");

                break;

            case "addemails":
            case "addreports":
            case "addsettings":
            case "addusers":

                $allFields = [
                    "addemails" => "slug,body",
                    "addreports" => "category_name,report_name,report_query,description",
                    "addsettings" => "category_name,setting_name,setting_value,description",
                    "addusers" => "username,password,name,phone,email,role,status,profile",
                    ];

                $fields = $allFields[$value];
                $values = explode(',',$fields);
                $params = [];
                $fields = '';


                foreach ($values as $key) {

                    if (empty($_REQUEST[$key])) continue;

                    // Just accept the defaults for these fields (null or 0, etc)
                    //if(!in_array($key,['start_date','stop_date','dob','status','insurance','flag','disabled','enabled'])) continue;

                    $fields .= "`$key`,";
                    $params[$key] = $_REQUEST[$key] ?? null;
                }

                if (isset($params['phone'])) $params['phone'] = $this->controller->cleanPhoneNumber($params['phone']);

                $fields = rtrim($fields, ',');
                $placeholders = implode(',', array_fill(0, count($params), '?'));
                $table = str_replace(['add','edit'],'app_yw_',$value);
                $sql = "INSERT INTO $table ($fields) values ($placeholders)";// dd([$sql, $params]);
                $controller->database->sql($sql, $params);
                $controller->flash("Record Added");

                break;

            case "editemails":
            case "editreports":
            case "editsettings":
            case "editusers":

            $allFields = [
                "editemails" => "slug,body",
                "editemployees" => null,
                "editforms" => null,
                "editreports" => "category_name,report_name,report_query,description",
                "editsettings" => "category_name,setting_name,setting_value,description",
                "editusers" => "username,password,name,phone,email,role,status,profile",
            ];

            $fields = $allFields[$value];
            $values = explode(',',$fields);
            $params = [];
            $fields = '';
            foreach ($values as $key) {
                if (empty($_REQUEST[$key]) and (!in_array($key,['start_date','stop_date','dob','status','insurance','flag','disabled','enabled']))) continue;
                $fields .= "`$key`=?,";

                if (in_array($key,['status', 'insurance', 'flag', 'disabled', 'enabled'])) {

                    $params[$key] = isset($_REQUEST[$key]) ? 1:0;

                } elseif (in_array($key,['start_date', 'stop_date', 'dob'])) {

                    $params[$key] = (!empty($_REQUEST[$key])) ? $_REQUEST[$key]:null;

                } else {

                    $params[$key] = $_REQUEST[$key] ?? null;
                }
            }
            if (isset($params['phone'])) $params['phone'] = $this->controller->cleanPhoneNumber($params['phone']);
            $fields = rtrim($fields, ',');
            $table = str_replace(['add','edit'],'app_yw_',$value);
            $params['id'] = $controller->arg1;
            $sql = "UPDATE $table SET $fields WHERE id=?";
            //dd([$allFields,$fields, $value, $sql, $params]);
            $controller->database->sql($sql, $params);
            $controller->flash("Record Updated");
            try {
                $sql = "UPDATE $table SET deleted_at=NULL WHERE id=?";
                $controller->database->sql($sql, $controller->arg1);
            } catch (\Exceptiohn $e) {
                // nobody cares
            }

            break;

            case 'resetpw':
                if (empty($controller->arg1)) {
                    $controller->back('You cannot do that here. Please try again.');
                }

                $password = $_REQUEST['password'];
                $password2 = $_REQUEST['password2'];

                if ($password != $password2) {
                    $controller->back('Passwords do not match. Please try again.');
                }

                if (strlen($password) < 6) {
                    $controller->back('Passwords is too short. Please try again.');
                }

                $sql = "SELECT * FROM app_yw_users 
                    WHERE token = ?";

                $stmt = $this->controller->database->sql($sql, [$controller->arg1]);

                $users = $stmt->fetchAll();

                if (count($users) == 0) {
                    $controller->back('Sorry, there were no users matching your request. Please try again.');
                }

                foreach ($users as $user) {
                    $sql = "UPDATE app_yw_users SET password=? WHERE id=?";
                    $this->controller->database->sql($sql, [$password, $user['id']]);
                }

                $controller->home('Password has been reset');

            case 'lostpassword':
                $sql = "SELECT * FROM app_yw_users 
                    WHERE username = ? OR email = ?";

                $stmt = $this->controller->database->sql($sql, [$_REQUEST['username'], $_REQUEST['email']]);

                $users = $stmt->fetchAll();

                if (count($users) == 0) {
                    $controller->back('Sorry, there were no users matching your request.');
                }

                foreach ($users as $user) {


                    $password = $user['password'];

                    $username = $user['username'];

                    if (empty($username)) $username = $user['email'];

                    $email = $user['email'];

                    $token = uniqid();

                    $this->controller->database->sql("UPDATE app_yw_users SET token=? WHERE id=?",[$token,$user['id']]);

                    $link = "https://app.yoreweb.com/register/resetpw/$token";

                    $body = <<<EOB
<p>\n
Username: <b>$username</b><br/>\n
</p>\n
<h4>Please click on the link below to reset your password:<br><small>Or copy and paste it into your browser</small></h4>\n
<p>\n
<a href="$link"><b>$link</b></a>\n
</p>\n
<br/>\n

EOB;


                    $a = $this->controller->mail->send($email,"Login Info requested", $body);
                }

                $controller->back('Email Sent Successfully.');
                break;

            default:
                $this->controller->abort(500,"Unknown Module Post: $value");

        }
    }

    /**
     * @param $controller
     * @return void | string
     */
    function yore_cron($controller = false)
    {

        return;

        // Example: Send email out to any Client/Patients who registered more than 24 hours ago but haven't completed the forms

        $sql = "
            SELECT p.first_name, p.last_name, p.email, u.id as user_id, u.password FROM app_yw_patients  p
            JOIN app_yw_users u ON u.id = p.user_id
            LEFT JOIN app_yw_emails_sent s ON s.user_id=p.user_id AND s.slug='reminder'
            WHERE
            s.id IS NULL 
            AND p.`status`=0 
            AND p.deleted_at IS NULL 
            AND p.created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
        ";

        $rows = $this->controller->database->sql($sql)->fetchAll();

        foreach ($rows as $row) {
            $email = $row['email'];
            //$email = 'mechickaboola@gmail.com'; // TODO: Remove!
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $password = $row['password'];
            $user_id = $row['user_id'];

            $password = $this->controller->encodeAll($password);
            $username = $this->controller->encodeAll($email);

            $link = "https://app.firsthealthhc.com/module/users/auto_login?a=$username&b=$password";

            $body = <<<EOB
<h4>Hello $first_name $last_name,</h4>\n
<p>\nThis is a reminder form First Home Healthcare. 
Please complete our intake forms at your earliest convenience by clicking the link below
<small>Or copy and paste it into your browser to log in</small> </p>
<p>\n<a href="$link"><b>https://app.firsthealthhc.com/login</b></a>\n
</p>\n
<br/>\n

EOB;

            echo "\nSending reminder email to $email\n";

            $this->controller->mail->send($email,"First Home Healthcare Reminder", $body);

            $sql = "INSERT INTO app_yw_emails_sent (user_id, slug, body) values (?,?,?)";

            $this->controller->database->sql($sql, [$user_id, 'reminder', $body]);
        }


    }

    public function yore_navbar($where = 'top-right')
    {

        $role = $_SESSION['role'] ?? 'guest';

        if ($role=='User') $role='admin';

        // TODO: Turn this into an Array that is used to generate the HTML for the Nav Bar

        $controller = $this->controller;
        $domain = 'app.' . $controller->data->domain;
        $domain = str_replace('app.app.', 'app.', $domain); // err
        $site = $controller->data->site;
        $page = $controller->data->page;
        $view = $controller->data->view;
        $theme = $controller->data->theme;
        $user_id = $_SESSION['user_id'] ?? '';
        $debug = '';

        $module = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/modules/App/Module.php";
        $ApiTrait = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/modules/App/Traits/ApiTrait.php";
        $FredTrait = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/modules/App/Traits/FredTrait.php";
        $WebTrait = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/modules/App/Traits/WebTrait.php";
        $YoreTrait = "jetbrains://php-storm/navigate/reference?project=yorr&path=yore/modules/App/Traits/YoreTrait.php";

        if ($controller->is_debug) {

            $debug = <<<EOF
            <li class="nav-item dropdown fadey">
                <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-toggle="dropdown">Debug</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item theme-navbar-debug-color" href="$module">App Module</a>
                    <a class="dropdown-item theme-navbar-debug-color" href="$ApiTrait">API Trait</a>
                    <a class="dropdown-item theme-navbar-debug-color" href="$FredTrait">Fred Trait</a>
                    <a class="dropdown-item theme-navbar-debug-color" href="$WebTrait">Web Trait</a>
                    <a class="dropdown-item theme-navbar-debug-color" href="$YoreTrait">Yore Trait</a>
                </div>
            </li>
EOF;
        }

        $navBarArray = [
            'top-right' => [
                'user' => [
                    '<a title="Account Settings and Email Templates" style="color:#e70" href="/settings"><i class="bi bi-gear-fill theme-navbar-color"></i></a>'
                ],
               'patient' => [
                    '<a title="Account Settings" style="color:#e70" href="/settings"><i class="bi bi-gear-fill theme-navbar-color"></i></a>'
                ],
                'admin' => [
                    '<a title="Account Settings and Email Templates" style="color:#e70" href="/settings"><i class="bi bi-gear-fill theme-navbar-color"></i></a>'
                ],
                'guest' => [
                    '<a title="Account Settings" style="color:#e70" href="/settings"><i class="bi bi-gear-fill theme-navbar-color"></i></a>'
                ],
                'cna' => [
                    '<a title="Account Settings" style="color:#e70" href="/settings"><i class="bi bi-gear-fill theme-navbar-color"></i></a>'
                ],
                'hha' => [
                    '<a title="Account Settings" style="color:#e70" href="/settings"><i class="bi bi-gear-fill theme-navbar-color"></i></a>'
                ],
            ],
            'top-left' => [
                'user' => [
                    ['name' => 'Dashboard', 'site' => 'default'],
                    ['name' => 'Reports', 'site' => 'reports/index'],
                    ['name' => 'Users', 'site' => 'users/index'],
                ],
                'admin' => [
                    ['name' => 'Dashboard', 'site' => 'default'],
                    ['name' => 'Reports', 'site' => 'reports/index'],
                    ['name' => 'Users', 'site' => 'users/index'],
                ],
                'guest' => [
                    ['name' => 'Login', 'site' => 'module/users/login'],
                    ['name' => 'Register', 'site' => 'module/users/register'],
                ],
            ]
        ];

        $output = '';

        switch ($where) {
            case 'top-right':
                foreach ($navBarArray[$where][$role] as $item) {
                    $output .= $item;
                }
                return $output;
                break;
            case 'top-left':

                $output .= $debug;

                foreach ($navBarArray[$where][$role] as $item) {
                    $output .= " <li class=\"nav-item\"><a class=\"nav-link\" style='color:white' href='/{$item['site']}'>{$item['name']}</a></li>";

                }
                return $output;
                break;
        }

    }
}