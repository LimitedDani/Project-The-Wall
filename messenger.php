<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-1-2017
 * Time: 22:16
 */
$cvwl = true;
include 'includes/phpimports.php';
$active = 'messenger';
if(isset($_GET['startchat'])) {
    if(user::excistUUID($mysqli, $_GET['startchat'])) {
        if(strcmp($_GET['startchat'], $_SESSION['UUID']) !=0) {
            chats::startChat($mysqli, $_GET['startchat'], $_SESSION['UUID']);
        }
    }
    header("Location: messenger.php?id=".chats::getChatID($mysqli, $_GET['startchat'], $_SESSION['UUID'])."");
    exit;
}
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Secret');
header('Access-Control-Max-Age: 120');
$chatid = $_GET['id'];
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
                            <h3 class="panel-title">InstaWall Messenger</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div>
                                        <?php
                                        if(!empty($chatid)) {
                                            if(!chats::isChatOfUser($mysqli, $chatid, $_SESSION['UUID'])) {
                                            }else {
                                                ?>
                                                <div id="chat" class="modal fade" role="dialog">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                <h4 class="modal-title">Gesprek
                                                                    met <?php echo chats::getNameOfChatter($mysqli, $chatid); ?></h4>
                                                            </div>
                                                            <div class="modal-body" id="chatbox"
                                                                 style="height:30em;width:auto;border:1px solid #ccc;overflow:auto;word-break: break-all;">
                                                                <?php chats::loadChat($mysqli, $chatid); ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <div class="form-group">
                                                                    <label for="bericht" class="col-md-2 control-label">Bericht</label>

                                                                    <div class="col-md-10">
                                                                        <input type="text" class="form-control" id="bericht" placeholder="Bericht">
                                                                    </div>
                                                                </div>
                                                                <button id="send" class="btn btn-success">Verzenden</button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <script type="text/javascript">
                                                    $(window).load(function () {
                                                        $('#chat').modal('show');
                                                        $('#chat').on('hide.bs.modal', function () {
                                                            window.location = "/instawall/messenger.php";
                                                        });
                                                        $('#chat').on('hidden', function () {
                                                            window.location = "/instawall/messenger.php";
                                                        });
                                                    });
                                                </script>
                                                <?php
                                            }
                                        }?>
                                        <div id="chats">
                                        <?php
                                            chats::loadChats($mysqli, $_SESSION['UUID']);
                                        ?>
                                        </div>
                                        <script>
                                            function openChat(id) {
                                                window.location = "/instawall/messenger.php?id=" + id;
                                            }
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if(isset($_SESSION['UUID'])) {?>
                    <div class="col-md-3 right-container well">
                        <h4 class="text-danger">Wie te volgen</h4>
                        <?php ads::skycraper();?>
                    </div>
                <?php }?>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="includes/chat.js"></script>
    </body>
    </html>
<?php
mysqli_close($mysqli);
?>