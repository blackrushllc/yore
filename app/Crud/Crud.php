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

namespace App\Crud;

use App\Command;

class Crud
{

    public $table, $site, $prefix, $domain, $controller;

    public function __construct($controller, $domain, $table, $site, $prefix)
    {
        $this->table = $table;
        $this->site = $site;
        $this->prefix = $prefix;
        $this->domain = $domain;
        $this->controller = $controller;
        $this->createCrud($controller, $domain, $table, $site, $prefix);

    }

    function createCrud(Command $controller, $domain, $table, $site, $prefix) {
        $D = $controller->database;
        $sql = "SELECT 
                    TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, 
                    COLUMN_TYPE, COLUMN_KEY, EXTRA, COLUMN_COMMENT
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'yore'
                AND TABLE_NAME = '$table';";

        $rows = $D->sql($sql);
        $title="Index Page";

        $content = $this->generateHtmlTablesScript($rows, $domain, $table, $site, $prefix);
        $this->createCrudPage('index', $rows, $domain, $table, $site, $prefix, $title, $content);
        $this->createCrudPage('deleted', $rows, $domain, $table, $site, $prefix, $title, $content);

        $rows = $D->sql($sql);
        $title="Add Record";
        $content = $this->generateAddTableRowForm($rows, $domain, $table, $site, $prefix);
        $this->createCrudPage('add', $rows, $domain, $table, $site, $prefix, $title, $content);

        $rows = $D->sql($sql);
        $title="Edit Record";
        $content = $this->generateEditTableRowForm($rows, $domain, $table, $site, $prefix);
        $this->createCrudPage('edit', $rows, $domain, $table, $site, $prefix, $title, $content);

        $rows = $D->sql($sql);
        $title="Delete Record";
        $content="Delete Record";
        $this->createCrudPage('delete', $rows, $domain, $table, $site, $prefix, $title, $content);

    }

    function createCrudPage($page, $rows, $domain, $table, $site, $prefix, $title, $content) {
        // Create the JSON file
        echo "Creating $page JSON " . PHP_EOL;
        $json = $this->getJsonTemplate($page);
        file_put_contents('eyegol.inc', $json);

        // Make variables available inside the included file (?)
        //extract(get_defined_vars());

        ob_start();
        include 'eyegol.inc';
        $output = ob_get_clean();

        $this->writeToDomainSiteJsonPage($page, $output);

        // Create the VIEW file
        echo "Creating $page HTML " . PHP_EOL;
        $json = $this->getHtmlTemplate($page);
        file_put_contents('eyegol.inc', $json);

        // Make variables available inside the included file (?)
        //extract(get_defined_vars());

        ob_start();
        include 'eyegol.inc';
        $output = ob_get_clean();

        $this->writeToDomainSiteHtmlPage($page, $output);

        //unlink ('eyegol.inc');

    }
    public function getJsonTemplate($viewName)
    {
        $path = __DIR__ . "/templates/$viewName.json";
        return file_get_contents($path);
    }

    public function getHtmlTemplate($viewName)
    {
        $path = __DIR__ . "/templates/views/$viewName.html";
        return file_get_contents($path);
    }

    public function writeToDomainSiteJsonPage($filename, $content)
    {
        // Get the current working directory (e.g., /path/to/yore/web)
        $cwd = getcwd();

        // Go one level up to get to /path/to/yore
        $projectRoot = dirname($cwd);

        // Build the target path: /path/to/yore/_domains/domain/site/myfile.html
        $targetPath = $projectRoot . '/pages/_domains/' . $this->domain . '/' . $this->site . '/' . $filename . '.json';

        // Ensure the directory exists
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }

