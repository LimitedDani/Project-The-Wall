<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-1-2017
 * Time: 22:16
 */
$cvwl = false;
include 'includes/phpimports.php';
$parkid = $_REQUEST['id'];
if(!park::exist($mysqli, $parkid) || (park::isDeleted($mysqli, $parkid))) {
    header("Location: home.php");
    exit;
}
if(!park::IsUserStaff($mysqli, $parkid, $_SESSION['UUID'])) {
    header("Location: home.php");
    exit;
}
if(!park::CanWriteArticle($mysqli, $parkid, $_SESSION['UUID'])) {
    header("Location: home.php");
    exit;
}
if(isset($_POST['submit'])) {
    $parkid = $_POST['id'];
    $title = $_POST['title'];
    $body = preg_replace("/\r\n|\r/", "<br />", $_POST["article"]);
    $body = strip_tags($body, '<strong>, <i>, <br>');
    $bodyimg;
    $imgb = $_FILES['articleimage'];
    $bodyimg = common::uploadimage($imgb);
    if(park::IsUserStaff($mysqli, $parkid, $_SESSION['UUID']) || park::IsUserOwner($mysqli, $parkid, $_SESSION['UUID'])) {
        $body = article::post($mysqli, $parkid, $title, $body, $bodyimg, $bodyimg);
        header("Location: home.php");
        exit;
    } else {
        header("Location: home.php");
        exit;
    }
    exit;
}
$active = '';
?>
<?php system::copyRightSign();?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ParkCraft Online</title>
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
                            <h3 class="panel-title">Artikel</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <form name="register" id="register" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" method="post" autocomplete="off" class="form-horizontal">
                                        <div class="form-group">
                                            <label for="title" class="col-md-2 control-label"><span class="text-info">Titel</span></label>
                                            <div class="col-md-10" id="titlediv">
                                                <input type="hidden" name="id" value="<?php echo $parkid;?>">
                                                <input type="text" class="form-control" name="title" id="title" placeholder="Typ hier de titel van het artikel" value="" required>
                                            </div>
                                        </div>
                                        <div class="form-group">

                                            <label for="article" class="col-md-2 control-label"><span class="text-info">Artikel</span></label>
                                            <div class="col-md-10" id="titlediv">
                                                <textarea type="text" class="form-control" name="article" id="article" placeholder="Typ hier het artikel" value="" rows="10" required></textarea>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="articleimage" class="col-md-2 control-label"><span class="text-info">Artikel afbeelding<br /><small><span class="text-danger">Beste afmeting is 500x300 pixels</span></small></span></label>
                                            <div class="col-md-10" id="headdiv">
                                                <input type="file" id="articleimage" multiple="" name="articleimage" accept="image/*" onchange="loadBodyPreview(this)">
                                                <input type="text" readonly="" class="form-control" placeholder="Kies een afbeelding" id="articletext">
                                                <script>
                                                    $("#articleimage").change(function(){
                                                        if($("#articleimage").val() == '') {
                                                            document.getElementById("articletext").placeholder = 'Kies een afbeelding';
                                                        } else {
                                                            document.getElementById("articletext").placeholder = $("#articleimage").val().replace(/C:\\fakepath\\/i, '');
                                                        }
                                                    });
                                                </script>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <p class="text-danger">Alle velden moeten worden ingevuld (inclusief de afbeelding velden)</p>
                                            <button type="submit" class="btn btn-raised btn-success" name="submit" id="postbutton">Post</button>
                                            <button type="button" onclick="loadPreview()" class="btn btn-raised btn-info" data-toggle="modal" data-target="#preview">Preview</button>
                                        </div>
                                        <div class="modal fade" id="preview" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        <h4 class="modal-title">Artikel preview</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <img src="nothing" alt="Geen afbeelding gekozen" class="img-responsive center-block" id="prvimage"/>
                                                        <h3 id="prvtitle"></h3>
                                                        <p id="prvbody"></p>
                                                    </div>
                                                    <script>
                                                        function loadPreview() {
                                                            document.getElementById('prvtitle').innerHTML = document.getElementById('title').value;
                                                            document.getElementById('prvbody').innerHTML = document.getElementById('article').value.replace(/\r?\n/g, '<br />');;
                                                        }
                                                        function loadBodyPreview(fileInput) {
                                                            var files = fileInput.files;
                                                            for (var i = 0; i < files.length; i++) {
                                                                var file = files[i];
                                                                var imageType = /image.*/;
                                                                if (!file.type.match(imageType)) {
                                                                    continue;
                                                                }
                                                                var img=document.getElementById("prvimage");
                                                                img.file = file;
                                                                var reader = new FileReader();
                                                                reader.onload = (function(aImg) {
                                                                    return function(e) {
                                                                        aImg.src = e.target.result;
                                                                    };
                                                                })(img);
                                                                reader.readAsDataURL(file);
                                                            }
                                                        }
                                                    </script>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 right-container well">
                    <h4 class="text-danger">Artikel opmaak</h4>
                    <p class="text-muted">Bij het schrijven van het artikel kun je gebruik maken van tags om het artikel op te maken. </p>
                    <p class="text-muted">Gebruik <code>&lt;strong&gt;Hier de text&lt;&#47;strong&gt;</code> om de tekst <strong>dik te maken.</strong></p>
                    <p class="text-muted">Gebruik <code>&lt;i&gt;Hier de text&lt;&#47;i&gt;</code> om de tekst <i>schuin te maken.</i></p>
                    <p class="text-danger">Alle andere tags die worden gebruikt worden verwijderd.</p>
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