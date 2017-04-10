<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 17-1-2017
 * Time: 21:01
 */
?>
<nav class="navbar navbar-customjooo">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-inverse-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="home.php" class="text-white"><strong class="navbar-brand"><span>InstaWall</span></strong></a>
        </div>
        <?php if(isset($_SESSION['UUID'])) {?>
        <div class="navbar-collapse collapse navbar-inverse-collapse">
            <ul class="nav navbar-nav">
                <li <?php if(strcmp($active, "home") == 0) { echo 'class="active"';}?>><a href="home.php">Home</a></li>
                <li <?php if(strcmp($active, "upload") == 0) { echo 'class="active"';}?>><a href="upload.php">Foto uploaden</a></li>
                <li <?php if(strcmp($active, "messenger") == 0) { echo 'class="active"';}?>><a href="messenger.php">Berichten <span class="badge text-white" id="chatcounts"><?php echo chats::countNotReadedMessages($mysqli, $_SESSION['UUID']);?></span></a></li>
            </ul>
            <form class="navbar-form navbar-right" action="search.php" method="get">
                <div class="form-group">
                    <input type="text" class="form-control col-md-8" name="keywords" placeholder="Zoeken">
                </div>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <?php if(staff::canUseStaffPanel($mysqli, $_SESSION['UUID'])) {?>
                    <li <?php if(strcmp($active, "staff") == 0) { echo 'class="active"';}?>><a href="staff.php?home">Staf paneel</a></li>
                <?php }?>
                <li class="dropdown">
                    <a href="#" data-target="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo user::getName($mysqli) ?>
                        <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="profile.php">Profiel</a></li>
                        <li><a href="settings.php">Instellingen</a></li>
                        <li class="divider"></li>
                        <li><a href="logout.php">Uitloggen</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <?php } else if($cvwl) {?>
        <div class="navbar-collapse collapse navbar-inverse-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="index.php">Login</a></li>
            </ul>
        </div>
        <?php } else {
            header("Location: index.php");
            exit;
        }?>
    </div>
    <script type="text/javascript">
        $('ul.dropdown-menu [data-toggle=dropdown]').on('click', function(event) {
            // Avoid following the href location when clicking
            event.preventDefault();
            // Avoid having the menu to close when clicking
            event.stopPropagation();
            // Re-add .open to parent sub-menu item
            $(this).parent().addClass('open');
            $(this).parent().find("ul").parent().find("li.dropdown").addClass('open');
        });
    </script>
    <script>
        function countmessages() {
            $('#chatcounts').load('https://daniquedejong.nl/instawall/chat-api.php?chatcount');
        }
        setInterval(countmessages, 1000);
    </script>
</nav>