        // Write the file
        file_put_contents($targetPath, $content);
    }

    public function writeToDomainSiteHtmlPage($filename, $content)
    {
        // Get the current working directory (e.g., /path/to/yore/web)
        $cwd = getcwd();

        // Go one level up to get to /path/to/yore
        $projectRoot = dirname($cwd);

        // Build the target path: /path/to/yore/_domains/domain/site/myfile.html
        $targetPath = $projectRoot . '/pages/_domains/' . $this->domain . '/' . $this->site . '/views/' . $filename . '.html';

        // Ensure the directory exists
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0775, true);
        }

        // Write the file
        file_put_contents($targetPath, $content);
    }

    public function generateHtmlTablesScript($rows, $domain, $table, $site, $prefix) {

        // TODO: Use something similar to this to generate add / edit forms!  (Fred OR HTML?)

        $COLUMNS = "";
        foreach ($rows as $row) {
            // Generate datatables columns
            $name = $row['COLUMN_NAME'];

            // Default (unless otherwise specified in the switches below)
            $col = " { data: '$name', name: '$name', visible: true }";

            // Also Default (unless otherwise specified in the switches below)
            //  Show booleans as Yes or No
            if (substr($name, 0, 3) === 'is_') {
                 $col = "{ 
                    data: '$name', 
                    name: '$name', 
                    title: '$name', 
                    visible: true,
                    render: function(data, type, row) {
                        if (data == '1')
                            return 'Yes';
                        else
                            return 'No';
                    },
                },";
            }

            // Hide any "_at" dates by default
            if (substr($name, -3) === '_at') {
                $col = " { data: '$name', name: '$name', visible: false }";
            }

            // Hide any "file" columns by default
            if (substr($name, -4) === 'file') {
                $col = " { data: '$name', name: '$name', visible: false }";
            }
            // Make any _name columns orderable by default (first_name, category_name, etc)
            if (substr($name, -5) === '_name') {
                $col = " { data: '$name', name: '$name', visible: true , orderable: true}";
            }

            // Hide any "flag" columns by default
            if (substr($name, 0, 4) === 'flag') {
                $col = " { data: '$name', name: '$name', visible: false }";
            }

            // Hide any "image" columns by default
            if (substr($name, 0, 5) === 'image') {
                $col = " { data: '$name', name: '$name', visible: false }";
            }

            // IS_NULLABLE could be "YES" or "NO"
            // COLUMN_DEFAULT could be something like NULL or 1 or now(), etc
            // COLUMN KEY of "PRI" would be the primary index.  "UNI" would be unique index
            // EXTRA could be "auto_increment" or "DEFAULT_GENERATED" or "on update CURRENT_TIMESTAMP" etc
            // You could also use COLUMN_COMMENT to customize these parameters as well

            switch ($row["DATA_TYPE"]) { // "COLUMN_TYPE" could also be "int unsigned" or "varchar(255)" etc
                case 'varchar':
                    switch ($row['COLUMN_NAME']) {
                        case 'some_special_colname':
                            $col = " { data: '$name', name: '$name', visible: false }";
                            break;
                        case 'name':  //first_name and last_name etc are covered in the defaults above
                        case 'address':
                            $col = " { data: '$name', name: '$name', visible: true , orderable: true}";
                            break;

                    }
                    break;

                case 'int':
                case 'tinyint':
                case 'bigint':

                case 'date': // use date pickers for these?
                case 'datetime':
                case 'timestamp':

                case 'text': // truncate these / use textareas?
                case 'longtext':
                case 'mediumtext':

                    switch ($row['COLUMN_NAME']) {
                        case 'status':
                            $col = " { data: '$name', name: '$name', visible: false }";
                            break;
                    }
                    break;


            }

            if ($name != 'id') // do not generate a column for id (we do it below)
                $COLUMNS .= "\n            $col,";

        }

        $COLUMNS = rtrim($COLUMNS, ',');

$script = <<<EOF1

<script>
        
