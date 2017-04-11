<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-1-2017
 * Time: 22:16
 */
$cvwl = false;
include 'includes/phpimports.php';
if(isset($_POST['submit'])) {
    $title = $_POST['text'];
    $bodyimg;
    $imgb = $_FILES['image'];
    $bodyimg = common::uploadimage($imgb);
    $body = posts::post($mysqli, $title, $bodyimg);
    header("Location: home.php");
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
                            <h3 class="panel-title">Upload nieuwe foto</h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <form name="register" id="register" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data"enctype="multipart/form-data" method="post" autocomplete="off" class="form-horizontal">
                                        <div class="form-group">
                                            <label for="articleimage" class="col-md-2 control-label"><span class="text-info">Afbeelding<br /></span></label>
                                            <div class="col-md-10" id="headdiv">
                                                <img src="nothing" alt="Geen afbeelding gekozen" class="img-responsive center-block" id="imgprev"/>
                                                <input type="file" id="image" multiple="" name="image" accept="image/*" onchange="loadBodyPreview(this)" data-max-size="2048" required>
                                                <input type="text" readonly="" class="form-control" placeholder="Kies een afbeelding" id="phototext">
                                                <script>
                                                    $("#image").change(function(){
                                                        if($("#image").val() == '') {
                                                            document.getElementById("phototext").placeholder = 'Kies een afbeelding';
                                                        } else {
                                                            document.getElementById("phototext").placeholder = $("#image").val().replace(/C:\\fakepath\\/i, '');
                                                        }
                                                    });
                                                    function loadBodyPreview(fileInput) {
                                                        var files = fileInput.files;
                                                        for (var i = 0; i < files.length; i++) {
                                                            var file = files[i];
                                                            var imageType = /image.*/;
                                                            if (!file.type.match(imageType)) {
                                                                continue;
                                                            }
                                                            var img=document.getElementById("imgprev");
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
                                            </div>
                                        </div>
                                        <div class="form-group">

                                            <label for="article" class="col-md-2 control-label" required><span class="text-info">Bijschrift</span></label>
                                            <div class="col-md-10" id="titlediv">
                                                <input type="text" name="text" class="form-control">
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-raised btn-success" name="submit" id="postbutton">Post</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 right-container well">
                    <h4 class="text-danger">Text opmaak</h4>
                    <p class="text-muted">Bij het schrijven van de beschrijving kun je gebruik maken van tags om de beschrijving op te maken. </p>
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
