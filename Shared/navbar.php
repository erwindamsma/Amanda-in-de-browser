<!-- Social Buttons for Bootstrap -->
<link rel="stylesheet" href="bootstrap/bootstrap-social.css">
<!-- Font Awesome -->
<link rel="stylesheet" href="font-awesome/css/font-awesome.css">

<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">AmandaJS</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li class="active"><a href="/">AmandaJS</a></li>
                <li><a href="#">Courses</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right nav-pills">
                <?php
                    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
                        echo '
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" style="padding: 4px 15px 4px 15px">
                                    <img src="' . $_SESSION['img'] . '?sz=42" height="42" style="border-radius: 100%">
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="#">' . $_SESSION['apiId'] . '</a></li>
                                    <li><a href="#">' . $_SESSION['firstName'] . ' ' . $_SESSION['lastName'] . '</a></li>
                                    <li><a href="#">' . $_SESSION['email'] . '</a></li>
                                    <li><a href="?logout">Logout</a></li>
                                </ul>
                            </li>';
                    } else {
                        echo '
                        <li><a href="' . $helper->getLoginUrl( array( 'email', 'user_friends' ) ) . '"><i class="fa fa-facebook fa-lg"></i></a></li>
                        <li><a href="' . $authUrl .'"><i class="fa fa-google fa-lg"></i></a></li>';
                    }
                ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>