$(document).ready(function() {
    $(document).on('click', '.deletelink', function(event) {
        var buttonText = $(this).html();
        if (confirm('Delete record, are you sure?')) {
            
            rel = $(this).attr('rel');
            //window.open('/$site/delete/' + rel, '_blank');   
            window.location.href = '/$site/delete/' + rel + '/' + buttonText;
        }
    });
    $(document).on('click', '.editlink', function(event) {
        rel = $(this).attr('rel');
        //window.open('/$site/edit/' + rel, '_blank');   
        window.location.href = '/$site/edit/' + rel;
    });
    $('#myTable').DataTable({
        "rowCallback": function(row, data, index) {
            if (index % 2 === 0) {
                $(row).css('background-color', '#aaffaa'); // Light grey
            } else {
                $(row).css('background-color', '#ccffcc'); // White
            }
        },
        createdRow: function(row, data, dataIndex) {
            // Add attributes to cells
            //$('td', row).eq(1).attr('title', 'law firm currently assigned lead');
            //$('td', row).eq(6).attr('title', 'lead sought medical treatment');
            //$('td', row).eq(7).attr('title', 'lead has determined fault');
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                className: 'nowrap',
                render: function(data, type, row) {
                    var ret = '';
                    console.log(row);
                        
                    ret += '<button class="editlink btn btn-sm btn-warning" rel="' + data + '">Edit</button>';
                    ret += '&nbsp;<button class="deletelink btn btn-sm btn-danger" rel="' + data + '">Del</button>';
                    
                    //ret += '<span id="clock' + data + '">' + jclock(row.created_at) + '</span>';
                    //ret += '<span id="audio' + data + '">' + jaudio(row.mp3file) + '</span>';

                    return ret;
                },
                orderable: false // Disable ordering on this column
            },

            $COLUMNS

        ],
        order: [[2, 'desc']],
       // dom: 'lBfrtip', // Include the Buttons extension
        dom: '<"top"lf>     <"top"B> rt<"bottom"ip><"clear">', // Include the Buttons extension
        buttons: [
            'csv', 'excel', 'pdf', 'print'
        ],
        lengthMenu: [ // Add this line to specify the number of rows per page options
            [10, 25, 50, 100], // Page lengths
            [10, 25, 50, 100]  // Display values
        ],
        responsive: true,
        pageLength: 25, // Default page length
        initComplete: function(settings, json) {
            // Add padding to the left of the buttons container

            console.log('DT $table Init Complete');

            $('.dt-buttons').wrap('<div class="buttons-container"></div>');
            $('.dt-buttons').wrap('<div class="buttons-container"></div>');
            $('.dt-buttons').wrap('<div class="buttons-container"></div>');

        }
    });
});

</script>
<style>
    .nowrap {
        white-space: nowrap;
    }
</style>

