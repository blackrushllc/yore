<?php
namespace Modules\Library;


use App\Controller;
use App\Modules;

/**
 *
 */
class Module extends Modules {

    protected $upload_css_once = true;

    /**
     *
     */
    public function __construct() {
        // Constructor is optional
        //echo "Hello World! Main class instantiated." . PHP_EOL;

        // Constructor in Parent does nothing right now, but all modules should call it anyway
        $this->dir = __DIR__;
        parent::__construct();
    }

    /**
     * @param Controller|null $controller
     * @return string
     */

    // Create a function that can be used in a Ph@ view, like @HELLO('World'). Return the string "Hello World" or "Hello " + argument

    /**
     * @param $world
     * @return string
     */
    public function fred_hello($world) {
        return "Hello $world";
    }
    /**
     * @param $world
     * @return string
     */
    public function fred_arg($arg) {
        switch($arg) {
            case 'page':   return $this->controller->pagekey; break;
            case 'name':   return $this->controller->pagekey; break;
            case 'domain':   return $this->controller->domain; break;
            case 'site':   return $this->controller->section; break;
            case 0:   return $this->controller->pagekey; break;
            case 1:   return $this->controller->arg1; break;
            case 2:   return $this->controller->arg2; break;
            case 3:   return $this->controller->arg3; break;
            case 4:   return $this->controller->section; break;
            case 5:   return $this->controller->domain; break;
            case 6:   return $this->controller->title; break;
            case 7:   return $this->controller->theme; break;
            case 8:   return $this->controller->desc; break;
            case 9:   return $this->controller->body; break;
            case 10:   return $this->controller->view; break;

        }

    }

    public function fred_flash() {
        if (!empty($this->controller->flash))
            return "<h1 class='flash'>" . $this->controller->flash . "</h1>";
        else
            return '';
    }

    /**
     * @param $world
     * @return string
     *
     * Display a form to upload a file with
     *
     */
    public function fred_upload($params = []) {

        $html = <<<EOCSS
<style>
    .upload-area {
        width: 100%;
        height: 200px;
        border: 2px dashed #ccc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #999;
        cursor: pointer;
    }
    .upload-area.dragover {
        border-color: #000;
        background-color: #f9f9f9;
    }
</style>

<script>
   $(function() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        // Click on the drag area to open file dialog
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle file drop
        uploadArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (event) => {
            event.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
            }
        });
    });
</script>
EOCSS;


        if ($this->upload_css_once) {
            $this->upload_css_once = false;
            $html .= "";
        }


        $html.= <<<EOF

    <!--form id="uploadForm" method="post" enctype="multipart/form-data"-->
        <!-- Drag and Drop area -->
        <div id="uploadArea" class="upload-area">
            Drag & Drop or Click to Upload File
        </div>

        <!-- Hidden file input -->
        <input type="file" class="btn btn-outline-primary" id="fileInput" name="fileToUpload" style="xdisplay:none;">

        <br><br>
        <button class="btn btn-primary" type="submit">Upload</button>
    <!--/form-->


EOF;

        return $html;



    }


    // Optional method to modify output before it gets rendered, after it gets Phatted

    /**
     * @param $controller
     * @param $output
     * @return void
     */
    public function yore_output($controller, &$output) {
        //$output = str_replace('Hello', 'Hark!', $output);
    }

    /**
     * @param $which
     * @return string
     */
    public function x_yore_navbar($which = 'top') {
        // Contribute to the web page's navigation bar
        // This needs to be more like a data structure and not sending back HTML
        // And then we need to be able to contribute stuff like
        // A top level link item
        // A top level _named_ drop down menu (<-- this is important)
        // With sub menu entries and all that implies
        // Like separators
        // And sub sub menus
        // And attributes like disabled, checked, etc
        // sub menu entries to add to existing other _named_ top menu dropdowns (<-- thats why that was important)


        switch($which) {

            // But for right now were just gonna do this fooligway:
            case 'top':
            case 'top-left':
                return '
                    <!--Hello Module Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="#">Hellosk!</a>
                    </li>
                    ';
                break;

            case 'top-right':

                return '

                     <!-- Some Rando DropDown -->
                    <li class="nav-item dropdown">
                        <span class="nav-link dropdown-toggle" id="dropdown-x" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Tools</span>
                        <div class="dropdown-menu" aria-labelledby="dropdown-x">
                            <a class="dropdown-item" href="#">(M)anage Site</a>
                            <a class="dropdown-item" href="#">(N)ew Site</a>
                            <a class="dropdown-item" href="#">(E)dit Page</a>
                            <a class="dropdown-item" href="/module/users/logout">Logout</a>
                        </div>
                    </li>

                ';
                break;
        }
    }

    // Create a function that can be called as an api endpoint. For example, this is /api/hello/test
    // Notice that the slug for this module is /hello/ and not /Hello/
    /**
     * @param $controller
     * @param $method
     * @return string
     */
    public function api_test($controller, $method='GET') {

        // Notice that you have the $controller object here as a parameter, which basically gives you everything
        //  including all the modules which is important because you will want modules to interoperate,
        //  especially modules like Users and Database and Logging

        // FYI Whatever you return will JSON encoded
        return "I believe that I have finally learned how to Want again";

    }

    /**
     * @param $world
     * @return string
     */
    public function fred_data($element) {
        return $this->controller->$element;
    }



}
