<nav class="navbar navbar-expand-md xnavbar-dark bg-dark fixed-top">
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


            <!-- Some Rando Link -->
            <li class="nav-item">
                <a class="nav-link" href="/module/users/login">Login</a>
            </li>

            <!-- Some Rando Disabled Link -->
            <li class="nav-item">
                <a class="nav-link disabled" href="#">Disabled</a>
            </li>

            <!-- Some Rando DropDown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Dropdown</a>
                <div class="dropdown-menu" aria-labelledby="dropdown01">
                    <a class="dropdown-item" href="#">Action</a>
                    <a class="dropdown-item" href="#">Another action</a>
                    <a class="dropdown-item" href="#">Something else here</a>
                    <a class="dropdown-item" href="#">Something else too!!</a>
                </div>
            </li>

            <?php

            foreach ($this->modules as $key => $module) {
                if (method_exists($module, 'yore_navbar')) {
                    echo "<!-- Top Right $key DropDown -->";
                    $navItem = $module->yore_navbar('top-right');
                    echo $navItem;
                }

            }


            ?>


        </ul>



        <h4>
            <?php
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

            <i class="bi bi-gear theme-navbar-color"></i>
        </h4>
    </div>
</nav>

