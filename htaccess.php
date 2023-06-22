<?php
session_start();
###########################################################################
#                          BWL htaccess manager                           #
###########################################################################
#                                                                         #
#                Copyright 2002 by BartsWeblabo                           #
#     Contact: bart@bartsweblabo.com || http://www.bartsweblabo.com       #
#               ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~			  #  
#                   Enjoy the script, its free				  #
###########################################################################
# config
#########
$password_2_enter = "LetMeIn";          // the pasword to enter this script
$pwfile = ".htpasswd";                  // the password file
$CR_LF = "\r\n";                        // use "\r\n" ore "\n" for newline
$mydomain = "iowa.ineosoxide.com"; 		// realm - found at: $mydomain
###################################
# functions
############

function error_message($msg) {
    echo "<script>alert (\"Error: $msg\") ; history.go(-1)</script>";
    echo"</body></html>";
    exit;
}

function html_footer() {
    echo "<p><a href=\"http://www.bartsweblabo.be\">2002 &copy; BartsWebLabo</a></p>";
    echo "</body>\n</html>";
}

###################################
# Begin
########
$password = '';
if (!empty($_POST) && isset($_POST['password'])) {
    $_SESSION['PASSWORD'] = test_input($_POST['password']);
    $password = test_input($_POST['password']);
} elseif (isset($_SESSION['PASSWORD'])) {
    $password = test_input($_SESSION['PASSWORD']);
}
// validate _GET
$valid_doe = ['create', 'destroy', 'new', 'del', 'show'];
$doe = !empty($_GET) && isset($_GET['doe']) && in_array($_GET['doe'], $valid_doe) ? test_input($_GET['doe']) : '';

