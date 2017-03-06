<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-1-2017
 * Time: 22:16
 */
$cvwl = false;
include 'includes/phpimports.php';
if(isset($_POST['submit']) && isset($_POST['oldpassword']) && isset($_POST['newpassword'])) {
    $old = $_POST['oldpassword'];
    $new = $_POST['newpassword'];
    if(user::changePassword($mysqli, $new, $old, $_SESSION['UUID'])) {
        header("Location: settings.php?info=Uw wachtwoord is gewijzigd");
        exit;
    } else {
        header("Location: settings.php?warning=Het wachtwoord dat u hebt ingevuld bij Oud Wachtwoord is onjuist.");
        exit;
    }
}
if(isset($_GET['email'])) {
    if(isset($_GET['value']) && strcmp($_GET['value'], "on") == 0) {
        user::setReceiveNewsEmails($mysqli, 1);
        header("Location: settings.php");
        exit;
    }
    if(isset($_GET['value']) && strcmp($_GET['value'], "off") == 0) {
        user::setReceiveNewsEmails($mysqli, 0);
        header("Location: settings.php");
        exit;
    }
}
system::copyRightSign();?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>InstaWall</title>
        <script src="assets/js/bootstrap.min.js"></script>
        <?php include 'includes/imports.php'; ?>
    </head>
    <body>
    <?php include 'includes/nav.php'; ?>
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
                            <?php if(isset($_GET['followed'])) {
                                ?><h3 class="panel-title">Parken die je volgt</h3><?
                                $title = 'Parken die je volgt';
                            } else if(isset($_GET['password'])) {
                                ?><h3 class="panel-title">Wachtwoord veranderen</h3><?php
                            } else {
                                ?><h3 class="panel-title">Algemene instellingen</h3><?php
                            }?>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-12">
                                        <?php if(isset($_GET['followed'])) {
                                            user::loadFollowedParks($mysqli, $_SESSION['UUID']);
                                        } else if(isset($_GET['password'])) {?>
                                        <form name="settings" id="settings"
                                              action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
                                              enctype="multipart/form-data" method="post" autocomplete="off"
                                              class="form-horizontal">
                                            <div class="form-group">
                                                <label for="oldpassword" class="col-md-2 control-label"><span
                                                            class="text-info">Oud wachtwoord</span></label>
                                                <div class="col-md-10" id="ipdiv">
                                                    <input type="password" class="form-control" name="oldpassword" id="oldpassword"
                                                           placeholder="Typ hier het oude wachtwoord"
                                                           value="" required>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label for="newpassword" class="col-md-2 control-label"><span class="text-info">Nieuw wachtwoord</span></label>
                                                <div class="col-md-10" id="emaildiv">
                                                    <input type="password" class="form-control" name="newpassword" id="newpassword"
                                                           placeholder="Typ hier het nieuwe wachtwoord"
                                                           value="" required>
                                                </div>
                                            </div>

                                            <div class="text-center">
                                                <button type="submit" class="btn btn-raised btn-success" name="submit"
                                                        id="postbutton">Aanpassen
                                                </button>
                                            </div>
                                        </form>
                                        <?php } else {?>
                                            <div class="togglebutton">
                                                <label>
                                                    <div class="btn-group">
                                                        <a class="btn btn-primary <?php if(user::getReceiveNewsEmails($mysqli, $_SESSION['UUID'])) { echo 'active';}?>" href="?email&value=on">
                                                            Aan
                                                        </a>
                                                        <a class="btn btn-danger <?php if(!user::getReceiveNewsEmails($mysqli, $_SESSION['UUID'])) { echo 'active';}?>" href="?email&value=off">
                                                            Uit
                                                        </a>
                                                    </div> Nieuws emails ontvangen van parkcraft
                                                </label>
                                            </div>
                                        <?php }?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 right-container well">
                    <h4 class="text-danger">Snelkoppelingen</h4>
                    <p><a href="?" class="shortcut"><i class="material-icons">settings</i><span>Algemene instellingen</span></a></p>
                    <p><a href="?password" class="shortcut"><i class="material-icons">keyboard</i><span>Wachtwoord</span></a></p>
                    <p><a href="?followed" class="shortcut"><i class="material-icons">keyboard_arrow_right</i><span>Volgend</span></a></p>
                    <?php ads::skycraper();?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    </body>
    </html>
<?php
mysqli_close($mysqli);
?>
