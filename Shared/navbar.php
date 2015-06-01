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
            <ul class="nav navbar-nav navbar-right">
                <?php
                if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']){
                    echo '<li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button">
                                ' . $_SESSION['accountInfo']['display_name'] . ' <span class="glyphicon glyphicon-chevron-down" style="font-size: 0.8em"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="?logout">Logout</a></li>
                            </ul>
                        </li>';
                } else {
                    echo '<li><a href="' . $authorizeUrl . '" style="padding: 14px 15px 23px 15px; height: 14px"><i class="fa fa-dropbox fa-lg"></i></a></li>';
                }
                ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>