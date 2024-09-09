<?php
namespace Modules\Chsapp;

# This is the module code for the Chsapp module 🔥😂🤑 😠🤔🧵👈😍💥

use App\Controller;
use App\Modules;

/**
 *
 */
class Module extends Modules {

    /**
     * CHS
     */
    public function __construct() {
        // Constructor is optional
        // Constructor in Parent does nothing right now, but all modules should call it anyway
        parent::__construct();
    }

    // Create a function that can be called as an api endpoint. For example, this is /api/hello/test
    // Notice that the slug for this module is /hello/ and not /Hello/
    /**
     * @param $controller
     * @param $method
     * @return string
     */
    public function api_call($controller, $method='GET') {

        // Notice that you have the $controller object here as a parameter, which basically gives you everything
        //  including all the modules which is important because you will want modules to interoperate,
        //  especially modules like Users and Database and Logging

        /*
         *
         * https://chsapp.com/module/chsapp/call/4011/emergency
          ["site"]=>
          string(6) "chsapp"
          ["name"]=>
          string(4) "call"
          ["arg1"]=>
          string(4) "4011"
          ["arg2"]=>
          string(9) "emergency"
          ["arg3"]=>
          bool(false)
         */

        $room = $controller->arg1;
        $alarm = $controller->arg2;

        $sql = "UPDATE chs_rooms SET alarm=? WHERE room=?";

        $ret = $controller->modules['Database']->sql($sql, [ $alarm, $room ]);

        // FYI Whatever you return will JSON encoded
        return $controller;

    }

    public function api_status($controller, $method='GET') {

        $stmt = $this->controller->modules['Database']->sql("SELECT * FROM chs_rooms WHERE room IS NOT NULL ORDER BY room");

        $rooms = $stmt->fetchAll();

        $status = [];
        foreach ($rooms as $r) {
            $room = $r['room'];
            $alarm = $r['alarm'];
            $time = $r['updated_at'];
            $r['mins'] = $this->minutesElapsedSince($time);
            $status[] = $r;
        }

        return $status;
    }

    /**
     * @param $world
     * @return string
     */
    public function fred_chsconsole($unit) {

        $unit = $_REQUEST['unit'] ?? 'no unit';

        $sql = "SELECT * FROM chs_rooms WHERE unit = ? AND room IS NOT NULL ORDER BY room";

        $stmt = $this->controller->modules['Database']->sql($sql, $unit);

        $rooms = $stmt->fetchAll();

        $output = '';

        foreach ($rooms as $r) {

            $output .= $this->roomButtonConsole($r);
        }
        return $output;
    }

    /**
     * @param $unit
     * @return string
     */
    public function fred_chsrooms() {

        if (empty($_REQUEST['unit'])) return 'Select a unit to add or remove rooms';

        $unit = $_REQUEST['unit'] ?? 'no unit';
        
        $sql = "SELECT * FROM chs_rooms where code=? and unit=? ORDER BY room";

        $stmt = $this->controller->modules['Database']->sql($sql, [$_SESSION['code'], $unit]);

        $rooms = $stmt->fetchAll();

        $onclick="onclick='doAddRoom()'";

        $output = "<table style='width:100%;'>
                        <tr>
                            <th class='td-room-number'>Room Numbers</th>
                            <th class='td-room-action'><button $onclick class='btn btn-success btn-add btn-add-room'><i class='bi bi-plus-circle'></i></button></th>
                        </tr>";

        foreach ($rooms as $r) {

            if ($r['room']) {

                $output .= $this->roomButtonAdmin($r);

            }
        }

        $output .= '</table>';

        return $output;
    }
    
    public function roomButtonAdmin($r) {

        $room = $r['room'];
        $room_id = $r['id'];

        $rmclick = "onclick='doDeleteRoom(\"$room_id\")'";

        $button = "
            <tr>
                <td class='td-room-number'>
                    $room
                </td>
                <td class='td-room-action'>
                    <button $rmclick class='btn btn-warning btn-del btn-del-room'>
                        <i class='bi bi-trash'></i></button>
                        <!--
                        &nbsp;
                        <button class='btn btn-primary btn-edit btn-edit-room'><i class='bi bi-pencil'></i></button>
                        -->
                </td>
            </tr>      
        ";

        return $button;
    }


    /**
     * @param $unit
     * @return string
     */
    public function fred_chsunits() {

        $sql = "SELECT DISTINCT unit FROM chs_rooms where code=? ORDER BY unit";

        $stmt = $this->controller->modules['Database']->sql($sql, [$_SESSION['code']]);

        $rooms = $stmt->fetchAll();

        $onclick="onclick='doAddUnit()'";

        $output = "<table style='width:100%;'>
                        <tr>
                            <th class='td-room-number'>Hospital Units</th>
                            <th class='td-room-action'><button $onclick class='btn btn-success btn-add btn-add-unit'><i class='bi bi-plus-circle'></i></button></th>
                        </tr>";

        foreach ($rooms as $r) {

            $output .= $this->unitButtonAdmin($r);
        }

        $output .= '</table>';

        return $output;
    }

