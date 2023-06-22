<?php
session_start();
###########################################################################
#                      BartBandit htaccess manager                        #
###########################################################################
#                                                                         #
#                    https://github.com/BartBandit/                       #
#               ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~			          #  
#                   Enjoy the script, its free				              #
###########################################################################
# config
#########
$password_2_enter = "LetMeIn";          // the pasword to enter this script
$pwfile = ".htpasswd";                  // the password file
$CR_LF = "\r\n";                        // use "\r\n" ore "\n" for newline
$mydomain = "github.com/BartBandit/  "; 		// realm - found at: $mydomain
###################################
# functions
############

function error_message($msg) {
    echo "<script>alert (\"Error: $msg\") ; history.go(-1)</script>";
    echo"</body></html>";
    exit;
}

function html_footer() {
    echo "<p><a href=\"https://github.com/BartBandit/\">https://github.com/BartBandit/</a></p>";
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
        <title>BartBandit htaccess manager v2.0</title>
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
        <h2>BartBandit htaccess manager v2.0</h2>
        <?php
###################################
# check password
#################
        if ($password_2_enter != $password) {
            ?>
            <p><b>Provide password</b></p>
            <form action="htaccess.php" method="post">
                Password: <input type="text" name="password"> <input type="submit" value="Log in">
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
        <a href="htaccess.php?doe=create">Create secure folder</a> | 
        <a href="htaccess.php?doe=destroy">Undo protection</a> | 
        <a href="htaccess.php?doe=new"> New member</a> | 
        <a href="htaccess.php?doe=del">Remove member</a> | 
        <a href="htaccess.php?doe=show">Show members</a><br>
        <br>

        <?php
###################################
# New member
############
        if ($doe == "new") {
            ?>
            <p><b>Add member</b></p>
            <form action="htaccess.php" method="post">
                Enter the username:<br>
                <input type="text" name="username">
                <br>
                <br>
                Enter the password twice:<br>
                <input type="password" name="pwd1">
                <br>
                <input type="password" name="pwd2">
                <input type="hidden" name="saveuser" value="yes">
                <br>
                <br>
                <input type="submit" name="submit" value="Add">
            </form>
            <?php
            html_footer();
            exit;
        }
        if ($_saveuser == "yes") {
            if ($_username == "" || $_pwd1 == "" || $_pwd2 == "") {
                error_message("Did you forget anything???!!!");
            } else {
                if (!file_exists($pwfile)) { // first time here, lets create the pwfile
                    if (!$fp = @fopen($pwfile, "w+"))
                        die("Cannot create the file!");
                    fclose($fp);
                }
                if (!($fp = fopen($pwfile, "r")))
                    die("Can not open " . $pwfile);
                $file_contents = @fread($fp, filesize($pwfile));
                fclose($fp);
                $lines = explode($CR_LF, $file_contents);
                foreach ($lines as $line) {
                    @list($member, $pass) = explode(':', $line);
                    if ($member == $_username)
                        error_message("This name already exists, choose another one.");
                    if ($_pwd1 == $_pwd2) {
                        $passwd = $_pwd2;
                        $data = "$_username:$passwd$CR_LF";
                    } else
                        error_message("Passwords do not match");
                }
            }
            $fp = fopen($pwfile, "a+");
            if (!fwrite($fp, $data))
                error_message("Unable to write to $pwfile. Is this folder chmod 777 ??!!");
            fclose($fp);
            echo '<p><b>New member</b></p>';
            echo "User $_username is created with password $_pwd2";
            html_footer();
            exit;
        }
###################################
# Delete member
################
        if ($doe == "del") {
            ?>
            <p><b>Remove member</b></p>
            <form method="post" action="htaccess.php">
                Name:<input type="text" name="username" maxlength="15">
                <br>
                <input type="hidden" name="deluser" value="yes">
                <br>
                <input type="submit" value="Remove">
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
                error_message($_username . " is not in the list.");
            $fp = fopen($pwfile, "w");
            fwrite($fp, $new_pwfile);
            fclose($fp);
            ?>
            <p><b>Remove member</b></p>
            <?= $_username ?> is removed.<br>
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
                error_message("There are no members");
            ?>
            <p><b>Show members</b></p>
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
                error_message("You must first create a member, otherwise you will not be able to log in!");
            if (file_exists("./.htaccess"))
                error_message("This folder is already protected");
            ?>
            <p><b>Create secure folder</b></p>
            <form action="htaccess.php" method="post">
                Name of the realm(max 15 characters)<br>
                <input type="text" name="realmname" maxlenght="15">
                <br>
                <input type="hidden" name="createrealm" value="yes">
                <br>
                <input type="submit" value="Protect this folder">
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
                error_message(".htaccess could not be made! Please chmod this directory to 777!");
            fclose($fp);
            echo "This folder has been successfully secured,<br>\n";
            echo "don't forget your login and password, you will need it soon.<br>\n";
            echo "Below is the content of .htacces:<br></br>\n";
            echo "<pre>$htaccesscontent</pre><br>\n";
            html_footer();
            exit;
        }
###################################
# Delete .htaccess and .passwd
###############################
        if ($doe == "destroy") {
            ?>
            <p><b>Undo protection</b></p>
            Are you sure that this folder should be accessible to everyone again?<br>
            <form action="htaccess.php" method="post">
                <input type="hidden" name="destroyrealm" value="yes">
                <input type="submit" value="Undo protection">
            </form>
            <?php
            html_footer();
            exit;
        }
        if ($_destroyrealm == "yes") {
            echo "<p><b>Undo protection</b></p>\n";
            if (file_exists("./" . $pwfile)) {
                if (!unlink("./" . $pwfile)) {
                    echo "$pwfile could not be deleted! Please remove manually with FTP.<br>\n";
                } else {
                    echo "$pwfile is deleted.<br>\n";
                }
            } else {
                echo "$pwfile does not exist!!??<br>\n";
            }
            if (file_exists("./.htaccess")) {
                if (!unlink("./.htaccess")) {
                    echo ".htaccess could not be deleted! Please remove manually with FTP.<br>\n";
                } else {
                    echo ".htaccess is deleted.<br>\n";
                }
            } else {
                echo ".htaccess does not exist!!??<br>\n";
            }
            html_footer();
            exit;
        }
        ?>
        <p><b>Welcome</b></p>
        htaccess manager is for securing directories on an apache web server. 
		With this script you can create a realm (private zone), add,view and delete members.<br>
        <br>
        Before you create a secure folder, you must first register at least one member, 
		otherwise nobody will be able to access the folder!<br>
        <br>
        <br>
        <br>
        <br>
        <?php
        html_footer();
        ?>
