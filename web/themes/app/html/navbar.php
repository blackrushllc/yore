<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a style="color:#f60" class="navbar-brand" href="/"><i class="bi bi-house theme-navbar-color"></i></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">

            <?php

                foreach ($this->modules as $key => $module) {
                    if (method_exists($module, 'yore_navbar')) {
                        $navItem = $module->yore_navbar('top-left');

                        echo $navItem;
                    }

                }

            ?>


        </ul>



        <h4>
            <?php
            foreach ($this->modules as $key => $module) {
                if ($key == 'Debug') {
                    if (method_exists($module, 'yore_navbar')) {
                        echo "<!-- Top Right $key DropDown -->";
                        $navItem = $module->yore_navbar('top-right');
                        echo $navItem;
                    }
                }
            }

            if (!empty($_SESSION['role'])) {
                foreach ($this->modules as $key => $module) {
                    if ($key != 'Debug') {
                        if (method_exists($module, 'yore_navbar')) {
                            echo "<!-- Top Right $key DropDown -->";
                            $navItem = $module->yore_navbar('top-right');
                            echo $navItem;
                        }
                    }
                }
            }
            // Get a NavBar icon from the Users module, if it exists, if it wants to give us one
            if ($this->users) {
                if (method_exists($this->users, 'yore_user_navbar')) {
                    $navItem = $this->users->yore_user_navbar('top');
                    if ($navItem) {
                        echo $navItem;
                        echo '&nbsp;';
                    }
                }
            }
            ?>

<!--            <i title="Account Settings" class="bi bi-gear theme-navbar-color"></i>-->
        </h4>
    </div>
</nav>