    /**
     * @param $unit
     * @return string
     */
    public function fred_chsoptions() {

        //if (empty($_SESSION['code'])) exit('Error: no code 1');

        $unit = $_REQUEST['unit'] ?? 'no unit';

        $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

        if (!empty($_SESSION['code'])) {
            $sql = "SELECT DISTINCT unit, code FROM chs_rooms where code=? ORDER BY unit";
            $stmt = $this->controller->modules['Database']->sql($sql, [$_SESSION['code']]);
        }
        else {
            $sql = "SELECT DISTINCT unit, code FROM chs_rooms ORDER BY unit";
            $stmt = $this->controller->modules['Database']->sql($sql, []);
        }



        $units = $stmt->fetchAll();

        if ($unit == 'no unit') {
            $output = "<option selected disabled>-- Select Unit --</option>";
        } else {
            $output = "<option disabled>-- Select Unit --</option>";
        }

        $output = "<option selected disabled>-- Select Unit --</option>";

        foreach ($units as $r) {

            if ($unit == $r['unit']) {
                $output .= "<option rel='{$r['code']}' selected>{$r['unit']}</option>";
            } else {
                $output .= "<option rel='{$r['code']}'>{$r['unit']}</option>";
            }

        }

        $output .= '</table>';

        return $output;
    }

    /**
     * @param $unit
     * @return string
     */
    public function fred_chsunit() {

        $unit = $_REQUEST['unit'] ?? 'no unit';

        $sql = "SELECT DISTINCT unit FROM chs_rooms where unit=?";

        $stmt = $this->controller->modules['Database']->sql($sql, [$unit]);

        $rooms = $stmt->fetchAll();

        $output = "Select a unit to edit";

        foreach ($rooms as $r) {

            $unit = $r['unit'];

            $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

            $unitz = base64_encode($unit); // This is so we can update it later and this is very gay btw

            $output = "
            
                <form method='post'>
                    <!-- Catch this form using yore_module_post method -->
                    <input type='hidden' name='chsapp_post' value='editunit'>
                    <input type='text' name='unit' id='unit' value=\"$unit\">
                    <input type='hidden' name='unitz' id='unitz' value='$unitz'>
                    <input type='submit' value='Update Unit Name' class='btn btn-primary'>

                </form> 
            
            ";
        }



        return $output;
    }

    public function unitButtonAdmin($r) {

        $unit = $r['unit'];

        $onclick = "onclick=\"location.href='/admin?unit=$unit'\"";
        $rmclick = "onclick='doDeleteUnit(\"$unit\")'";

        $button = "
            <tr>
                <td class='td-room-number'>
                    $unit
                </td>
                <td class='td-room-action'>
                    <button $rmclick class='btn btn-warning btn-del btn-del-unit'><i class='bi bi-trash'></i></button>
                    &nbsp;
                    <button $onclick class='btn btn-primary btn-edit btn-edit-unit'><i class='bi bi-pencil'></i></button>
                </td>
            </tr>    
        ";

        return $button;
    }

    /**
     * @param $world
     * @return string
     */
    public function fred_chsnurse($unit = '') {

        $sql = "SELECT * FROM chs_rooms WHERE 
                    alarm != 'clear' 
                    AND room IS NOT NULL
                    AND unit = ?    
                        ORDER BY updated_at";

        $stmt = $this->controller->modules['Database']->sql($sql, [$unit]);

        $rooms = $stmt->fetchAll();

        $output = 'All Quiet Here';

        foreach ($rooms as $r) {

            $output .= $this->roomButtonNurse($r);
        }
        return $output;
    }