EOF1;


        return $script;
    }



    public function generateEditTableRowForm($rows, $domain, $table, $site, $prefix) {

        $FIELDS = "\n<!-- FIELDS -->\n";

        foreach ($rows as $row) {
            // Generate datatables columns
            $name = $row['COLUMN_NAME'];
            $ucname = ucwords(str_replace('_', ' ' , $name));

            // Default (unless otherwise specified in the switches below)
            $field = "@input(\"$name\",\"$ucname\",\"$ucname:\",\"@field(\"$name\")\")";

            // Also Default (unless otherwise specified in the switches below)
            //  Show booleans as Yes or No
            if (substr($name, 0, 3) === 'is_') {
                $field = "@checkbox(\"$name\",\"$ucname:\",\"@field(\"$name\")\")";
            }

            // Hide any "_at" dates by default
            if (substr($name, -3) === '_at') {
                $field = "";
            }

            if (substr($name, 0, 4) === 'flag') {
                $field = "@checkbox(\"$name\",\"$ucname:\",\"@field(\"$name\")\")";
            }

            if (substr($name, 0, 5) === 'image') {
                $field = "@image(\"$name\",\"$ucname\",\"$ucname:\",\"@field(\"$name\")\")";
            }

            if (substr($name, 0, 5) === 'email') {
                $field = "@email(\"$name\",\"$ucname\",\"$ucname:\",\"@field(\"$name\")\")";
            }

            // IS_NULLABLE could be "YES" or "NO"
            // COLUMN_DEFAULT could be something like NULL or 1 or now(), etc
            // COLUMN KEY of "PRI" would be the primary index.  "UNI" would be unique index
            // EXTRA could be "auto_increment" or "DEFAULT_GENERATED" or "on update CURRENT_TIMESTAMP" etc
            // You could also use COLUMN_COMMENT to customize these parameters as well

            switch ($row["DATA_TYPE"]) { // "COLUMN_TYPE" could also be "int unsigned" or "varchar(255)" etc
                case 'varchar':
                    switch ($row['COLUMN_NAME']) {
                        case 'some_special_colname':
                            $field = "@input(\"$name\",\"$ucname\",\"$ucname:\",\"@field(\"$name\")\")";
                            break;
                    }
                    break;

                case 'int':
                case 'tinyint':
                case 'bigint':

                    break;
                case 'date': // use date pickers for these?
                case 'datetime':
                case 'timestamp':

                    if (substr($name, -3) === '_at') {
                        $field = "";
                    } else {
                        $field = "@date(\"$name\",\"$ucname\",\"$ucname:\",\"@field(\"$name\")\")";
                    }

                    break;

                case 'text': // truncate these / use textareas?
                case 'longtext':
                case 'mediumtext':
                    $field = "@textarea(\"$name\",\"$ucname\",\"$ucname:\",\"@field(\"$name\")\")";
                    break;


            }

            if ($name != 'id') // do not generate a column for id (we do it below)
                $FIELDS .= "\n    $field";

        }

        $FIELDS .= "\n<!-- END FIELDS -->\n";

        return $FIELDS;

    }

    public function generateAddTableRowForm($rows, $domain, $table, $site, $prefix) {

        $FIELDS = "\n<!-- ADD FIELDS -->\n";

        foreach ($rows as $row) {
            // Generate datatables columns
            $name = $row['COLUMN_NAME'];
            $ucname = ucwords(str_replace('_', ' ' , $name));

            // Default (unless otherwise specified in the switches below)
            $field = "@input(\"$name\",\"$ucname\",\"$ucname:\",\"\")";

            // Also Default (unless otherwise specified in the switches below)
            //  Show booleans as Yes or No
            if (substr($name, 0, 3) === 'is_') {
                $field = "@checkbox(\"$name\",\"$ucname:\",\"\")";
            }

            // Hide any "_at" dates by default
            if (substr($name, -3) === '_at') {
                $field = "";
            }

            if (substr($name, 0, 4) === 'flag') {
                $field = "@checkbox(\"$name\",\"$ucname:\",\"\")";
            }

            if (substr($name, 0, 5) === 'image') {
                $field = "@image(\"$name\",\"$ucname\",\"$ucname:\",\"\")";
            }

            if (substr($name, 0, 5) === 'email') {
                $field = "@email(\"$name\",\"$ucname\",\"$ucname:\",\"\")";
            }

            // IS_NULLABLE could be "YES" or "NO"
            // COLUMN_DEFAULT could be something like NULL or 1 or now(), etc
            // COLUMN KEY of "PRI" would be the primary index.  "UNI" would be unique index
            // EXTRA could be "auto_increment" or "DEFAULT_GENERATED" or "on update CURRENT_TIMESTAMP" etc
            // You could also use COLUMN_COMMENT to customize these parameters as well

            switch ($row["DATA_TYPE"]) { // "COLUMN_TYPE" could also be "int unsigned" or "varchar(255)" etc
                case 'varchar':
                    switch ($row['COLUMN_NAME']) {
                        case 'some_special_colname':
                            $field = "@input(\"$name\",\"$ucname\",\"$ucname:\",\"\")";
                            break;
                    }
                    break;

                case 'int':
                case 'tinyint':
                case 'bigint':

                    break;
                case 'date': // use date pickers for these?
                case 'datetime':
                case 'timestamp':

                    if (substr($name, -3) === '_at') {
                        $field = "";
                    } else {
                        $field = "@date(\"$name\",\"$ucname\",\"$ucname:\",\"\")";
                    }
                    break;

                case 'text': // truncate these / use textareas?
                case 'longtext':
                case 'mediumtext':
                    $field = "@textarea(\"$name\",\"$ucname\",\"$ucname:\",\"\")";
                    break;


            }

            if ($name != 'id') // do not generate a column for id (we do it below)
                $FIELDS .= "\n    $field";

        }

        $FIELDS .= "\n<!-- END FIELDS -->\n";

        return $FIELDS;

    }

}
