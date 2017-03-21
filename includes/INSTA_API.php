<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 19-1-2017
 * Time: 10:21
 */
class user {
    static function loginWithCookie($mysqli, $email, $sessionID, $redirect) {
        if(!empty($email) && !empty($sessionID)) {

            $sql="SELECT * FROM pco_users WHERE email='$email' AND sessionID='$sessionID'";
            $result = mysqli_query($mysqli, $sql);
            $count = mysqli_num_rows($result);
            $row = mysqli_fetch_assoc($result);
            if($count > 0) {
                if(!user::hasAccess($mysqli, $row['UUID'])) {
                    header("Location: index.php?warning=Je hebt geen toegang tot InstaWall.");
                    exit;
                }
                session_start();
                $_SESSION['user'] = $row['ID'];
                $_SESSION['UUID'] = $row['UUID'];
                header("Location: ".$redirect);
                exit;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    static function createSessionID($mysqli) {
        $key = '';
        $keys = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
        for ($i = 0; $i < 20; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        $sql="SELECT * FROM pco_users WHERE sessionID='$key'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return user::createSessionID($mysqli);
        } else {
            return $key;
        }
    }
    static function login($mysqli, $email, $password, $redirect, $remeberme) {
        if(!empty($email) && !empty($password)) {
            $salt = 'fe98yh7834bd2s';
            $hashed = hash('sha256', $salt.$password);

            $sql="SELECT * FROM pco_users WHERE email='$email' AND password='$hashed'";
            $result = mysqli_query($mysqli, $sql);
            $count = mysqli_num_rows($result);
            $row = mysqli_fetch_assoc($result);
            if($count > 0) {
                if(!user::hasAccess($mysqli, $row['UUID'])) {
                    header("Location: index.php?warning=Je hebt geen toegang tot InstaWall.");
                    exit;
                }
                session_start();
                $_SESSION['user'] = $row['ID'];
                $_SESSION['UUID'] = $row['UUID'];
                $sessionID = user::createSessionID($mysqli);
                $sql1="UPDATE pco_users SET sessionID='$sessionID' WHERE email='$email' AND password='$hashed'";
                $result1 = mysqli_query($mysqli, $sql1);
                if($remeberme) {
                    setcookie("pcoemail", $email, time() + (86400 * 30), '/');
                    setcookie("pcosessionid", $sessionID, time() + (86400 * 30), '/');
                }
                header("Location: ".$redirect);
                exit;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    static function hasAccess($mysqli, $userid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$userid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($row['access'] == 1) {
            return true;
        } else {
            return false;
        }
    }
    static function isActivated($mysqli, $userid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$userid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if(strcmp($row['activated'], '1') == 0) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    static function register($mysqli, $name, $email, $password) {
        if(!empty($name) && !empty($email) && !empty($password)) {
            $name = strip_tags($name);
            $email = strip_tags($email);
            $salt = 'fe98yh7834bd2s';
            $hashed = hash('sha256', $salt.$password);
            $activationcode = common::random(40);
            $sql = "INSERT INTO pco_users (UUID, name, email, password, activated) VALUES (UUID(), '$name', '$email', '$hashed', '$activationcode');";
            $result = mysqli_query($mysqli, $sql);
            user::sendActivationMail($mysqli, $email, $activationcode);
            return true;
        } else {
            return false;
        }
    }
    static function getActivationCode($mysqli, $uuid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['activated'];
        } else {
            return user::getActivationCode($mysqli, $uuid);
        }
    }
    static function exist($mysqli, $email) {
        $sql="SELECT * FROM pco_users WHERE email='$email'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    static function excistID($mysqli, $id) {
        $sql="SELECT * FROM pco_users WHERE ID='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    static function excistUUID($mysqli, $UUID) {
        $sql="SELECT * FROM pco_users WHERE UUID='$UUID'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    static function getIDFromUUID($mysqli, $uuid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['ID'];
        }
        return '0';
    }

    static function update($mysqli, $name, $email, $rank, $access, $activated, $uuid) {
        $name = strip_tags($name);
        $email = strip_tags($email);
        $sql="UPDATE pco_users SET name='$name', email='$email', rank='$rank', access='$access', activated='$activated' WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);

        $sql="SELECT * FROM pco_staff WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            if($rank > 0) {
                $sql="UPDATE pco_staff SET rank='$rank' WHERE UUID='$uuid'";
                $result = mysqli_query($mysqli, $sql);
            } else {
                $sql = "DELETE FROM pco_staff WHERE UUID = '$uuid'";
                $result = mysqli_query($mysqli, $sql);
            }
        } else {
            if($rank > 0) {
                $sql = "INSERT INTO pco_staff (UUID, rank) VALUES ('$uuid', '$rank');";
                $result = mysqli_query($mysqli, $sql);
            }
        }
    }
    static function getUUIDFromEmail($mysqli, $email) {
        $sql="SELECT * FROM pco_users WHERE email='$email'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['UUID'];
        }
        return '0';
    }
    static function getUUIDFromID($mysqli, $id) {
        $sql="SELECT * FROM pco_users WHERE ID='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['UUID'];
        }
        return '0';
    }
    static function logout() {
        session_start();
        if(isset($_COOKIE["pcoemail"]) && isset($_COOKIE["pcosessionid"])) {
            unset($_COOKIE["pcoemail"]);
            unset($_COOKIE["pcosessionid"]);
            setcookie('pcoemail', null, -1, '/');
            setcookie('pcosessionid', null, -1, '/');
        }
        session_unset();
        session_destroy();
        session_write_close();
    }
    static function getName($mysqli) {
        $id = $_SESSION['user'];
        $sql="SELECT * FROM pco_users WHERE ID='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['name'];
        }
    }
    static function getEmail($mysqli, $uuid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['email'];
        }
    }
    static function getPrefix($mysqli, $rank) {
        $sql="SELECT * FROM pco_ranks WHERE rank='$rank'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['prefix'];
        }
    }
    static function getLabel($mysqli, $rank) {
        $sql="SELECT * FROM pco_ranks WHERE rank='$rank'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['color'];
        }
    }
    static function getNameByUUID($mysqli, $uuid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['name'];
        }
    }
    static function getRank($mysqli) {
        $id = $_SESSION['UUID'];
        $sql="SELECT * FROM pco_staff WHERE UUID='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['rank'];
        } else {
            return 0;
        }
    }
    static function getRankByUUID($mysqli, $UUID) {
        $sql="SELECT * FROM pco_staff WHERE UUID='$UUID'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['rank'];
        } else {
            return 0;
        }
    }
    static function getCurrentPassword($mysqli, $UUID) {
        $sql="SELECT * FROM pco_users WHERE uuid='$UUID'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['password'];
        }
    }
    static function IsFollowingPark($mysqli, $parkid, $uuid) {
        $sql="SELECT * FROM pco_parks WHERE ID='$parkid' and followers LIKE '%{$uuid}%'";
        $result=mysqli_query($mysqli,$sql);
        $count=mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0){
            return 1;
        }
        return 0;
    }
    static function IsActivationCodeValid($mysqli, $code) {
        $sql="SELECT * FROM pco_users WHERE activated='$code'";
        $result=mysqli_query($mysqli,$sql);
        $count=mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0){
            if($code == 1) {
                return false;
            }
            return true;
        }
        return false;
    }
    static function IsPasswordCodeValid($mysqli, $code) {
        $sql="SELECT * FROM pco_users WHERE changepassword='$code'";
        $result=mysqli_query($mysqli,$sql);
        $count=mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0){
            if(strcmp($code, '0') == 0) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }
    static function activateAccount($mysqli, $code) {
        $sql = "UPDATE pco_users SET activated='1' WHERE activated='$code'";
        $result = mysqli_query($mysqli, $sql);
    }
    static function changePassword($mysqli, $newpassword, $oldpassword, $userid) {
        $salt = 'fe98yh7834bd2s';
        $hashedold = hash('sha256', $salt.$oldpassword);
        $hashednew = hash('sha256', $salt.$newpassword);
        if(user::getCurrentPassword($mysqli, $userid) == $hashedold) {
            $sql = "UPDATE pco_users SET password='$hashednew' WHERE UUID='$userid'";
            $result = mysqli_query($mysqli, $sql);
            return true;
        } else {
            return false;
        }
    }
    static function changeForgotPassword($mysqli, $newpassword, $code) {
        $salt = 'fe98yh7834bd2s';
        $hashednew = hash('sha256', $salt.$newpassword);
        $sql = "UPDATE pco_users SET password='$hashednew', changepassword='0' WHERE changepassword='$code'";
        $result = mysqli_query($mysqli, $sql);
    }
    static function delete($mysqli, $code) {
        $sql = "DELETE FROM pco_users WHERE activated='$code'";
        $result = mysqli_query($mysqli, $sql);
    }
    static function sendActivationMail($mysqli, $email, $code) {

        $uuid = user::getUUIDFromEmail($mysqli, $email);
        $name = user::getNameByUUID($mysqli, $uuid);

        $to = $email;
        $subject = "Feedback";
        $htmlContent = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta name="viewport" content="width=device-width" />

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>InstaWall</title>

        </head>

        <body bgcolor="#FFFFFF">
        <style>
        /* -------------------------------------
                GLOBAL
        ------------------------------------- */
        * {
            margin:0;
            padding:0;
        }
        * { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }

        img {
            max-width: 100%;
        }
        .collapse {
            margin:0;
            padding:0;
        }
        body {
            -webkit-font-smoothing:antialiased;
            -webkit-text-size-adjust:none;
            width: 100%!important;
            height: 100%;
        }


        /* -------------------------------------
                ELEMENTS
        ------------------------------------- */
        a { color: #2BA6CB;}

        .btn {
            text-decoration:none;
            color: #FFF;
            background-color: #666;
            padding:10px 16px;
            font-weight:bold;
            margin-right:10px;
            text-align:center;
            cursor:pointer;
            display: inline-block;
        }

        p.callout {
            padding:15px;
            background-color:#ECF8FF;
            margin-bottom: 15px;
        }
        .callout a {
            font-weight:bold;
            color: #2BA6CB;
        }

        table.social {
        /* 	padding:15px; */
            background-color: #ebebeb;

        }
        .social .soc-btn {
            padding: 3px 7px;
            font-size:12px;
            margin-bottom:10px;
            text-decoration:none;
            color: #FFF;font-weight:bold;
            display:block;
            text-align:center;
        }
        a.fb { background-color: #3B5998!important; }
        a.tw { background-color: #1daced!important; }
        a.gp { background-color: #DB4A39!important; }
        a.ms { background-color: #000!important; }

        .sidebar .soc-btn {
            display:block;
            width:100%;
        }

        /* -------------------------------------
                HEADER
        ------------------------------------- */
        table.head-wrap { width: 100%;}

        .header.container table td.logo { padding: 15px; }
        .header.container table td.label { padding: 15px; padding-left:0px;}


        /* -------------------------------------
                BODY
        ------------------------------------- */
        table.body-wrap { width: 100%;}


        /* -------------------------------------
                FOOTER
        ------------------------------------- */
        table.footer-wrap { width: 100%;	clear:both!important;
        }
        .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
        .footer-wrap .container td.content p {
            font-size:10px;
            font-weight: bold;

        }


        /* -------------------------------------
                TYPOGRAPHY
        ------------------------------------- */
        h1,h2,h3,h4,h5,h6 {
        font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000;
        }
        h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }

        h1 { font-weight:200; font-size: 44px;}
        h2 { font-weight:200; font-size: 37px;}
        h3 { font-weight:500; font-size: 27px;}
        h4 { font-weight:500; font-size: 23px;}
        h5 { font-weight:900; font-size: 17px;}
        h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}

        .collapse { margin:0!important;}

        p, ul {
            margin-bottom: 10px;
            font-weight: normal;
            font-size:14px;
            line-height:1.6;
        }
        p.lead { font-size:17px; }
        p.last { margin-bottom:0px;}

        ul li {
            margin-left:5px;
            list-style-position: inside;
        }

        /* -------------------------------------
                SIDEBAR
        ------------------------------------- */
        ul.sidebar {
            background:#ebebeb;
            display:block;
            list-style-type: none;
        }
        ul.sidebar li { display: block; margin:0;}
        ul.sidebar li a {
            text-decoration:none;
            color: #666;
            padding:10px 16px;
        /* 	font-weight:bold; */
            margin-right:10px;
        /* 	text-align:center; */
            cursor:pointer;
            border-bottom: 1px solid #777777;
            border-top: 1px solid #FFFFFF;
            display:block;
            margin:0;
        }
        ul.sidebar li a.last { border-bottom-width:0px;}
        ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}



        /* ---------------------------------------------------
                RESPONSIVENESS
                Nuke it from orbit. It\'s the only way to be sure.
        ------------------------------------------------------ */

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display:block!important;
            max-width:600px!important;
            margin:0 auto!important; /* makes it centered */
            clear:both!important;
        }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            padding:15px;
            max-width:600px;
            margin:0 auto;
            display:block;
        }

        /* Let\'s make sure tables in the content area are 100% wide */
        .content table { width: 100%; }


        /* Odds and ends */
        .column {
            width: 300px;
            float:left;
        }
        .column tr td { padding: 15px; }
        .column-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .column table { width:100%;}
        .social .column {
            width: 280px;
            min-width: 279px;
            float:left;
        }

        /* Be sure to place a .clear element after each set of columns, just to be safe */
        .clear { display: block; clear: both; }


        /* -------------------------------------------
                PHONE
                For clients that support media queries.
                Nothing fancy.
        -------------------------------------------- */
        @media only screen and (max-width: 600px) {

            a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}

            div[class="column"] { width: auto!important; float:none!important;}

            table.social div[class="column"] {
                width:auto!important;
            }

        }
        </style>
        <!-- HEADER -->
        <table class="head-wrap" bgcolor="#f44242">
            <tr>
                <td></td>
                <td class="header container" >

                        <div class="content">
                            <h3><span style="color: white;">InstaWall</span></h3>
                        </div>

                </td>
                <td></td>
            </tr>
        </table><!-- /HEADER -->


        <!-- BODY -->
        <table class="body-wrap">
            <tr>
                <td></td>
                <td class="container" bgcolor="#FFFFFF">

                    <div class="content">
                    <table>
                        <tr>
                            <td>
                                <h3>Beste, '.$name.'</h3>
                                <p class="lead">Wat leuk dat je hebt geregistreerd op InstaWall!.</p>
                                <p>Je bent van plan om bij een van de grootste Minecraft pretpark community aan te sluiten.</p>
                                <p>InstaWall heeft de volgende functies:</p>
                                <ul>
                                    <li>Je favoriete parken te volgen</i>
                                    <li>Je eigen park aanmelden</i>
                                    <li>Artikelen schrijven over en voor jouw park</i>
                                    <li>En nog veel meer...</i>
                                </ul>
                                <!-- Callout Panel -->
                                <p class="callout">
                                    Je kunt je account activeren door <a href="http://daniquedejong.nl/instawall/activate.php?code='.$code.'">hier</a> te klikken.
                                </p><!-- /Callout Panel -->
                                <table class="social" width="100%">
                                    <tbody><tr>
                                        <td>

                                            <!-- column 1 -->
                                            <table align="left" class="column">
                                                <tbody><tr>
                                                    <td>

                                                        <h5 class="">heb je nog vragen? Je kunt ons hier bereiken:</h5>
                                                        <p class=""><a href="https://www.facebook.com/ParkCraft-370915049752819/" class="soc-btn fb">Facebook</a> <a href="https://twitter.com/ParkenCraft" class="soc-btn tw">Twitter</a> <a href="https://www.youtube.com/ParkCraft" class="soc-btn gp">YouTube</a></p>


                                                    </td>
                                                </tr>
                                            </tbody></table><!-- /column 1 -->

                                            <!-- column 2 -->
                                            <table align="left" class="column">
                                                <tbody><tr>
                                                    <td>

                                                        <h5 class="">Heb je fouten gevonden?</h5>
                                                        <p>Email: <strong><a href="emailto:info@daniquedejong.nl">info@daniquedejong.nl</a></strong></p>

                                                    </td>
                                                </tr>
                                            </tbody></table><!-- /column 2 -->

                                            <span class="clear"></span>

                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </table>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>

        </body>
        </html>';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@daniquedejong.nl' . "\r\n";
        mail($to,$subject,$htmlContent,$headers);
    }
    static function sendChangePassword($mysqli, $email) {

        $uuid = user::getUUIDFromEmail($mysqli, $email);
        $name = user::getNameByUUID($mysqli, $uuid);
        $code = common::random(30);

        $sql = "UPDATE pco_users SET changepassword='$code' WHERE email='$email'";
        $result = mysqli_query($mysqli, $sql);


        $to = $email;
        $subject = "Wachtwoord vergeten";
        $htmlContent = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta name="viewport" content="width=device-width" />

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>InstaWall</title>

        </head>

        <body bgcolor="#FFFFFF">
        <style>
        /* -------------------------------------
                GLOBAL
        ------------------------------------- */
        * {
            margin:0;
            padding:0;
        }
        * { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }

        img {
            max-width: 100%;
        }
        .collapse {
            margin:0;
            padding:0;
        }
        body {
            -webkit-font-smoothing:antialiased;
            -webkit-text-size-adjust:none;
            width: 100%!important;
            height: 100%;
        }


        /* -------------------------------------
                ELEMENTS
        ------------------------------------- */
        a { color: #2BA6CB;}

        .btn {
            text-decoration:none;
            color: #FFF;
            background-color: #666;
            padding:10px 16px;
            font-weight:bold;
            margin-right:10px;
            text-align:center;
            cursor:pointer;
            display: inline-block;
        }

        p.callout {
            padding:15px;
            background-color:#ECF8FF;
            margin-bottom: 15px;
        }
        .callout a {
            font-weight:bold;
            color: #2BA6CB;
        }

        table.social {
        /* 	padding:15px; */
            background-color: #ebebeb;

        }
        .social .soc-btn {
            padding: 3px 7px;
            font-size:12px;
            margin-bottom:10px;
            text-decoration:none;
            color: #FFF;font-weight:bold;
            display:block;
            text-align:center;
        }
        a.fb { background-color: #3B5998!important; }
        a.tw { background-color: #1daced!important; }
        a.gp { background-color: #DB4A39!important; }
        a.ms { background-color: #000!important; }

        .sidebar .soc-btn {
            display:block;
            width:100%;
        }

        /* -------------------------------------
                HEADER
        ------------------------------------- */
        table.head-wrap { width: 100%;}

        .header.container table td.logo { padding: 15px; }
        .header.container table td.label { padding: 15px; padding-left:0px;}


        /* -------------------------------------
                BODY
        ------------------------------------- */
        table.body-wrap { width: 100%;}


        /* -------------------------------------
                FOOTER
        ------------------------------------- */
        table.footer-wrap { width: 100%;	clear:both!important;
        }
        .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
        .footer-wrap .container td.content p {
            font-size:10px;
            font-weight: bold;

        }


        /* -------------------------------------
                TYPOGRAPHY
        ------------------------------------- */
        h1,h2,h3,h4,h5,h6 {
        font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000;
        }
        h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }

        h1 { font-weight:200; font-size: 44px;}
        h2 { font-weight:200; font-size: 37px;}
        h3 { font-weight:500; font-size: 27px;}
        h4 { font-weight:500; font-size: 23px;}
        h5 { font-weight:900; font-size: 17px;}
        h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}

        .collapse { margin:0!important;}

        p, ul {
            margin-bottom: 10px;
            font-weight: normal;
            font-size:14px;
            line-height:1.6;
        }
        p.lead { font-size:17px; }
        p.last { margin-bottom:0px;}

        ul li {
            margin-left:5px;
            list-style-position: inside;
        }

        /* -------------------------------------
                SIDEBAR
        ------------------------------------- */
        ul.sidebar {
            background:#ebebeb;
            display:block;
            list-style-type: none;
        }
        ul.sidebar li { display: block; margin:0;}
        ul.sidebar li a {
            text-decoration:none;
            color: #666;
            padding:10px 16px;
        /* 	font-weight:bold; */
            margin-right:10px;
        /* 	text-align:center; */
            cursor:pointer;
            border-bottom: 1px solid #777777;
            border-top: 1px solid #FFFFFF;
            display:block;
            margin:0;
        }
        ul.sidebar li a.last { border-bottom-width:0px;}
        ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}



        /* ---------------------------------------------------
                RESPONSIVENESS
                Nuke it from orbit. It\'s the only way to be sure.
        ------------------------------------------------------ */

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display:block!important;
            max-width:600px!important;
            margin:0 auto!important; /* makes it centered */
            clear:both!important;
        }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            padding:15px;
            max-width:600px;
            margin:0 auto;
            display:block;
        }

        /* Let\'s make sure tables in the content area are 100% wide */
        .content table { width: 100%; }


        /* Odds and ends */
        .column {
            width: 300px;
            float:left;
        }
        .column tr td { padding: 15px; }
        .column-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .column table { width:100%;}
        .social .column {
            width: 280px;
            min-width: 279px;
            float:left;
        }

        /* Be sure to place a .clear element after each set of columns, just to be safe */
        .clear { display: block; clear: both; }


        /* -------------------------------------------
                PHONE
                For clients that support media queries.
                Nothing fancy.
        -------------------------------------------- */
        @media only screen and (max-width: 600px) {

            a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}

            div[class="column"] { width: auto!important; float:none!important;}

            table.social div[class="column"] {
                width:auto!important;
            }

        }
        </style>
        <!-- HEADER -->
        <table class="head-wrap" bgcolor="#f44242">
            <tr>
                <td></td>
                <td class="header container" >

                        <div class="content">
                            <h3><span style="color: white;">InstaWall</span></h3>
                        </div>

                </td>
                <td></td>
            </tr>
        </table><!-- /HEADER -->


        <!-- BODY -->
        <table class="body-wrap">
            <tr>
                <td></td>
                <td class="container" bgcolor="#FFFFFF">

                    <div class="content">
                    <table>
                        <tr>
                            <td>
                                <h3>Beste, '.$name.'</h3>
                                <p class="lead">Je bent je wachtwoord vergeten. Je kunt hieronder je wachtwoord verander.</p>
                                <!-- Callout Panel -->
                                <p class="callout">
                                    Je kunt je wachtwoord veranderen door <a href="http://daniquedejong.nl/instawall/index.php?changepassword=&code='.$code.'">hier</a> te klikken.
                                </p><!-- /Callout Panel -->
                                <table class="social" width="100%">
                                    <tbody><tr>
                                        <td>

                                            <!-- column 1 -->
                                            <table align="left" class="column">
                                                <tbody><tr>
                                                    <td>

                                                        <h5 class="">heb je nog vragen? Je kunt ons hier bereiken:</h5>
                                                        <p class=""><a href="https://www.facebook.com/ParkCraft-370915049752819/" class="soc-btn fb">Facebook</a> <a href="https://twitter.com/ParkenCraft" class="soc-btn tw">Twitter</a> <a href="https://www.youtube.com/ParkCraft" class="soc-btn gp">YouTube</a></p>


                                                    </td>
                                                </tr>
                                            </tbody></table><!-- /column 1 -->

                                            <!-- column 2 -->
                                            <table align="left" class="column">
                                                <tbody><tr>
                                                    <td>

                                                        <h5 class="">Heb je fouten gevonden?</h5>
                                                        <p>Email: <strong><a href="emailto:info@daniquedejong.nl">info@daniquedejong.nl</a></strong></p>

                                                    </td>
                                                </tr>
                                            </tbody></table><!-- /column 2 -->

                                            <span class="clear"></span>

                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </table>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>

        </body>
        </html>';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@daniquedejong.nl' . "\r\n";
        mail($to,$subject,$htmlContent,$headers);
    }
    static function sendEmail($mysqli, $email, $subject, $body) {

        $uuid = user::getUUIDFromEmail($mysqli, $email);
        $name = user::getNameByUUID($mysqli, $uuid);


        $to = $email;
        $htmlContent = '
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
        <meta name="viewport" content="width=device-width" />

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>InstaWall</title>

        </head>

        <body bgcolor="#FFFFFF">
        <style>
        /* -------------------------------------
                GLOBAL
        ------------------------------------- */
        * {
            margin:0;
            padding:0;
        }
        * { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }

        img {
            max-width: 100%;
        }
        .collapse {
            margin:0;
            padding:0;
        }
        body {
            -webkit-font-smoothing:antialiased;
            -webkit-text-size-adjust:none;
            width: 100%!important;
            height: 100%;
        }


        /* -------------------------------------
                ELEMENTS
        ------------------------------------- */
        a { color: #2BA6CB;}

        .btn {
            text-decoration:none;
            color: #FFF;
            background-color: #666;
            padding:10px 16px;
            font-weight:bold;
            margin-right:10px;
            text-align:center;
            cursor:pointer;
            display: inline-block;
        }

        p.callout {
            padding:15px;
            background-color:#ECF8FF;
            margin-bottom: 15px;
        }
        .callout a {
            font-weight:bold;
            color: #2BA6CB;
        }

        table.social {
        /* 	padding:15px; */
            background-color: #ebebeb;

        }
        .social .soc-btn {
            padding: 3px 7px;
            font-size:12px;
            margin-bottom:10px;
            text-decoration:none;
            color: #FFF;font-weight:bold;
            display:block;
            text-align:center;
        }
        a.fb { background-color: #3B5998!important; }
        a.tw { background-color: #1daced!important; }
        a.gp { background-color: #DB4A39!important; }
        a.ms { background-color: #000!important; }

        .sidebar .soc-btn {
            display:block;
            width:100%;
        }

        /* -------------------------------------
                HEADER
        ------------------------------------- */
        table.head-wrap { width: 100%;}

        .header.container table td.logo { padding: 15px; }
        .header.container table td.label { padding: 15px; padding-left:0px;}


        /* -------------------------------------
                BODY
        ------------------------------------- */
        table.body-wrap { width: 100%;}


        /* -------------------------------------
                FOOTER
        ------------------------------------- */
        table.footer-wrap { width: 100%;	clear:both!important;
        }
        .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
        .footer-wrap .container td.content p {
            font-size:10px;
            font-weight: bold;

        }


        /* -------------------------------------
                TYPOGRAPHY
        ------------------------------------- */
        h1,h2,h3,h4,h5,h6 {
        font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000;
        }
        h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }

        h1 { font-weight:200; font-size: 44px;}
        h2 { font-weight:200; font-size: 37px;}
        h3 { font-weight:500; font-size: 27px;}
        h4 { font-weight:500; font-size: 23px;}
        h5 { font-weight:900; font-size: 17px;}
        h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}

        .collapse { margin:0!important;}

        p, ul {
            margin-bottom: 10px;
            font-weight: normal;
            font-size:14px;
            line-height:1.6;
        }
        p.lead { font-size:17px; }
        p.last { margin-bottom:0px;}

        ul li {
            margin-left:5px;
            list-style-position: inside;
        }

        /* -------------------------------------
                SIDEBAR
        ------------------------------------- */
        ul.sidebar {
            background:#ebebeb;
            display:block;
            list-style-type: none;
        }
        ul.sidebar li { display: block; margin:0;}
        ul.sidebar li a {
            text-decoration:none;
            color: #666;
            padding:10px 16px;
        /* 	font-weight:bold; */
            margin-right:10px;
        /* 	text-align:center; */
            cursor:pointer;
            border-bottom: 1px solid #777777;
            border-top: 1px solid #FFFFFF;
            display:block;
            margin:0;
        }
        ul.sidebar li a.last { border-bottom-width:0px;}
        ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}



        /* ---------------------------------------------------
                RESPONSIVENESS
                Nuke it from orbit. It\'s the only way to be sure.
        ------------------------------------------------------ */

        /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
        .container {
            display:block!important;
            max-width:600px!important;
            margin:0 auto!important; /* makes it centered */
            clear:both!important;
        }

        /* This should also be a block element, so that it will fill 100% of the .container */
        .content {
            padding:15px;
            max-width:600px;
            margin:0 auto;
            display:block;
        }

        /* Let\'s make sure tables in the content area are 100% wide */
        .content table { width: 100%; }


        /* Odds and ends */
        .column {
            width: 300px;
            float:left;
        }
        .column tr td { padding: 15px; }
        .column-wrap {
            padding:0!important;
            margin:0 auto;
            max-width:600px!important;
        }
        .column table { width:100%;}
        .social .column {
            width: 280px;
            min-width: 279px;
            float:left;
        }

        /* Be sure to place a .clear element after each set of columns, just to be safe */
        .clear { display: block; clear: both; }


        /* -------------------------------------------
                PHONE
                For clients that support media queries.
                Nothing fancy.
        -------------------------------------------- */
        @media only screen and (max-width: 600px) {

            a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}

            div[class="column"] { width: auto!important; float:none!important;}

            table.social div[class="column"] {
                width:auto!important;
            }

        }
        </style>
        <!-- HEADER -->
        <table class="head-wrap" bgcolor="#f44242">
            <tr>
                <td></td>
                <td class="header container" >

                        <div class="content">
                            <h3><span style="color: white;">InstaWall</span></h3>
                        </div>

                </td>
                <td></td>
            </tr>
        </table><!-- /HEADER -->


        <!-- BODY -->
        <table class="body-wrap">
            <tr>
                <td></td>
                <td class="container" bgcolor="#FFFFFF">

                    <div class="content">
                    <table>
                        <tr>
                            <td>
                                <h3>Beste, '.$name.'</h3>
                                <p class="lead">'.$body.'</p>
                                <!-- Callout Panel -->
                                <table class="social" width="100%">
                                    <tbody><tr>
                                        <td>

                                            <!-- column 1 -->
                                            <table align="left" class="column">
                                                <tbody><tr>
                                                    <td>

                                                        <h5 class="">heb je nog vragen? Je kunt ons hier bereiken:</h5>
                                                        <p class=""><a href="https://www.facebook.com/ParkCraft-370915049752819/" class="soc-btn fb">Facebook</a> <a href="https://twitter.com/ParkenCraft" class="soc-btn tw">Twitter</a> <a href="https://www.youtube.com/ParkCraft" class="soc-btn gp">YouTube</a></p>


                                                    </td>
                                                </tr>
                                            </tbody></table><!-- /column 1 -->

                                            <!-- column 2 -->
                                            <table align="left" class="column">
                                                <tbody><tr>
                                                    <td>

                                                        <h5 class="">Heb je fouten gevonden?</h5>
                                                        <p>Email: <strong><a href="emailto:info@daniquedejong.nl">info@daniquedejong.nl</a></strong></p>

                                                    </td>
                                                </tr>
                                            </tbody></table><!-- /column 2 -->

                                            <span class="clear"></span>

                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </table>
                    </div>
                </td>
                <td></td>
            </tr>
        </table>

        </body>
        </html>';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: noreply@daniquedejong.nl' . "\r\n";
        mail($to,$subject,$htmlContent,$headers);
    }
    static function loadFollowedParks($mysqli, $uuid)
    {
        $sql = "SELECT * FROM pco_parks WHERE followers LIKE '%{$uuid}%'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            echo '
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Naam</th>
                            <th>Opties</th>
                        </tr>
                    </thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                if(!park::isDeleted($mysqli, $row['ID'])){
                    $logo = $row['logo'];
                    if(empty($row['logo'])) {
                       $logo = 'resources/defaultavatar.png';
                    }
                    $name = $row['name'];
                    $parkid = $row['ID'];
                    echo '<tr>';
                    echo '<td><img src="' . $logo . '" alt="" class="avatar"/></td>';
                    echo '<td><a href="park.php?id=' . $parkid . '" class="">'.$name.'</a></td>';
                    echo '<td><a href="park.php?id=' . $parkid . '&unfollow=&bts=" class="btn btn-danger btn-sm">Ontvolgen</a></td>';
                    echo '</tr>';
                }
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Je volgt geen parken.</p>';
        }
    }
    static function loadAllUsers($mysqli, $pageid) {
        $pageusers = $pageid*50;
        $sql="SELECT * FROM pco_users LIMIT $pageusers, 50";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            echo '
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>Email</th>
                            <th>Rank</th>
                            <th>Toegang</th>
                            <th>Geactiveerd</th>
                            <th>Laatst Online</th>
                            <th>Opties</th>
                        </tr>
                    </thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                $id = $row['ID'];
                $uuid = $row['UUID'];
                $name = $row['name'];
                $email = $row['email'];
                $rank = user::getRankByUUID($mysqli, $uuid);
                $access = $row['access'];
                $activated = $row['activated'];
                $lastonline = $row['last_execution'];
                if($activated == 1) {
                    $activated = 'Ja';
                } else {
                    $activated = 'Nee';
                }
                if($access == 1) {
                    $access = 'Ja';
                } else {
                    $access = 'Nee';
                }
                echo '<tr>';
                echo '<td>'.$name.'</td>';
                echo '<td>'.$email.'</td>';
                echo '<td>'.$rank.'</td>';
                echo '<td>'.$access.'</td>';
                echo '<td>'.$activated.'</td>';
                echo '<td>'.$lastonline.'</td>';
                echo '<td><a href="staff.php?users=&id=' . $uuid . '&pi=' . ($pageid + 1) . '" class="btn btn-danger btn-sm">Bekijken</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            $sql1="SELECT * FROM pco_users";
            $result1 = mysqli_query($mysqli, $sql1);
            $count1 = mysqli_num_rows($result1);
            if($count1 > ($pageid+1)*50) {
                if($pageid > 0) {
                    echo '<a href="staff.php?users=&page=' . ($pageid) . '" class="btn btn-danger btn-sm">Terug</a>';
                }
                echo '<a href="staff.php?users=&page=' . ($pageid + 2) . '" class="btn btn-danger btn-sm">Volgende</a>';
            } else {
                if ($pageid > 0) {
                    echo '<a href="staff.php?users=&page=' . ($pageid) . '" class="btn btn-danger btn-sm">Terug</a>';
                    if ($count1 > ($pageid + 1) * 50) {
                        echo '<a href="staff.php?users=&page=' . ($pageid + 2) . '" class="btn btn-danger btn-sm">Volgende</a>';
                    }
                }
            }
        } else {
            echo '<p>Geen gebruikers gevonden op deze pagina.</p>';
        }
    }
    static function loadAllUsersSearch($mysqli, $keyword) {
        $sql="SELECT * FROM pco_users WHERE name LIKE '%{$keyword}%' OR email LIKE '%{$keyword}%' OR UUID LIKE '%{$keyword}%'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            if($count > 50) {
                echo '<p>Geef een specefiekere zoekopdracht!</p>';
                exit;
            }
            echo '
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Naam</th>
                            <th>Email</th>
                            <th>Rank</th>
                            <th>Toegang</th>
                            <th>Geactiveerd</th>
                            <th>Laatst Online</th>
                            <th>Opties</th>
                        </tr>
                    </thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                $id = $row['ID'];
                $uuid = $row['UUID'];
                $name = $row['name'];
                $email = $row['email'];
                $rank = user::getRankByUUID($mysqli, $uuid);
                $access = $row['access'];
                $activated = $row['activated'];
                $lastonline = $row['last_execution'];
                if($activated == 1) {
                    $activated = 'Ja';
                } else {
                    $activated = 'Nee';
                }
                if($access == 1) {
                    $access = 'Ja';
                } else {
                    $access = 'Nee';
                }
                echo '<tr>';
                echo '<td>'.$name.'</td>';
                echo '<td>'.$email.'</td>';
                echo '<td>'.$rank.'</td>';
                echo '<td>'.$access.'</td>';
                echo '<td>'.$activated.'</td>';
                echo '<td>'.$lastonline.'</td>';
                echo '<td><a href="staff.php?users=&id=' . $uuid . '&pi=1" class="btn btn-danger btn-sm">Bekijken</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Geen gebruikers gevonden op deze pagina.</p>';
        }
    }
    static function loadUserIn($mysqli, $uuid) {
        $sql="SELECT * FROM pco_users WHERE UUID='$uuid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            $file = htmlspecialchars($_SERVER["PHP_SELF"]);
            echo '                    <form name="edituser" id="edituser" action="'.$file.'" enctype="multipart/form-data" method="post" autocomplete="off" class="form-horizontal">';
            echo '                              <div class="form-group">
                                                    <label for="title" class="col-md-2 control-label"><span class="text-info">Naam</span></label>
                                                    <div class="col-md-10" id="naamdiv">
                                                        <input type="text" class="form-control" value="'.$row["name"].'" name="name" id="name"/>
                                                        <input type="hidden" class="form-control" value="'.$_GET['pi'].'" name="pi" id="pi"/>
                                                        <input type="hidden" class="form-control" value="'.$_GET['id'].'" name="uuid" id="uuid"/>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="col-md-2 control-label"><span class="text-info">Email</span></label>
                                                    <div class="col-md-10" id="naamdiv">
                                                        <input type="email" class="form-control" value="'.$row["email"].'" name="email" id="email"/>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="col-md-2 control-label"><span class="text-info">Account geactiveerd</span></label>
                                                    <div class="col-md-10" id="naamdiv">
                                                        <select name="ag" class="form-control">
                                                            <option value="1" '; if($row["activated"] == 1) { echo "selected"; } echo '>Ja</option>
                                                            <option value="0" '; if($row["activated"] != 1) { echo "selected"; } echo '>Nee</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="col-md-2 control-label"><span class="text-info">Toegang tot InstaWall</span></label>
                                                    <div class="col-md-10" id="naamdiv">
                                                        <select name="ttpo" class="form-control">
                                                            <option value="1" '; if($row["access"] == 1) { echo "selected"; } echo '>Ja</option>
                                                            <option value="0" '; if($row["access"] != 1) { echo "selected"; } echo '>Nee</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="title" class="col-md-2 control-label"><span class="text-info">Rank</span></label>
                                                    <div class="col-md-10" id="naamdiv">
                                                        <select name="rank" class="form-control">
                                                            <option value="5" '; if(user::getRankByUUID($mysqli, $row["UUID"]) == 5) { echo "selected"; } echo '>Auteur</option>
                                                            <option value="4" '; if(user::getRankByUUID($mysqli, $row["UUID"]) == 4) { echo "selected"; } echo '>Developer</option>
                                                            <option value="3" '; if(user::getRankByUUID($mysqli, $row["UUID"]) == 3) { echo "selected"; } echo '>Beheerder</option>
                                                            <option value="2" '; if(user::getRankByUUID($mysqli, $row["UUID"]) == 2) { echo "selected"; } echo '>Support</option>
                                                            <option value="1" '; if(user::getRankByUUID($mysqli, $row["UUID"]) == 1) { echo "selected"; } echo '>Moderator</option>
                                                            <option value="0" '; if(user::getRankByUUID($mysqli, $row["UUID"]) == 0) { echo "selected"; } echo '>Gebruiker</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <p class="col-md-2 control-label">Laatste online:</p>
                                                    <p class="form-control">'.$row["last_execution"].'</p>
                                                </div>
                                                <div class="text-center"">
                                                    <button type="submit" class="btn btn-raised btn-success" name="edituserbutton" id="edituserbutton">Opslaan
                                                    </button>
                                                </div>
                                </form>';
        }
    }
    static function sendEmailToEveryone($mysqli, $subject, $body) {
        if(!user::getRank($mysqli) > 2) {
            header("Location: staff.php?warning=Geen toegang tot dit gedeelte.");
            exit;
        }
        $sql="SELECT * FROM pco_users WHERE news_mail='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while ($row = mysqli_fetch_assoc($result)) {
            $uuid = $row['UUID'];
            $name = $row['name'];
            $email = $row['email'];

            $to = $email;
            $htmlContent = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
            <meta name="viewport" content="width=device-width" />

            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <title>InstaWall</title>

            </head>

            <body bgcolor="#FFFFFF">
            <style>
            /* -------------------------------------
                    GLOBAL
            ------------------------------------- */
            * {
                margin:0;
                padding:0;
            }
            * { font-family: "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif; }

            img {
                max-width: 100%;
            }
            .collapse {
                margin:0;
                padding:0;
            }
            body {
                -webkit-font-smoothing:antialiased;
                -webkit-text-size-adjust:none;
                width: 100%!important;
                height: 100%;
            }


            /* -------------------------------------
                    ELEMENTS
            ------------------------------------- */
            a { color: #2BA6CB;}

            .btn {
                text-decoration:none;
                color: #FFF;
                background-color: #666;
                padding:10px 16px;
                font-weight:bold;
                margin-right:10px;
                text-align:center;
                cursor:pointer;
                display: inline-block;
            }

            p.callout {
                padding:15px;
                background-color:#ECF8FF;
                margin-bottom: 15px;
            }
            .callout a {
                font-weight:bold;
                color: #2BA6CB;
            }

            table.social {
            /* 	padding:15px; */
                background-color: #ebebeb;

            }
            .social .soc-btn {
                padding: 3px 7px;
                font-size:12px;
                margin-bottom:10px;
                text-decoration:none;
                color: #FFF;font-weight:bold;
                display:block;
                text-align:center;
            }
            a.fb { background-color: #3B5998!important; }
            a.tw { background-color: #1daced!important; }
            a.gp { background-color: #DB4A39!important; }
            a.ms { background-color: #000!important; }

            .sidebar .soc-btn {
                display:block;
                width:100%;
            }

            /* -------------------------------------
                    HEADER
            ------------------------------------- */
            table.head-wrap { width: 100%;}

            .header.container table td.logo { padding: 15px; }
            .header.container table td.label { padding: 15px; padding-left:0px;}


            /* -------------------------------------
                    BODY
            ------------------------------------- */
            table.body-wrap { width: 100%;}


            /* -------------------------------------
                    FOOTER
            ------------------------------------- */
            table.footer-wrap { width: 100%;	clear:both!important;
            }
            .footer-wrap .container td.content  p { border-top: 1px solid rgb(215,215,215); padding-top:15px;}
            .footer-wrap .container td.content p {
                font-size:10px;
                font-weight: bold;

            }


            /* -------------------------------------
                    TYPOGRAPHY
            ------------------------------------- */
            h1,h2,h3,h4,h5,h6 {
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif; line-height: 1.1; margin-bottom:15px; color:#000;
            }
            h1 small, h2 small, h3 small, h4 small, h5 small, h6 small { font-size: 60%; color: #6f6f6f; line-height: 0; text-transform: none; }

            h1 { font-weight:200; font-size: 44px;}
            h2 { font-weight:200; font-size: 37px;}
            h3 { font-weight:500; font-size: 27px;}
            h4 { font-weight:500; font-size: 23px;}
            h5 { font-weight:900; font-size: 17px;}
            h6 { font-weight:900; font-size: 14px; text-transform: uppercase; color:#444;}

            .collapse { margin:0!important;}

            p, ul {
                margin-bottom: 10px;
                font-weight: normal;
                font-size:14px;
                line-height:1.6;
            }
            p.lead { font-size:17px; }
            p.last { margin-bottom:0px;}

            ul li {
                margin-left:5px;
                list-style-position: inside;
            }

            /* -------------------------------------
                    SIDEBAR
            ------------------------------------- */
            ul.sidebar {
                background:#ebebeb;
                display:block;
                list-style-type: none;
            }
            ul.sidebar li { display: block; margin:0;}
            ul.sidebar li a {
                text-decoration:none;
                color: #666;
                padding:10px 16px;
            /* 	font-weight:bold; */
                margin-right:10px;
            /* 	text-align:center; */
                cursor:pointer;
                border-bottom: 1px solid #777777;
                border-top: 1px solid #FFFFFF;
                display:block;
                margin:0;
            }
            ul.sidebar li a.last { border-bottom-width:0px;}
            ul.sidebar li a h1,ul.sidebar li a h2,ul.sidebar li a h3,ul.sidebar li a h4,ul.sidebar li a h5,ul.sidebar li a h6,ul.sidebar li a p { margin-bottom:0!important;}



            /* ---------------------------------------------------
                    RESPONSIVENESS
                    Nuke it from orbit. It\'s the only way to be sure.
            ------------------------------------------------------ */

            /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
            .container {
                display:block!important;
                max-width:600px!important;
                margin:0 auto!important; /* makes it centered */
                clear:both!important;
            }

            /* This should also be a block element, so that it will fill 100% of the .container */
            .content {
                padding:15px;
                max-width:600px;
                margin:0 auto;
                display:block;
            }

            /* Let\'s make sure tables in the content area are 100% wide */
            .content table { width: 100%; }


            /* Odds and ends */
            .column {
                width: 300px;
                float:left;
            }
            .column tr td { padding: 15px; }
            .column-wrap {
                padding:0!important;
                margin:0 auto;
                max-width:600px!important;
            }
            .column table { width:100%;}
            .social .column {
                width: 280px;
                min-width: 279px;
                float:left;
            }

            /* Be sure to place a .clear element after each set of columns, just to be safe */
            .clear { display: block; clear: both; }


            /* -------------------------------------------
                    PHONE
                    For clients that support media queries.
                    Nothing fancy.
            -------------------------------------------- */
            @media only screen and (max-width: 600px) {

                a[class="btn"] { display:block!important; margin-bottom:10px!important; background-image:none!important; margin-right:0!important;}

                div[class="column"] { width: auto!important; float:none!important;}

                table.social div[class="column"] {
                    width:auto!important;
                }

            }
            </style>
            <!-- HEADER -->
            <table class="head-wrap" bgcolor="#f44242">
                <tr>
                    <td></td>
                    <td class="header container" >

                            <div class="content">
                                <h3><span style="color: white;">InstaWall</span></h3>
                            </div>

                    </td>
                    <td></td>
                </tr>
            </table><!-- /HEADER -->


            <!-- BODY -->
            <table class="body-wrap">
                <tr>
                    <td></td>
                    <td class="container" bgcolor="#FFFFFF">

                        <div class="content">
                        <table>
                            <tr>
                                <td>
                                    <h3>Beste, ' . $name . '</h3>
                                    <p class="lead">' . $body . '</p>
                                    <!-- Callout Panel -->
                                    <table class="social" width="100%">
                                        <tbody><tr>
                                            <td>

                                                <!-- column 1 -->
                                                <table align="left" class="column">
                                                    <tbody><tr>
                                                        <td>

                                                            <h5 class="">heb je nog vragen? Je kunt ons hier bereiken:</h5>
                                                            <p class=""><a href="https://www.facebook.com/ParkCraft-370915049752819/" class="soc-btn fb">Facebook</a> <a href="https://twitter.com/ParkenCraft" class="soc-btn tw">Twitter</a> <a href="https://www.youtube.com/ParkCraft" class="soc-btn gp">YouTube</a></p>


                                                        </td>
                                                    </tr>
                                                </tbody></table><!-- /column 1 -->

                                                <!-- column 2 -->
                                                <table align="left" class="column">
                                                    <tbody><tr>
                                                        <td>

                                                            <h5 class="">Heb je fouten gevonden?</h5>
                                                            <p>Email: <strong><a href="emailto:info@daniquedejong.nl">info@daniquedejong.nl</a></strong></p>

                                                        </td>
                                                    </tr>
                                                </tbody></table><!-- /column 2 -->

                                                <span class="clear"></span>

                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </table>
                        </div>
                    </td>
                    <td></td>
                </tr>
            </table>

            </body>
            </html>';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: noreply@daniquedejong.nl' . "\r\n";
            mail($to, $subject, $htmlContent, $headers);
        }
    }
    static function isPlayerFollowingAnyPark($mysqli, $uuid) {
        $sql = "SELECT * FROM pco_parks WHERE followers LIKE '%{$uuid}%'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function getTimeNow() {
        setlocale(LC_TIME, 'NL_nl');
        $time = strftime('%H:%M %e-%m-%Y',time());
        return $time;
    }
    static function setLastExcecution($mysqli) {
        $sesuuid = $_SESSION['UUID'];
        $sql = "UPDATE pco_users SET last_execution='".user::getTimeNow()."' WHERE UUID='$sesuuid'";
        $result = mysqli_query($mysqli, $sql);
    }
    static function getLastExcecution($mysqli) {
        $sesuuid = $_SESSION['UUID'];
        $sql = "SELECT * FROM pco_users WHERE UUID='$sesuuid';";
        $result = mysqli_query($mysqli, $sql);
        $row = mysqli_fetch_assoc($result);
        if(empty($row['last_execution'])) {
            return 'n.v.t.';
        }
        return $row['last_execution'];
    }
    static function setReceiveNewsEmails($mysqli, $value) {
        $sesuuid = $_SESSION['UUID'];
        $sql = "UPDATE pco_users SET news_email='$value' WHERE UUID='$sesuuid'";
        $result = mysqli_query($mysqli, $sql);
    }
    static function getReceiveNewsEmails($mysqli, $sesuuid) {
        $sql = "SELECT * FROM pco_users WHERE UUID='$sesuuid'";
        $result = mysqli_query($mysqli, $sql);
        $row = mysqli_fetch_assoc($result);
        if($row['news_email'] == 0) {
            return false;
        } else {
            return true;
        }
        return false;
    }
    static function sendActivationMailToAll($mysqli) {
        $sql="SELECT * FROM pco_users WHERE activated NOT IN ('1', 1);";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while ($row = mysqli_fetch_assoc($result)) {
            user::sendActivationMail($mysqli, $row['email'], user::getActivationCode($mysqli, $row['UUID']));
            echo $row['email'].'<br />';
        }
    }
}
class posts {
    static function post($mysqli, $title, $image)
    {
        setlocale(LC_TIME, 'NL_nl');
        if(strpos($image, 'Invalid URL') !== false) {
            exit;
        }
        $title = strip_tags($title);
        $sql = "INSERT INTO pco_posts (user_id, post_text, post_image, posted_on) VALUES ('".$_SESSION["UUID"]."', '$title', '$image', '" . strftime('%e-%m-%Y om %H:%M', time()) . "');";
        $result = mysqli_query($mysqli, $sql);
    }

    static function isDeleted($mysqli, $postid) {
        $sql = "SELECT * FROM pco_posts WHERE ID='$postid' AND deleted='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        }
        return false;
    }
    static function deletepost($mysqli, $parkid, $postid, $userid) {
        if(park::CanEditSettings($mysqli, $parkid, $userid) || staff::canManagePosts($mysqli, $userid)) {
            $sql = "UPDATE pco_posts SET deleted='1' WHERE park_id='$parkid' AND ID='$postid'";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function undeletepost($mysqli, $parkid, $postid, $userid) {
        if(park::CanEditSettings($mysqli, $parkid, $userid) || staff::canManagePosts($mysqli, $userid)) {
            $sql = "UPDATE pco_posts SET deleted='0' WHERE park_id='$parkid' AND ID='$postid'";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function deletepoststaff($mysqli, $postid, $userid) {
        if(staff::canManagePosts($mysqli, $userid)) {
            $sql = "UPDATE pco_posts SET deleted='2' WHERE ID='$postid'";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function undeletepoststaff($mysqli, $postid, $userid) {
        if(staff::canManagePosts($mysqli, $userid)) {
            $sql = "UPDATE pco_posts SET deleted='0' WHERE ID='$postid'";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function exist($mysqli, $id) {
        $sql = "SELECT * FROM pco_posts WHERE ID=$id AND deleted='0'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            $sql1 = "SELECT * FROM pco_posts WHERE ID=$id AND deleted='1' OR deleted='2'";
            $result1 = mysqli_query($mysqli, $sql1);
            $count1 = mysqli_num_rows($result1);
            if($count1 > 0) {
                if (staff::canManagePosts($mysqli, $_SESSION['UUID'])) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }
    static function loadArticlesUser($mysqli, $userid) {
        $useruuid = user::getUUIDFromID($mysqli, $userid);
        $sql = "SELECT * FROM pco_posts WHERE user_id='$useruuid' AND deleted='0' order by ID desc";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count == 0) {
            echo '<p>Deze persoon heeft nog geen artikelen gepost.</p>';
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $title = $row['post_text'];
            $postid = $row['ID'];
            $parkname = user::getNameByUUID($mysqli, $row['user_id']);
            $logo = '';
            $post = common::random(20);
            $postheader = $row['post_image'];

            $icon = '';
            if (posts::isLiking($mysqli, $postid, $_SESSION['UUID'])) {
                $icon = 'favorite';
            } else {
                $icon = 'favorite_border';
            }
            $like = '';
            if (posts::isLiking($mysqli, $postid, $_SESSION['UUID'])) {
                $like = 'unlike';
            } else {
                $like = 'like';
            }
            echo '
                        <div class="jumbotron hover" id="' . $post . '">
                            <div>
                                <a href="profile.php?id='.user::getIDFromUUID($mysqli, $useruuid).'"><span style="color: black; font-weight: bold;"><span>' . $parkname . '</span></a>
                            </div>
                            <div>
                                <div>
                                    <img src="' . $postheader . '" alt="header" class="img-responsive center-block" style="max-height: 300px;"/>
                                </div>
                                <h3>' . $title . '</h3>
                            </div>
                            <script>
                                var id' . $post . ' = document.getElementById("' . $post . '");

                                id' . $post . '.onclick = function() {
                                    window.location.href = "post.php?id=' . $postid . '";
                                };
                            </script>
                            <span class="shortcut"><i class="material-icons heart"><a href="post.php?id=' . $postid . '&' . $like . '" style="text-decoration: none;">' . $icon . '</a></i><span><a href="article.php?id=' . $postid . '&likes" style="color: #000000; text-decoration: none;">' . posts::countLikes($mysqli, $postid) . '</a>
                            <span class="shortcut"><i class="material-icons">mode_comment</i><span>'.posts::getReactionCount($mysqli, $postid).'</span></span>
                            <i style="float: right;">Geplaatst op: ' . $row["posted_on"] . '</i>
                        </div>


                        ';
        }
    }
    static function loadArticles($mysqli)
    {
        $sql = "SELECT * FROM pco_posts WHERE deleted='0' order by ID desc";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while ($row = mysqli_fetch_assoc($result)) {
            $title = $row['post_text'];
            $postid = $row['ID'];
            $parkname = user::getNameByUUID($mysqli, $row['user_id']);
            $logo = '';
            $post = common::random(20);
            $postheader = $row['post_image'];

            $icon = '';
            if (posts::isLiking($mysqli, $postid, $_SESSION['UUID'])) {
                $icon = 'favorite';
            } else {
                $icon = 'favorite_border';
            }
            $like = '';
            if (posts::isLiking($mysqli, $postid, $_SESSION['UUID'])) {
                $like = 'unlike';
            } else {
                $like = 'like';
            }
            echo '
                        <div class="jumbotron hover" id="' . $post . '">
                            <div>
                                <a href="profile.php?id='.user::getIDFromUUID($mysqli, $row['user_id']).'"><span style="color: black; font-weight: bold;"><span>' . $parkname . '</span></a>
                            </div>
                            <div>
                                <div>
                                    <img src="' . $postheader . '" alt="header" class="img-responsive center-block" style="max-height: 300px;"/>
                                </div>
                                <h3>' . $title . '</h3>
                            </div>
                            <script>
                                var id' . $post . ' = document.getElementById("' . $post . '");

                                id' . $post . '.onclick = function() {
                                    window.location.href = "post.php?id=' . $postid . '";
                                };
                            </script>
                            <span class="shortcut"><i class="material-icons heart"><a href="post.php?id=' . $postid . '&' . $like . '" style="text-decoration: none;">' . $icon . '</a></i><span><a href="article.php?id=' . $postid . '&likes" style="color: #000000; text-decoration: none;">' . posts::countLikes($mysqli, $postid) . '</a>
                            <span class="shortcut"><i class="material-icons">mode_comment</i><span>'.posts::getReactionCount($mysqli, $postid).'</span></span>
                            <i style="float: right;">Geplaatst op: ' . $row["posted_on"] . '</i>
                        </div>


                        ';
        }
    }
    static function loadArticle($mysqli, $id) {
        $sql = "SELECT * FROM pco_posts WHERE ID='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        $parkname = user::getNameByUUID($mysqli, $row['user_id']);
        $logo = '';
        $body = $row['post_text'];
        $postheader = $row['post_image'];
        $url = "http://daniquedejong.nl/instawall/post.php?id=$id";
        $icon = '';
        if(posts::isLiking($mysqli, $id, $_SESSION['UUID'])) {
            $icon = 'favorite';
        } else {
            $icon = 'favorite_border';
        }
        $like = '';
        if(posts::isLiking($mysqli, $id, $_SESSION['UUID'])) {
            $like = 'unlike';
        } else {
            $like = 'like';
        }
        echo '
            <div>
                <div>
                    <a href="profile.php?id='.user::getIDFromUUID($mysqli, $row['user_id']).'"><span style="color: black; font-weight: bold;"><span>' . $parkname . '</span></a>
                </div>
                <div>
                    <img src="'.$postheader.'" alt="header" class="img-responsive center-block" style="max-height: 300px;"/>
                </div>
                <span>'.$body.'</span>
                <hr />
                <i style="float: right;">Geplaatst opx: '.$row["posted_on"].'</i>
                <span style="float: left;" class="shortcut"><i class="material-icons heart"><a href="?id='.$id.'&'.$like.'" style="text-decoration: none">'.$icon.'</a></i><span><a href="?id='.$id.'&likes" style="color: #000000; text-decoration: none;">'.posts::countLikes($mysqli, $id).'</a></span></span><br /><br />
                <ul class="share-buttons">
                  <li><a href="https://www.facebook.com/sharer/sharer.php?u='.$url.'&t='.$body.'" title="Share on Facebook" target="_blank"><img alt="Share on Facebook" src="resources/svg/Facebook.svg"></a></li>
                  <li><a href="https://twitter.com/intent/tweet?source='.$url.'&text='.$body.' '.$url.'&via=Limited_Dani" target="_blank" title="Tweet"><img alt="Tweet" src="resources/svg/Twitter.svg"></a></li>
                  <li><a href="http://www.reddit.com/submit?url='.$url.'&title='.$body.'" target="_blank" title="Submit to Reddit"><img alt="Submit to Reddit" src="resources/svg/Reddit.svg"></a></li>
                </ul>
            </div>
        ';
    }
    static function loadReactions($mysqli, $id) {
        $sql = "SELECT * FROM pco_reaction WHERE article_id='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {
            $rowid = $row['ID'];
                echo '
                <strong><span> ' . user::getNameByUUID($mysqli, $row['uuid']) . '</span></strong>
                <p>' . $row['reaction'] . '</p>';
                if ($row['uuid'] == $_SESSION['UUID'] || staff::canManageComments($mysqli, $_SESSION['UUID'])) {
                    echo '<a href="?remove=' . $rowid . '&id=' . $id . '">Verwijder</a>';
                }
                echo '<hr />';
        }
    }
    static function getReactionCount($mysqli, $id) {
        $sql = "SELECT * FROM pco_reaction WHERE article_id='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        return $count;
    }
    static function editArticle($mysqli, $parkid, $article_id, $title, $body, $userid) {
        if(park::CanWriteArticle($mysqli, $parkid, $userid) || staff::canManageParks($mysqli,  $userid)) {
            $sql = "UPDATE pco_posts SET post_title='$title', post_body='' WHERE park_id='$parkid' AND ID='$article_id'";
            $result = mysqli_query($mysqli, $sql);

            $splitted = str_split($body, 100);
            for($i = 0; $i < count($splitted); $i++) {
                $sql3 = "SELECT * FROM pco_posts WHERE park_id='$parkid' AND ID='$article_id' order by ID desc";
                $result3 = mysqli_query($mysqli, $sql3);
                $row3 = mysqli_fetch_assoc($result3);
                $oldbody = $row3['post_body'];

                $newbody = $oldbody.$splitted[$i];
                $sql2 = "UPDATE pco_posts SET post_body = '$newbody' WHERE ID='$article_id'";
                $result2 = mysqli_query($mysqli, $sql2);
            }
            return true;
        }
        return false;
    }
    static function PlaceReaction($mysqli, $id, $reaction) {
        $user = $_SESSION['UUID'];
        $reaction = strip_tags($reaction);
        $sql = "INSERT INTO pco_reaction (article_id, uuid, reaction) VALUES ('$id', '$user', '$reaction')";
        $result = mysqli_query($mysqli, $sql);
    }
    static function RemoveReaction($mysqli, $id) {
        $user = $_SESSION['UUID'];
        $sql = "SELECT * FROM pco_reaction WHERE ID='$id' AND uuid='$user'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0 || user::getRank($mysqli) > 2 ) {
            $sql = "DELETE FROM pco_reaction WHERE ID = $id";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function getTitle($mysqli, $id) {
        $sql="SELECT * FROM pco_posts WHERE ID='$id'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            return $row['post_text'];
        } else {
            return 'Artikel verwijderd';
        }
    }
    static function loadAllReactions($mysqli, $pageid) {
        $pageusers = $pageid*50;
        $sql="SELECT * FROM pco_reaction ORDER BY ID DESC LIMIT $pageusers, 50";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            echo '
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Gebruiker</th>
                            <th>Artikel</th>
                            <th>Reactie</th>
                            <th>Opties</th>
                        </tr>
                    </thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                $name = user::getNameByUUID($mysqli, $row['uuid']);
                $artikel = $row['article_id'];
                $reactie = $row['reaction'];
                echo '<tr>';
                echo '<td>'.$name.'</td>';
                echo '<td><a href="article.php?id=' . $artikel . '">'.posts::getTitle($mysqli, $row["article_id"]).'</a></td>';
                echo '<td><p style="word-wrap: break-word;">'.$reactie.'</p></td>';
                echo '<td><a href="staff.php?reactions=&id=' . $row['ID'] . '&pi=' . ($pageid + 1) . '&removereaction=" class="btn btn-danger btn-sm">Verwijder</a></td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            $sql1="SELECT * FROM pco_reaction";
            $result1 = mysqli_query($mysqli, $sql1);
            $count1 = mysqli_num_rows($result1);
            if($count1 > ($pageid+1)*50) {
                if($pageid > 0) {
                    echo '<a href="staff.php?reactions=&page=' . ($pageid) . '" class="btn btn-danger btn-sm">Terug</a>';
                }
                echo '<a href="staff.php?reactions=&page=' . ($pageid + 2) . '" class="btn btn-danger btn-sm">Volgende</a>';
            } else {
                if ($pageid > 0) {
                    echo '<a href="staff.php?reactions=&page=' . ($pageid) . '" class="btn btn-danger btn-sm">Terug</a>';
                    if ($count1 > ($pageid + 1) * 50) {
                        echo '<a href="staff.php?reactions=&page=' . ($pageid + 2) . '" class="btn btn-danger btn-sm">Volgende</a>';
                    }
                }
            }
        } else {
            echo '<p>Geen reacties gevonden op deze pagina.</p>';
        }
    }
    static function loadAllPosts($mysqli, $pageid) {
        $pageposts = $pageid*50;
        $sql="SELECT * FROM pco_posts ORDER BY ID DESC LIMIT $pageposts, 50";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            echo '
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Gebruiker</th>
                            <th>Artikel</th>
                            <th>Reacties</th>
                            <th>Opties</th>
                        </tr>
                    </thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                $id = $row['ID'];
                $title = $row['post_text'];
                $user = user::getNameByUUID($mysqli, $row['user_id']);
                $deleted = $row['deleted'];
                echo '<tr>';
                echo '<td><p>'.$user.'</p></td>';
                echo '<td><a href="post.php?id=' . $id . '">'.$title.'</a></td>';
                echo '<td><p style="word-wrap: break-word;">'.posts::getReactionCount($mysqli, $id).'</p></td>';
                if($deleted == 2 || $deleted == 1) {
                    echo '<td><a href="staff.php?posts=' . $id . '&undoremovepost=&pi='.($pageid + 1).'" class="btn btn-info btn-sm">Verwijderen ongedaan maken</a></td>';
                } else {
                    echo '<td><a href="staff.php?posts=' . $id . '&removepost=&pi='.($pageid + 1).'" class="btn btn-danger btn-sm">Verwijderen</a></td>';
                }
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
            $sql1="SELECT * FROM pco_reaction";
            $result1 = mysqli_query($mysqli, $sql1);
            $count1 = mysqli_num_rows($result1);
            if($count1 > ($pageid+1)*50) {
                if($pageid > 0) {
                    echo '<a href="staff.php?posts=&page=' . ($pageid) . '" class="btn btn-danger btn-sm">Terug</a>';
                }
                echo '<a href="staff.php?posts=&page=' . ($pageid + 2) . '" class="btn btn-danger btn-sm">Volgende</a>';
            } else {
                if ($pageid > 0) {
                    echo '<a href="staff.php?posts=&page=' . ($pageid) . '" class="btn btn-danger btn-sm">Terug</a>';
                    if ($count1 > ($pageid + 1) * 50) {
                        echo '<a href="staff.php?posts=&page=' . ($pageid + 2) . '" class="btn btn-danger btn-sm">Volgende</a>';
                    }
                }
            }
        } else {
            echo '<p>Geen artikelen gevonden op deze pagina.</p>';
        }
    }
    static function like($mysqli, $articleid, $uuid) {
        if(!posts::isLiking($mysqli, $articleid, $uuid)) {
            $sql="UPDATE pco_posts SET post_likes = CONCAT(post_likes,'".$uuid.",') WHERE ID='$articleid';";
            $result=mysqli_query($mysqli, $sql);
        }
    }
    static function unlike($mysqli, $articleid, $uuid) {
        if(posts::isLiking($mysqli, $articleid, $uuid)) {
            $sql = "UPDATE pco_posts SET post_likes = REPLACE(post_likes,'" . $uuid . ",','') WHERE ID='$articleid';";
            $result = mysqli_query($mysqli, $sql);
        }
    }
    static function isLiking($mysqli, $articleid, $uuid) {
        $sql = "SELECT * FROM pco_posts WHERE ID='$articleid' AND post_likes LIKE '%{$uuid}%'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function countLikes($mysqli, $articleid) {
        $sql="SELECT * FROM pco_posts WHERE ID='$articleid'";
        $result=mysqli_query($mysqli,$sql);
        $count=mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        $likes = explode(",", $row['post_likes']);
        return (count($likes)-1);
    }
    static function LoadLikes($mysqli, $postid) {
        $sql="SELECT * FROM pco_posts WHERE ID='$postid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            $row = mysqli_fetch_assoc($result);
            $followers = explode(",", $row['post_likes']);
            for($i = 0; $i < (count($followers)-1); $i++) {
                echo '
            <div>
                <span> </span><span class="label '.user::getLabel($mysqli, user::getRankByUUID($mysqli, str_replace(",", "", $followers[$i]))).'">'.user::getPrefix($mysqli, user::getRankByUUID($mysqli, str_replace(",", "", $followers[$i]))).'</span> <span>'.user::getNameByUUID($mysqli, str_replace(",", "", $followers[$i])).'</span>
                <hr />
            </div>
        ';
            }
        } else {
            echo '<p>Geen likes.</p>';
        }
    }
}
class common {
    static function random($length) {
        $key = '';
        $keys = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $key;
    }
    static function uploadimage($img) {
        $filename = $img['tmp_name'];
        $client_id="59dff13b54c75eb";
        $handle = fopen($filename, "r");
        $data = fread($handle, filesize($filename));
        $pvars   = array('image' => base64_encode($data));
        $timeout = 30;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $pvars);
        $out = curl_exec($curl);
        curl_close ($curl);
        $pms = json_decode($out,true);
        $url=$pms['data']['link'];
        if($url!=""){
            return str_replace("http","https",$url);;
        }else{
            return $pms['data']['error'];
        }
    }
}
class nav {
    static function parks($mysqli, $userid) {
        $sql = "SELECT * FROM pco_parks WHERE owner LIKE '%{$userid}%'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {
            if(!park::isDeleted($mysqli, $row['ID'])) {
                echo '
                    <a href="./park.php?id=' . $row['ID'] . '" class="dropdown-header"><li class="dropdown-header">' . $row['name'] . '</li></a>
                    <li><a href="./parksettings.php?id=' . $row['ID'] . '">Beheren</a></li>
                    <li><a href="./writearticle.php?id=' . $row['ID'] . '">Schrijf een artikel</a></li>
                    <li class="divider"></li>
                ';
            }
        }

        $sql = "SELECT * FROM pco_parks_staff WHERE uuid='$userid'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {
            echo '
                    <a href="./park.php?id=' . $row['park_id'] . '" class="dropdown-header"><li class="dropdown-header">' . park::getName($mysqli, $row['park_id']) . '</li></a>
                    <li><a href="./parksettings.php?id=' . $row['park_id'] . '">Beheren</a></li>';
            if ($row['can_write'] == 1) {
                echo '<li><a href="./writearticle.php?id=' . $row['park_id'] . '">Schrijf een artikel</a></li>';
            } else {
                echo '<li><a href="" style="cursor:not-allowed">Schrijf een artikel</a></li>';
            }
            echo '<li class="divider"></li>';
        }
    }
}
class search {
    static function loadArticles($mysqli, $keywords) {
        $sql = "SELECT * FROM pco_posts WHERE post_text LIKE '%{$keywords}%' order by ID desc";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        while($row = mysqli_fetch_assoc($result)) {
            $title = $row['post_text'];
            $postid = $row['ID'];
            $username = user::getNameByUUID($mysqli, $row['user_id']);
            $post = common::random(20);

            $icon = '';
            if (posts::isLiking($mysqli, $postid, $_SESSION['UUID'])) {
                $icon = 'favorite';
            } else {
                $icon = 'favorite_border';
            }
            $like = '';
            if (posts::isLiking($mysqli, $postid, $_SESSION['UUID'])) {
                $like = 'unlike';
            } else {
                $like = 'like';
            }
            echo '
                   <div class="jumbotron">
                       <div>
                            <p><span>' . $username . '</span></p>
                        </div>
                        <div class="hover" id="' . $post . '">
                            <div>
                                <img src="'.$row["post_image"].'" alt="header" class="img-responsive center-block" style="max-height: 300px;"/>
                            </div>
                            <h3>' . $title . '</h3>
                        </div>
                        <script>
                            var id' . $post . ' = document.getElementById("' . $post . '");

                            id' . $post . '.onclick = function() {
                                window.location.href = "post.php?id=' . $postid . '";
                            };
                        </script>
                            <span class="shortcut"><i class="material-icons heart"><a href="post.php?id=' . $postid . '&' . $like . '" style="text-decoration: none;">' . $icon . '</a></i><span><a href="article.php?id=' . $postid . '&likes" style="color: #000000; text-decoration: none;">' . posts::countLikes($mysqli, $postid) . '</a>
                            <span class="shortcut"><i class="material-icons">mode_comment</i><span>'.posts::getReactionCount($mysqli, $postid).'</span></span>
                            <i style="float: right;">Geplaatst op: ' . $row["posted_on"] . '</i>
                    </div>


                    ';
        }
    }
}
class system {
    static function isMaintenanceModeOn($mysqli) {
        $sql = "SELECT * FROM pco_settings WHERE variable='MAINTENANCE_MODE'";
        $result = mysqli_query($mysqli, $sql);
        $count=mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if(strcmp($row['data'], '0') == 0) {
            return false;
        }
        if(strcmp($row['data'], '1') == 0) {
            if(user::getRank($mysqli) > 0) {
                return false;
            }
            return true;
        }
        return false;
    }
    static function copyRightSign() {
        echo '<!--Copyright (c) 2017 Daníque de Jong-->';
    }
    static function addPageVisit($mysqli) {
        if(user::getRank($mysqli) > 0) {

        } else {
            setlocale(LC_TIME, 'NL_nl');
            $datenow = system::getDateNow();
            $sql = "SELECT * FROM pco_pageview WHERE date='$datenow'";
            $result = mysqli_query($mysqli, $sql);
            $count = mysqli_num_rows($result);
            $row = mysqli_fetch_assoc($result);
            if ($count > 0) {
                $oldcount = $row['count'];
                $newcount = $oldcount + 1;
                $sql1 = "UPDATE pco_pageview SET count='$newcount' WHERE date='$datenow'";
                $result1 = mysqli_query($mysqli, $sql1);
            } else {
                $oldcount = 0;
                $newcount = $oldcount + 1;
                $sql1 = "INSERT INTO pco_pageview (date, count) VALUES ('$datenow', '$newcount')";
                $result1 = mysqli_query($mysqli, $sql1);
            }
        }
    }
    static function getDateNow() {
        setlocale(LC_TIME, 'NL_nl');
        $time = strftime('%Y-%m-%e',time());
        return $time;
    }
}
class ads {
    static function skycraper() {
        echo '<span class="text-muted">Advertentie</span><br /><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                    <!-- ParkCraft Skyscraper -->
                    <ins class="adsbygoogle"
                         style="display:inline-block;width:260px;height:600px"
                         data-ad-client="ca-pub-3044188577438541"
                         data-ad-slot="1213730515"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>';
    }
    static function vierkant() {
        echo '<span class="text-muted">Advertentie</span><br /><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- ParkCraft vierknt -->
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-3044188577438541"
             data-ad-slot="2271661314"
             data-ad-format="auto"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
        </script>';
    }
}
class staff {
    static function canUseStaffPanel($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_use_staffpanel='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManageParkRequests($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_parkrequests='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManageUsers($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_users='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManageParks($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_parks='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManageComments($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_comments='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canSendMail($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_send_mail='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManagePosts($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_posts='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManageApplications($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_applications='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
    static function canManageJobs($mysqli, $useruuid) {
        $sql = "SELECT * FROM pco_staff WHERE UUID='$useruuid' AND can_manage_jobs='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        if($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}
class statistics {
    static function posts($mysqli) {
        $sql = "SELECT * FROM pco_posts";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        return $count;
    }
    static function reactions($mysqli) {
        $sql = "SELECT * FROM pco_reaction";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        return $count;
    }
    static function users($mysqli) {
        $sql = "SELECT * FROM pco_users";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        return $count;
    }
    static function usersActivated($mysqli) {
        $sql = "SELECT * FROM pco_users WHERE activated='1'";
        $result = mysqli_query($mysqli, $sql);
        $count = mysqli_num_rows($result);
        return $count;
    }
    static function totalPageVisits($mysqli) {
        $datenow = strftime('%Y-%m-%e', time());
        $sql = "SELECT * FROM pco_pageview WHERE date='$datenow'";
        $result = mysqli_query($mysqli, $sql);
        $count=mysqli_num_rows($result);
        $row = mysqli_fetch_assoc($result);
        if($count > 0) {
            $idnow = $row['ID'];
            $sql1 = "SELECT * FROM pco_pageview WHERE ID > '".($idnow - 6)."'";
            $result1 = mysqli_query($mysqli, $sql1);
            $count1=mysqli_num_rows($result1);
            while($row1 = mysqli_fetch_assoc($result1)) {
                $datesql = explode("-", $row1['date']);
                $year = $datesql['0'];
                $month = $datesql['1'];
                $day = $datesql['2'];
                echo '{ x: new Date('.$year.','.($month-1).','.$day.'), y: '.$row1["count"].' },
                ';
            }
        }
        return 0;
    }
}
?>