    public function api_nurse($controller, $method='GET') {

        if (empty($_SESSION['unit'])) return 'No unit has been selected';

        $unit = $_SESSION['unit'] ?? 'no unit';

        $sql = "SELECT * FROM chs_rooms 
                    WHERE alarm != 'clear' 
                    AND room IS NOT NULL
                    AND unit = ?
                    ORDER BY updated_at";

        $stmt = $this->controller->modules['Database']->sql($sql, [ $unit ]);

        $rooms = $stmt->fetchAll();

        $output = "";

        if (count($rooms) == 0) {
            $output = 'Silence is Golden!';
        }

        foreach ($rooms as $r) {

            $output .= $this->roomButtonNurse($r);
        }
        return $output;
    }
    function roomButtonNurse($r) {
        $room = $r['room'];
        $alarm = $r['alarm'];
        $time = $r['updated_at'];
        $mins = $this->minutesElapsedSince($time);

        $button = "
            <div class='call-div'>
                <button rel='$alarm' data-room='$room' id='room-$room' class='alarm call-button call-button-$alarm' onclick='doClick(this)'>
                    <div>$room<span id='span-$room' style='font-size:50%;margin-left:10px;'>$mins</span></div>
                </button>
            </div>";

        return $button;
    }
    function roomButtonConsole($r) {
        $room = $r['room'];
        $alarm = $r['alarm'];
        $time = $r['updated_at'];
        $mins = $this->minutesElapsedSince($time);

        if ($alarm == 'clear') {

            $label = "<div>$room<span id='span-$room' style='font-size:50%;margin-left:10px;'></span></div>";

        } else {
            $label = "<div>$room<span id='span-$room' style='font-size:50%;margin-left:10px;'>$mins</span></div>";
        }

        $button = "<div class='call-div'>
                <button rel='$alarm' data-room='$room' id='room-$room' class='alarm call-button call-button-$alarm'>
                    $label
                </button>
            </div>";

        return $button;
    }
    function minutesElapsedSince($datetime) {

        if (empty($datetime)) {
            return false;
        }
        // Create a DateTime object from the provided string
        $dateTimeObject = new \DateTime($datetime);

        // Get the current time as a DateTime object
        $currentDateTime = new \DateTime();

        // Calculate the time difference between now and the provided datetime
        $interval = $currentDateTime->diff($dateTimeObject);

        // Convert the difference to total minutes
        $minutesElapsed = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

        // If the datetime is in the future, return negative minutes
        if ($dateTimeObject > $currentDateTime) {
            return -$minutesElapsed;
        }

        return $minutesElapsed;
    }

    function yore_module_post($controller, $value) {
        // This method is called if a form is being posted, with whatever we wanted passed to us from the form in $value

        if ($value == 'register') {

            $sql = "SELECT * FROM chs_users 
                    WHERE admin_username = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, $_REQUEST['admin_username']);

            $users = $stmt->fetchAll();

            if (count($users) > 0) {
                exit('TODO SCREEN GOES HERE FOR: admin_username already exists. Go back and try again');
            }

            $sql = "SELECT * FROM chs_users 
                    WHERE code = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, $_REQUEST['code']);

            $users = $stmt->fetchAll();

            if (count($users) > 0) {
                exit('TODO SCREEN GOES HERE FOR: Console Code already exists. Go back and try again');
            }

            $sql = "INSERT INTO chs_users SET
                        admin_password = ?,
                        admin_username = ?,
                        code = ?,
                        console_password = ?,
                        console_username = ?,
                        hospital_address = ?,
                        hospital_name = ?,
                        primary_contact_email = ?,
                        primary_contact_name = ?,
                        primary_contact_phone = ?
                    ";

            $array = [
                $_REQUEST['admin_password'],
                $_REQUEST['admin_username'],
                $_REQUEST['code'],
                $_REQUEST['console_password'],
                $_REQUEST['console_username'],
                $_REQUEST['hospital_address'],
                $_REQUEST['hospital_name'],
                $_REQUEST['primary_contact_email'],
                $_REQUEST['primary_contact_name'],
                $_REQUEST['primary_contact_phone'],
            ];

            $stmt = $this->controller->modules['Database']->sql($sql, $array);

