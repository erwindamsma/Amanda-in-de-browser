<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">AmandaOnline</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="#">AmandaJS</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php
                    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
                        echo '
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" style="padding: 2px 15px 2px 15px">
                                    <img src="' . $_SESSION['img'] . '?sz=50" style="border-radius: 100%">
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">' . $_SESSION['apiId'] . '</a></li>
                                    <li><a href="#">' . $_SESSION['firstName'] . ' ' . $_SESSION['lastName'] . '</a></li>
                                    <li><a href="#">' . $_SESSION['email'] . '</a></li>
                                    <li><a href="?logout">Logout</a></li>
                                </ul>
                            </li>';
                    } else {
                        echo '<li><a>login: </a></li>
                        <li><a href="' . $helper->getLoginUrl( array( 'email', 'user_friends' ) ) . '">FB</a></li>
                        <li><a class="login" href="' . $authUrl .'">G</a></li>';
                    }
                ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>