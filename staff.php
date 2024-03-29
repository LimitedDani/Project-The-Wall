<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-1-2017
 * Time: 22:16
 */
$cvwl = false;
include 'includes/phpimports.php';
if(!staff::canUseStaffPanel($mysqli, $_SESSION['UUID'])) {
    header("Location: home.php");
    exit;
}
if(isset($_POST['mailbutton'])) {
    if(!staff::canSendMail($mysqli, $_SESSION['UUID'])) {
        header("Location: staff.php?home=&warning=Geen toegang tot dit gedeelte.");
        exit;
    }
    $subject = $_POST['subject'];
    $body = preg_replace("/\r\n|\r/", "<br />", $_POST["mail"]);
    user::sendEmailToEveryone($mysqli, $subject, $body);
    header("Location: staff.php?mail&info=Email verstuurd");
    exit;
}
if(isset($_POST['edituserbutton'])) {
    if(!staff::canManageUsers($mysqli, $_SESSION['UUID'])) {
        header("Location: staff.php?home=&warning=Geen toegang tot dit gedeelte.");
        exit;
    }
    $uuid = $_POST['uuid'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $rank = $_POST['rank'];
    $access = $_POST['ttpo'];
    $activated = $_POST['ag'];
    $pageid = $_POST['pi'];
    user::update($mysqli, $name, $email, $rank, $access, $activated, $uuid);
    header("Location: staff.php?users=&page=$pageid&info=Gebruiker aangepast");
    exit;
}
if(isset($_GET['removepost']) || isset($_GET['undoremovepost'])) {
    if(!staff::canManagePosts($mysqli, $_SESSION['UUID'])) {
        header("Location: staff.php?home=&warning=Geen toegang tot dit gedeelte.");
        exit;
    }
    $post = $_GET['posts'];
    if(isset($_GET['undoremovepost'])) {
        posts::undeletepoststaff($mysqli, $post, $_SESSION['UUID']);
    }
    if(isset($_GET['removepost'])) {
        posts::deletepoststaff($mysqli, $post, $_SESSION['UUID']);
    }
    $pageid = $_GET['pi'];
    header("Location: staff.php?posts=&page=$pageid&info=Artikel aangepast");
    exit;
}
if(isset($_GET['removereaction'])) {
    $id = $_GET['id'];
    posts::RemoveReaction($mysqli, $id);
    $pageid = $_GET['pi'];
    header("Location: staff.php?reactions=&page=$pageid&info=Reactie verwijderd");
    exit;
}
$active = 'staff';
$keywords;
if(isset($_GET['keywords'])) {
    $keywords = $_GET['keywords'];
}
?>
<?php system::copyRightSign();?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>InstaWall</title>
        <script src="assets/js/bootstrap.min.js"></script>
        <?php include 'includes/imports.php';?>
    </head>
    <body>
    <?php include 'includes/nav.php';?>
    <div class="container">
        <?php if (isset($warning)) { ?>
            <div class="alert alert-dismissible alert-warning">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <span><?php echo $warning; ?></span>
            </div>
        <?php } ?>
        <?php if (isset($danger)) { ?>
            <div class="alert alert-dismissible alert-danger">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <span><?php echo $danger; ?></span>
            </div>
        <?php } ?>
        <?php if (isset($info)) { ?>
            <div class="alert alert-dismissible alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <span><?php echo $info; ?></span>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="col-md-9">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <?php if(isset($_GET['posts'])) {?>
                                <h3 class="panel-title">Posts beheren</h3>
                            <?php } else if(isset($_GET['reactions'])) {?>
                                <h3 class="panel-title">Reacties</h3>
                            <?php } else if(isset($_GET['mail'])) {?>
                                <h3 class="panel-title">Mail naar alle gebruikers</h3>
                            <?php } else if(isset($_GET['users'])) {?>
                                <h3 class="panel-title">Gebruikers</h3>
                            <?php } else if(isset($_GET['statistics'])) {?>
                                <h3 class="panel-title">Statistieken</h3>
                            <?php } else {?>
                                <h3 class="panel-title">Home</h3>
                            <?php }?>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php if(isset($_GET['users']) && staff::canManageUsers($mysqli, $_SESSION['UUID'])) {
                                        if(!isset($_GET['id'])) {
                                            ?>
                                            <form action="staff.php" method="get">
                                                <div class="form-group">
                                                    <input type="hidden" class="form-control col-md-8" name="users">
                                                    <div style="display:inline-block;">
                                                        <input type="text" class="form-control" name="keywords"
                                                               placeholder="Zoeken" value="<?php echo $keywords;?>">
                                                    </div>
                                                    <div style="display:inline-block;">
                                                        <a href="staff.php?users=&page=1" class="btn-sm btn-danger">Reset</a>
                                                    </div>
                                                </div>
                                            </form>
                                            <?php
                                        }
                                         if(isset($_GET['id'])) {
                                            user::loadUserIn($mysqli, $_GET['id']);
                                        } else if($_GET['keywords']) {
                                            user::loadAllUsersSearch($mysqli, $_GET['keywords']);
                                        } else if(isset($_GET['page'])) {
                                            if($_GET['page'] != 0) {
                                                $pageid = $_GET['page'] - 1;
                                                user::loadAllUsers($mysqli, $pageid);
                                            }
                                        }
                                    } else if(isset($_GET['reactions']) && staff::canManageComments($mysqli, $_SESSION['UUID'])) {
                                        if(isset($_GET['page'])) {
                                            if($_GET['page'] != 0) {
                                                $pageid = $_GET['page'] - 1;
                                                posts::loadAllReactions($mysqli, $pageid);
                                            }
                                        }
                                    } else if(isset($_GET['posts']) && staff::canManagePosts($mysqli, $_SESSION['UUID'])) {
                                        if(isset($_GET['page'])) {
                                            if($_GET['page'] != 0) {
                                                $pageid = $_GET['page'] - 1;
                                                posts::loadAllPosts($mysqli, $pageid);
                                            }
                                        }
                                    } else if(isset($_GET['mail'])) {
                                        if(!staff::canSendMail($mysqli, $_SESSION['UUID'])) {
                                            header("Location: staff.php?warning=Geen toegang tot dit gedeelte.");
                                            exit;
                                        }
                                        ?>
                                        <form name="mail" id="mail" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" method="post" autocomplete="off" class="form-horizontal">
                                            <div class="form-group">
                                                <label for="title" class="col-md-2 control-label"><span class="text-info">Titel</span></label>
                                                <div class="col-md-10" id="titlediv">
                                                    <input type="text" class="form-control" name="subject" id="subject" placeholder="Typ hier het onderwerp..." value="" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="article" class="col-md-2 control-label"><span class="text-info">De Mail</span></label>
                                                <div class="col-md-10" id="titlediv">
                                                    <textarea type="text" class="form-control" name="mail" id="mail" placeholder="Typ hier de mail" value="" rows="10" required></textarea>
                                                </div>
                                            </div>

                                            <div class="text-center">
                                                <button type="submit" class="btn btn-raised btn-success" name="mailbutton" id="mailbutton" form="mail">Post</button>
                                            </div>
                                        </form>
                                        <?php
                                    } else if(isset($_GET['home'])){?>
                                        <p>Home</p>
                                    <?php } else if(isset($_GET['statistics'])){?>
                                        <div class="jumbotron">
                                            <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
                                            <h3>Pagina weergaven</h3>
                                            <script type="text/javascript" src="assets/js/jquery.canvasjs.min.js"></script>
                                            <div id="chartContainer" style="height: 300px; width: 100%;"></div>
                                            <script type="text/javascript">
                                                window.onload = function () {
                                                    var chart = new CanvasJS.Chart("chartContainer",
                                                        {

                                                            title:{
                                                                text: "Weergaven",
                                                                fontSize: 15
                                                            },
                                                            animationEnabled: true,
                                                            axisX:{

                                                                gridColor: "Silver",
                                                                tickColor: "silver",
                                                                valueFormatString: "DD/MMM"

                                                            },
                                                            toolTip:{
                                                                shared:true
                                                            },
                                                            theme: "theme2",
                                                            axisY: {
                                                                gridColor: "Silver",
                                                                tickColor: "silver"
                                                            },
                                                            legend:{
                                                                verticalAlign: "center",
                                                                horizontalAlign: "right"
                                                            },
                                                            data: [
                                                                {
                                                                    type: "line",
                                                                    showInLegend: true,
                                                                    lineThickness: 2,
                                                                    name: "Weergaven",
                                                                    markerType: "square",
                                                                    color: "#F08080",
                                                                    dataPoints: [
                                                                        <?php
                                                                        statistics::totalPageVisits($mysqli);
                                                                        ?>
                                                                    ]
                                                                },
                                                            ],
                                                            legend:{
                                                                cursor:"pointer",
                                                                itemclick:function(e){
                                                                    if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                                                                        e.dataSeries.visible = false;
                                                                    }
                                                                    else{
                                                                        e.dataSeries.visible = true;
                                                                    }
                                                                    chart.render();
                                                                }
                                                            }
                                                        });

                                                    chart.render();
                                                }
                                            </script>
                                            <hr />
                                            <h3>Aantal posts</h3>
                                            <?php
                                            $countpost = statistics::posts($mysqli);
                                            ?>
                                            <span class="countpost"><?php echo $countpost;?></span>
                                            <div class="progress progress-striped">
                                                <div class="progress-bar progress-bar-info countpostl" style="width: 0%"></div>
                                            </div>
                                            <script type="text/javascript">
                                                $('.countpost').each(function () {
                                                    $(this).prop('Counter',0).animate({
                                                        Counter: $(this).text()
                                                    }, {
                                                        duration: 4000,
                                                        easing: 'swing',
                                                        step: function (now) {
                                                            $(this).text(Math.ceil(now));
                                                            $('.countpostl').css('width', ((100 / <?php echo $countpost;?>) * Math.ceil(now)) + '%');
                                                        }
                                                    });
                                                });
                                            </script>
                                            <hr />
                                            <h3>Aantal reacties op posts</h3>
                                            <?php
                                            $countcomments = statistics::reactions($mysqli);
                                            ?>
                                            <span class="countcomments"><?php echo $countcomments;?></span>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success countcommentsl" style="width: 0%"></div>
                                            </div>
                                            <script type="text/javascript">
                                                $('.countcomments').each(function () {
                                                    $(this).prop('Counter',0).animate({
                                                        Counter: $(this).text()
                                                    }, {
                                                        duration: 4000,
                                                        easing: 'swing',
                                                        step: function (now) {
                                                            $(this).text(Math.ceil(now));
                                                            $('.countcommentsl').css('width', ((100 / <?php echo $countcomments;?>) * Math.ceil(now)) + '%');
                                                        }
                                                    });
                                                });
                                            </script>
                                            <hr />
                                            <h3>Aantal gebruikers</h3>
                                            <?php
                                            $countusers = statistics::users($mysqli);
                                            $countusersactivated = statistics::usersActivated($mysqli);
                                            $countusersnotactivated = $countusers - $countusersactivated;
                                            ?>
                                            <span class="text-success">Aantal gebruikers: </span> <span class="countusers"><?php echo $countusers;?></span><br/>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success countusersl" style="width: 0%"></div>
                                            </div>
                                            <span class="text-success">Aantal geactiveerde gebruikers: </span><span class="countusersa"><?php echo $countusersactivated;?></span><br/>
                                            <span class="text-danger">Aantal niet geactiveerde gebruikers: </span><span class="countusersna"><?php echo $countusersnotactivated;?></span>
                                            <div class="progress progress-striped active">
                                                <div class="progress-bar progress-bar-success countusersla" style="width: 0%"></div>
                                                <div class="progress-bar progress-bar-danger countuserslna" style="width: 0%"></div>
                                            </div>
                                            <script type="text/javascript">
                                                $('.countusers').each(function () {
                                                    $(this).prop('Counter',0).animate({
                                                        Counter: $(this).text()
                                                    }, {
                                                        duration: 4000,
                                                        easing: 'swing',
                                                        step: function (now) {
                                                            $(this).text(Math.ceil(now));
                                                            $('.countusersl').css('width', (100 / <?php echo $countusers;?> * now) + '%');
                                                        }
                                                    });
                                                });
                                                $('.countusersa').each(function () {
                                                    $(this).prop('Counter',0).animate({
                                                        Counter: $(this).text()
                                                    }, {
                                                        duration: 4000,
                                                        easing: 'swing',
                                                        step: function (now) {
                                                            $(this).text(Math.ceil(now));
                                                            $('.countusersla').css('width', (100 / $('.countusers').text() * <?php echo $countusersactivated;?>) + '%');
                                                        }
                                                    });
                                                });
                                                $('.countusersna').each(function () {
                                                    $(this).prop('Counter',0).animate({
                                                        Counter: $(this).text()
                                                    }, {
                                                        duration: 4000,
                                                        easing: 'swing',
                                                        step: function (now) {
                                                            $(this).text(Math.ceil(now));
                                                            $('.countuserslna').css('width', (100 / $('.countusers').text() * <?php echo $countusersnotactivated;?>) + '%');
                                                        }
                                                    });
                                                });
                                            </script>
                                        </div>
                                    <?php } else {
                                        header("Location: staff.php?home=&warning=Geen toegang tot dit gedeelte.");
                                        exit;
                                    }?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 right-container well">
                    <h4 class="text-danger">Snelkoppelingen</h4>
                    <p><a href="?home" class="shortcut"><i class="material-icons">home</i><span>Home</span></a></p>
                    <?php if(staff::canManageUsers($mysqli, $_SESSION['UUID'])) {?>
                        <p><a href="?users=&page=1" class="shortcut"><i class="material-icons">accessibility</i><span>Gebruikers beheren</span></a></p>
                    <?php }
                    if(staff::canManagePosts($mysqli, $_SESSION['UUID'])) {?>
                        <p><a href="?posts=&page=1" class="shortcut"><i class="material-icons">assignment</i><span>Posts beheren</span></a></p>
                    <?php }
                    if(staff::canManageComments($mysqli, $_SESSION['UUID'])) {?>
                        <p><a href="?reactions=&page=1" class="shortcut"><i class="material-icons">textsms</i><span>Reacties beheren</span></a></p>
                    <?php }
                    if(staff::canSendMail($mysqli, $_SESSION['UUID'])) {?>
                        <p><a href="?mail" class="shortcut"><i class="material-icons">mail</i><span>Mail</span></a></p>
                    <?php }?>
                    <p><a href="?statistics" class="shortcut"><i class="material-icons">timeline</i><span>Statistieken</span></a></p>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    </body>
    </html>
<?php
mysqli_close($mysqli);
?>