            exit('TODO SCREEN GOES HERE FOR: Registration Accepted. <a href="/">Go To Home Page</>');
        }

        if ($value == 'admin') {

            $sql = "SELECT * FROM chs_users 
                    WHERE {$value}_username = ? AND {$value}_password = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$_REQUEST['username'], $_REQUEST['password']]);

            $users = $stmt->fetchAll();

            if (count($users) == 0) {
                exit("TODO SCREEN GOES HERE FOR: Incorrect $value login. Go back and try again");
            }

            foreach ($users as $user) {
                $_SESSION['role'] = $value; // admin or console
                $_SESSION['code'] = $user['code'];
                $_SESSION['admin_username'] = $user['admin_username'];
                $_SESSION['console_username'] = $user['console_username'];
                $_SESSION['hospital_name'] = $user['hospital_name'];
                $_SESSION['primary_contact_email'] = $user['primary_contact_email'];
                $_SESSION['primary_contact_phone'] = $user['primary_contact_phone'];
                $_SESSION['primary_contact_name'] = $user['primary_contact_name'];
            }

        }

        if ($value == 'console') {

            $sql = "SELECT * FROM chs_users 
                    WHERE {$value}_username = ? AND {$value}_password = ? AND code = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$_REQUEST['username'], $_REQUEST['password'], $_REQUEST['code']]);

            $users = $stmt->fetchAll();

            if (count($users) == 0) {
                exit("TODO SCREEN GOES HERE FOR: Incorrect $value login for code. Go back and try again");
            }

            foreach ($users as $user) {
                $_SESSION['role'] = $value; // admin or console
                $_SESSION['code'] = $user['code'];
                $_SESSION['console_username'] = $user['console_username'];
                $_SESSION['hospital_name'] = $user['hospital_name'];
                $_SESSION['primary_contact_email'] = $user['primary_contact_email'];
                $_SESSION['primary_contact_phone'] = $user['primary_contact_phone'];
                $_SESSION['primary_contact_name'] = $user['primary_contact_name'];
            }

        }

        if ($value == 'nurse') {

            $unit = $_REQUEST['unit'] ?? 'no unit';

            $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

            $sql = "SELECT * FROM chs_users 
                    WHERE code = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$_REQUEST['code']]);

            $users = $stmt->fetchAll();

            if (count($users) == 0) {
                exit("TODO SCREEN GOES HERE FOR: Incorrect code. Go back and try again");
            }

            foreach ($users as $user) {
                $_SESSION['role'] = 'nurse';
                $_SESSION['code'] = $user['code'];
                $_SESSION['hospital_name'] = $user['hospital_name'];
                $_SESSION['primary_contact_email'] = $user['primary_contact_email'];
                $_SESSION['primary_contact_phone'] = $user['primary_contact_phone'];
                $_SESSION['primary_contact_name'] = $user['primary_contact_name'];
            }

            $_SESSION['unit'] = $unit;

        }

        if ($value == 'patient') {

            // Verify that we have an actual user/hospital with this code

            $sql = "SELECT * FROM chs_users 
                    WHERE code = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$_REQUEST['code']]);

            $users = $stmt->fetchAll();

            if (count($users) == 0) {
                exit("TODO SCREEN GOES HERE FOR: Incorrect code. Go back and try again");
            }

            // Verify that we have a room with this code and room number

            $sql = "SELECT * FROM chs_rooms 
                    WHERE code = ? AND room = ?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$_REQUEST['code'], $_REQUEST['room']]);

            $rooms = $stmt->fetchAll();

            if (count($rooms) == 0) {
                exit("TODO SCREEN GOES HERE FOR: Incorrect room. Go back and try again");
            }

            foreach ($rooms as $room) {
                $_SESSION['role'] = 'patient';
                $_SESSION['code'] = $room['code'];
                $_SESSION['room'] = $room['room'];
            }

            foreach ($users as $user) {
                $_SESSION['hospital_name'] = $user['hospital_name'];
                $_SESSION['primary_contact_email'] = $user['primary_contact_email'];
                $_SESSION['primary_contact_phone'] = $user['primary_contact_phone'];
                $_SESSION['primary_contact_name'] = $user['primary_contact_name'];
            }

        }

        if ($value == 'editunit') {

            if (empty($_SESSION['code'])) exit('Error: no code 2');

            $code = $_SESSION['code'] ?? "no code";
            $unit = $_REQUEST['unit'] ?? "no unit";
            $unitz = $_REQUEST['unitz'] ?? "no unitz";

            $unitz = base64_decode($unitz);

            $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

            $sql = "UPDATE chs_rooms
                    SET unit=?
                    WHERE code = ? AND unit=?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$unit,$code,$unitz]);

        }

        if ($value == 'delunit') {

            if (empty($_SESSION['code'])) exit('Error: no code 3');

            $code = $_SESSION['code'] ?? "no code";
            $unit = $_REQUEST['unit'] ?? "no unit";

            $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

            $sql = "DELETE FROM chs_rooms
                    WHERE code = ? AND unit=?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$code,$unit]);

        }


        if ($value == 'addunit') {

            if (empty($_SESSION['code'])) exit('Error: no code 4');

            $code = $_SESSION['code'] ?? "no code";
            $unit = $_REQUEST['unit'] ?? "no unit";

            $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

            $sql = "INSERT INTO chs_rooms
                    SET code = ?, unit=?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$code,$unit]);

        }

        if ($value == 'delroom') {

            if (empty($_SESSION['code'])) exit('Error: no code 5');

            $room_id = $_REQUEST['room_id'] ?? "no room";

            $sql = "DELETE FROM chs_rooms WHERE id=?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$room_id]);

        }

        if ($value == 'addroom') {

            if (empty($_SESSION['code'])) exit('Error: no code 6');

            $code = $_SESSION['code'] ?? "no code";

            $room = $_REQUEST['room'] ?? "no room";

            $unit = $_REQUEST['unit'] ?? "no unit";

            $unit = str_replace(['"',"'",'\\','\n','\r','@'],'',$unit);

            $room = str_replace(['"',"'",'\\','\n','\r','@'],'',$room);

            $sql = "INSERT INTO chs_rooms
                    SET code = ?, room=?, unit=?";

            $stmt = $this->controller->modules['Database']->sql($sql, [$code,$room,$unit]);

        }

    }
}