//define _POST variables and set to empty values
$_username = $_pwd1 = $_pwd2 = $_saveuser = $_deluser = $_realmname = $_createrealm = $_destroyrealm = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    @$_username = test_input($_POST["username"]);
    @$_pwd1 = test_input($_POST["pwd1"]);
    @$_pwd2 = test_input($_POST["pwd2"]);
    @$_saveuser = test_input($_POST['saveuser']);
    @$_deluser = test_input($_POST['deluser']);
    @$_realmname = test_input($_POST['realmname']);
    @$_createrealm = test_input($_POST['createrealm']);
    @$_destroyrealm = test_input($_POST['destroyrealm']);
}
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<html>
    <head>
        <title>BWL htaccess manager v1.0</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <style type="text/css">
            <!--
            body {  background-color: #000000; font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #FFFFFF}
            h2 {  font-family: tahoma; font-size: 18px; font-weight: bold; color: #CCCCCC}
            p {  font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #999999; text-decoration: underline}
            -->
        </style>
    </head>

    <body bgcolor="#000000" text="#FFFFFF" leftmargin="100" link="#FFFFFF" vlink="#CCCCCC" alink="#999999">
        <h2>BWL htaccess manager v1.0</h2>
        <?php
###################################
# check password
#################
        if ($password_2_enter != $password) {
            ?>
            <p><b>Paswoord ingeven</b></p>
            <form action="htaccess.php" method="post">
                Paswoord: <input type="text" name="password"> <input type="submit" value="Inloggen">
            </form>
            <br>
            <br>
            <br>
            <?php
            html_footer();
            exit;
        }
###################################
# continue
###########
        ?>
        <a href="htaccess.php">Index</a> | 
        <a href="htaccess.php?doe=create">Beveiligde map maken</a> | 
        <a href="htaccess.php?doe=destroy">Beveiliging ongedaan maken</a> | 
        <a href="htaccess.php?doe=new"> Nieuw lid</a> | 
        <a href="htaccess.php?doe=del">Verwijder lid</a> | 
        <a href="htaccess.php?doe=show">Toon Leden</a><br>
        <br>

        <?php
###################################
# New member
############
        if ($doe == "new") {
            ?>
            <p><b>Nieuw lid aanmaken</b></p>
            <form action="htaccess.php" method="post">
                Geef de gebruikersnaam:<br>
                <input type="text" name="username">
                <br>
                <br>
                Geef het wachtwoord tweemaal:<br>
                <input type="password" name="pwd1">
                <br>
                <input type="password" name="pwd2">
                <input type="hidden" name="saveuser" value="yes">
                <br>
                <br>
                <input type="submit" name="submit" value="Toevoegen">
            </form>
            <?php
            html_footer();
            exit;
        }
        if ($_saveuser == "yes") {
            if ($_username == "" || $_pwd1 == "" || $_pwd2 == "") {
                error_message("Ben je niets vergeten???!!!");
            } else {
                if (!file_exists($pwfile)) { // first time here, lets create the pwfile
                    if (!$fp = @fopen($pwfile, "w+"))
                        die("Kan de file niet maken!");
                    fclose($fp);
                }
                if (!($fp = fopen($pwfile, "r")))
                    die("Kan $pwfile niet openen");
                $file_contents = @fread($fp, filesize($pwfile));
                fclose($fp);
                $lines = explode($CR_LF, $file_contents);
                foreach ($lines as $line) {
                    @list($member, $pass) = explode(':', $line);
                    if ($member == $_username)
                        error_message("Deze naam komt reeds voor, kies een andere.");
                    if ($_pwd1 == $_pwd2) {
                        $passwd = $_pwd2;
                        $data = "$_username:$passwd$CR_LF";
                    } else
                        error_message("Wachtwoorden komen niet overeen");
                }
            }
            $fp = fopen($pwfile, "a+");
            if (!fwrite($fp, $data))
                error_message("Er kon niet geschreven worden naar $pwfile. Is deze map 777 gechmod??!!");
            fclose($fp);
            echo '<p><b>Nieuw lid</b></p>';
            echo "User $_username is aangemaakt met wachtwoord $_pwd2";
            html_footer();
            exit;
        }
###################################
# Delete member
################
        if ($doe == "del") {
            ?>
            <p><b>Verwijder lid</b></p>
            <form method="post" action="htaccess.php">
                naam:<input type="text" name="username" maxlength="15">
                <br>
                <input type="hidden" name="deluser" value="yes">
                <br>
                <input type="submit" value="Verwijderen">
            </form>
            <?php
            html_footer();
            exit;
        }
        if ($_deluser == "yes") {
            $count = 0;
            if (!($fp = @fopen($pwfile, "r")))
                die("Cannot open $pwfile");
            $file_contents = fread($fp, filesize($pwfile));
            fclose($fp);
            $lines = explode($CR_LF, $file_contents);
            $new_pwfile = '';
            foreach ($lines as $line) {
                @list($member, $pass) = explode(":", $line);
                if ($member == $_username) {
                    $count++;
                } else if ($member)
                    $new_pwfile .= "$member:$pass$CR_LF";
            }
            if ($count == 0)
                error_message($_username . " staat niet in de lijst");
            $fp = fopen($pwfile, "w");
            fwrite($fp, $new_pwfile);
            fclose($fp);
            ?>
            <p><b>Lid verwijderen</b></p>
            <?php echo $_username ?> is verwijderd.<br>
            <?php
            html_footer();
            exit;
        }
###################################
# show member(s)
#################
        if ($doe == "show") {
            $count = 0;
            if (!($fp = @fopen($pwfile, "r")))
                die("Cannot open $pwfile");
            $file_contents = fread($fp, filesize($pwfile));
            fclose($fp);
            $lines = explode($CR_LF, $file_contents);
            $show_pwfile = '';
            foreach ($lines as $line) {
                @list($member, $pass) = explode(':', $line);
                $count++;
                if ($member) {
                    $show_pwfile .= "$count : $member<br>\n";
                }
            }if ($count == 1)
                error_message("Er zijn geen leden");
            ?>
            <p><b>Toon leden</b></p>
            <?php
            echo $show_pwfile;
            html_footer();
            exit;
        }
###################################
# create .htaccess file
########################
        if ($doe == "create") {
            if (!file_exists($pwfile))
                error_message("Je moet eerst een lid aanmaken, anders kan je straks niet inloggen!");
            if (file_exists("./.htaccess"))
                error_message("Deze map is reeds beveiligd");
            ?>
            <p><b>Beveiligde map aanmaken</b></p>
            <form action="htaccess.php" method="post">
                Naam van de realm(max 15 karakters)<br>
                <input type="text" name="realmname" maxlenght="15">
                <br>
                <input type="hidden" name="createrealm" value="yes">
                <br>
                <input type="submit" value="Beveilig deze map">
            </form>

            <?php
            html_footer();
            exit;
        }
        if ($_createrealm == "yes") {
            $path = $_SERVER['SCRIPT_FILENAME'];
            $path = preg_replace('/htaccess.php/', '', $path);
            $htaccesscontent = "AuthType Basic\nAuthName \"" . $_realmname . " - found at: " . $mydomain . "\"\nAuthUserFile " . $path . $pwfile . "\nrequire valid-user";
            $fp = fopen("./.htaccess", "w+");
            if (!fwrite($fp, $htaccesscontent))
                error_message(".htaccess kon niet gemaakt worden! Gelieve deze map naar 777 te chmoden!");
            fclose($fp);
            echo "Deze map is met succes beveiligd,<br>\n";
            echo "vergeet je login en wachtwoord niet, je zult het dadelijk nodig hebben<br>\n";
            echo "hieronder staat de inhoud van .htacces:<br></br>\n";
            echo "<pre>$htaccesscontent</pre><br>\n";
            html_footer();
            exit;
        }
###################################
# Delete .htaccess and .passwd
###############################
        if ($doe == "destroy") {
            ?>
            <p><b>Beveiliging ongedaan maken</b></p>
            Weet je zeker dat deze map terug toegangkelijk mag zijn voor iedereen?<br>
            <form action="htaccess.php" method="post">
                <input type="hidden" name="destroyrealm" value="yes">
                <input type="submit" value="Beveiliging ongedaan maken">
            </form>
            <?php
            html_footer();
            exit;
        }
        if ($_destroyrealm == "yes") {
            echo "<p><b>Beveiliging ongedaan maken</b></p>\n";
            if (file_exists("./" . $pwfile)) {
                if (!unlink("./" . $pwfile)) {
                    echo "$pwfile kon niet verwijderd worden! Gelieve manueel met FTP te verwijderen.<br>\n";
                } else {
                    echo "$pwfile is verwijderd.<br>\n";
                }
            } else {
                echo "$pwfile bestaat niet!!??<br>\n";
            }
            if (file_exists("./.htaccess")) {
                if (!unlink("./.htaccess")) {
                    echo ".htaccess kon niet verwijderd worden! Gelieve manueel met FTP te verwijderen.<br>\n";
                } else {
                    echo ".htaccess is verwijderd.<br>\n";
                }
            } else {
                echo ".htaccess bestaat niet!!??<br>\n";
            }
            html_footer();
            exit;
        }
        ?>
        <p><b>Welkom</b></p>
        htaccess manager is voor het beveiligen van mappen op een apache webserver.<br>
        Met dit script kan je een realm (private zone) aanmaken, leden toevoegen, leden 
        verwijderen en leden nakijken.<br>
        <br>
        Voordat je een beveiligde map aanmaakt moet je eerst minstens &eacute;&eacute;n 
        lid inschrijven, anders kan je zelf niet meer in de map.<br>
        <br>
        <br>
        <br>
        <br>
        <?php
        html_footer();
        ?>
