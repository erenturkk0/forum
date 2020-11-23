<?php
ob_start();
session_start();
include("includes/functions.php");

$step = $_GET['step'];

if ($step == 1) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Step 2 • Install Osiris Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Install Osiris Semantic Forum">
    <meta name="author" content="Vanguard">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="./css/icon.min.css">
    <link rel="stylesheet" href="./css/semantic.min.css">
    <link rel="stylesheet" href="./css/animate.min.css">
    <!-- End Stylesheets -->

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="./images/logo.png">
    <!-- End Favicons -->
    
    <!-- Styles perso -->
    <style type="text/css">
        body {
            background-color: #edeff0;
        }
        
        .ui.menu .item img.logo {
            margin-right: 1.3em;
        }
        
        .main.container {
            margin-top: 7em;
        }
        
        .wireframe {
            margin-top: 2em;
        }
        
        .ui.footer.segment {
            margin: 5em 0em 0em;
            padding: 5em 0em;
        }

    </style>
    <!-- End Styles perso -->
</head>
<body>
	<!-- HEADER START -->
	<div class="ui fixed menu">
        <div class="ui container">
            <div class="header item">
                <h2>Osiris</h2>
            </div>
            <div class="right item">
                <a href="http://demo.squamifer.ovh/Osiris/" class="ui positive button" target="_blank">Support</a>
            </div>
        </div>
    </div>
	<!-- HEADER END -->
	<!-- BODY START -->
    <div class="ui main container animated fadeIn">
        <div class="ui three top attached ordered steps">
            <div class="completed step">
                <div class="content">
                    <div class="title">Introduction</div>
                    <div class="description">Informations before installation</div>
                </div>
            </div>
            <div class="active step">
                <div class="content">
                    <div class="title">Installation</div>
                    <div class="description">Configure your MySQL and Personal settings</div>
                </div>
            </div>
            <div class="disabled step">
                <div class="content">
                    <div class="title">Success</div>
                    <div class="description">Checking parameters</div>
                </div>
            </div>
        </div>
        <div class="ui attached padded segment">
        <?php
            if (isset($_POST['do_install'])) {
            $mysql_host = $_POST['mysql_host'];
            $mysql_user = $_POST['mysql_user'];
            $mysql_pass = $_POST['mysql_pass'];
            $mysql_db = $_POST['mysql_db'];
            $pseudo = $_POST['pseudo'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            $temps = time();

                if (empty($mysql_host) or empty($mysql_user) or empty($mysql_pass) or empty($mysql_db) or empty($pseudo) or empty($password) or empty($email)) { echo error("Error! All fields are required."); }
                else {

                    $db = new PDO('mysql:host='. $mysql_host .';dbname='. $mysql_db, $mysql_user, $mysql_pass, [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);

                    if ($db) {

                        $sql_filename = 'sql.sql';
                        $sql_contents = file_get_contents($sql_filename);
                        $sql_contents = explode(";", $sql_contents);

                        foreach($sql_contents as $k=>$v) {
                            $db->exec($v);
                        }

                        $password = md5($password);
                        $insert = $db->prepare("INSERT forum_membres (membre_pseudo,membre_mdp,membre_email,membre_btag,membre_siteweb,membre_avatar,membre_signature,membre_localisation,membre_inscrit,membre_derniere_visite,membre_rang,membre_post) VALUES (?,?,?,'','','avataradmin.jpg','','',?,?,'4','0')");
                        $insert->execute(array($pseudo,$password,$email,$temps,$temps));
                        mkdir($main_folder_name);

                        $current .= '<?php
                        ';
                        $current .= 'try
                        ';
                        $current .= '{
                        ';
                        $current .= '$sql["driver"] = "mysql";
                        ';
                        $current .= '$sql["host"] = "'.$mysql_host.'";
                        ';
                        $current .= '$sql["user"] = "'.$mysql_user.'";
                        ';
                        $current .= '$sql["pass"] = "'.$mysql_pass.'";
                        ';
                        $current .= '$sql["base"] = "'.$mysql_db.'";
                        ';
                        $current .= '$db = new PDO($sql["driver"] .":host=". $sql["host"] .";dbname=". $sql["base"], $sql["user"], $sql["pass"]);
                        ';
                        $current .= '}
                        ';
                        $current .= 'catch (Exception $e)
                        ';
                        $current .= '{
                        ';
                        $current .= 'die("Erreur : " . $e->getMessage());
                        ';
                        $current .= '}
                        ';
                        $current .= '?>
                        ';

                        file_put_contents("includes/identifiants.php", $current);

                        $_SESSION['install_pseudo'] = $pseudo;
                        $_SESSION['install_password'] = $_POST['password'];

                        header("Location: ./install.php?step=2");

                    } else {
                        echo error('<div class="ui icon error message"><i class="checkmark icon"></i><div class="content"><div class="header">Error!</div><p>Failed to connect to MySQL server.</p></div></div>');
                    }
                }
            }
            ?>
            <form action="" method="post" accept-charset="utf-8" class="ui form">
                <div class="ui horizontal icon divider"><i class="circular teal database icon"></i></div>
                <div class="field required">
                    <label for="1">MySQL Hostname</label>
                    <input name="mysql_host" id="1" value="<?php echo $_POST['mysql_host']; ?>" placeholder="e.g: localhost" type="text" autocomplete="off" required>
                </div>
                <div class="field required">
                    <label for="2">MySQL Database</label>
                    <input name="mysql_db" id="2" value="<?php echo $_POST['mysql_db']; ?>" placeholder="e.g: database" type="text" autocomplete="off" required>
                </div>
                <div class="field required">
                    <label for="3">MySQL Username</label>
                    <input name="mysql_user" id="3" value="<?php echo $_POST['mysql_user']; ?>" placeholder="e.g: root" type="text" autocomplete="off" required>
                </div>
                <div class="field required">
                    <label for="4">MySQL Password</label>
                    <input name="mysql_pass" id="4" value="<?php echo $_POST['mysql_pass']; ?>" placeholder="e.g: rootpassword" type="text" autocomplete="off" required>
                </div>
                <div class="ui horizontal icon divider"><i class="circular red user icon"></i></div>
                <div class="field required">
                    <label for="15">E-mail Address</label>
                    <input name="email" id="15" value="<?php echo $_POST['email']; ?>" placeholder="e.g: example@address.com" type="email" autocomplete="off" required>
                </div>
                <div class="field required">
                    <label for="16">Admin Username</label>
                    <input name="pseudo" id="16" value="<?php echo $_POST['pseudo']; ?>" placeholder="e.g: Johndoe" type="text" autocomplete="off" required>
                </div>
                <div class="field required">
                    <label for="17">Admin Password</label>
                    <input name="password" id="17" value="<?php echo $_POST['password']; ?>" placeholder="e.g: YourPassword321" type="password" autocomplete="off" required>
                </div>
                <div class="ui divider"></div>
                <button type="submit" name="do_install" class="ui positive fluid button">Install Osiris</button>
            </form>
        </div>
        <div class="ui divider hidden"></div>
    </div>
	<!-- BODY END -->
	<!-- FOOTER CODE -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>  
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script type="text/javascript" src="./js/semantic.min.js"></script>
    <script type="text/javascript" src="./js/dropdown.min.js"></script>
	<!-- FOOTER CODE -->
</body>
</html>

<?php
} elseif ($step == 2) {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Step 3 • Install Osiris Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Install Osiris Semantic Forum">
    <meta name="author" content="Vanguard">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="./css/icon.min.css">
    <link rel="stylesheet" href="./css/semantic.min.css">
    <link rel="stylesheet" href="./css/animate.min.css">
    <!-- End Stylesheets -->

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="./images/logo.png">
    <!-- End Favicons -->
    
    <!-- Styles perso -->
    <style type="text/css">
        body {
            background-color: #edeff0;
        }
        
        .ui.menu .item img.logo {
            margin-right: 1.3em;
        }
        
        .main.container {
            margin-top: 7em;
        }
        
        .wireframe {
            margin-top: 2em;
        }
        
        .ui.footer.segment {
            margin: 5em 0em 0em;
            padding: 5em 0em;
        }

    </style>
    <!-- End Styles perso -->
</head>
<body>
	<!-- HEADER START -->
	<div class="ui fixed menu">
        <div class="ui container">
            <div class="header item">
                <h2>Osiris</h2>
            </div>
            <div class="right item">
                <a href="http://demo.squamifer.ovh/osiris/" class="ui positive button" target="_blank">Support</a>
            </div>
        </div>
    </div>
	<!-- HEADER END -->
	<!-- BODY START -->
	<div class="ui main container animated fadeIn">
        <div class="ui three top attached ordered steps">
            <div class="completed step">
                <div class="content">
                    <div class="title">Introduction</div>
                    <div class="description">Informations before installation</div>
                </div>
            </div>
            <div class="completed step">
                <div class="content">
                    <div class="title">Installation</div>
                    <div class="description">Configure your MySQL and Personal settings</div>
                </div>
            </div>
            <div class="completed active step">
                <div class="content">
                    <div class="title">Success</div>
                    <div class="description">Checking parameters</div>
                </div>
            </div>
        </div>
        <div class="ui attached padded segment">
            <h1 class="ui center aligned green header animated pulse">Vanguard Installation Processed!</h1>
            <center>
                <p>Parameters installation successfully completed.</p>
                <div class="ui divider"></div>
                <p><strong>Admin Username</strong><br> <span class="ui black label"><?php echo $_SESSION['install_pseudo']; ?></span></p>
                <p><strong>Admin Password</strong><br> <span class="ui black label"><?php echo $_SESSION['install_password']; ?></span></p>
                <div class="ui divider"></div>
                <a href="./index.php" class="large ui primary button">Visit your forum <i class="right arrow icon"></i></a></center>
        </div>
    </div>
	<!-- BODY END -->
	<!-- FOOTER CODE -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>  
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script type="text/javascript" src="./js/semantic.min.js"></script>
    <script type="text/javascript" src="./js/dropdown.min.js"></script>
	<!-- FOOTER CODE -->
</body>
</html>

<?php
@unlink("install.php");
@unlink("sql.sql");
unset($_SESSION['install_pseudo']);
unset($_SESSION['install_password']);
session_unset();
session_destroy();
} else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Install Osiris Forum</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Install Osiris Semantic Forum">
    <meta name="author" content="Vanguard">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="./css/icon.min.css">
    <link rel="stylesheet" href="./css/semantic.min.css">
    <link rel="stylesheet" href="./css/animate.min.css">
    <!-- End Stylesheets -->

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="./images/logo.png">
    <!-- End Favicons -->
    
    <!-- Styles perso -->
    <style type="text/css">
        body {
            background-color: #edeff0;
        }
        
        .ui.menu .item img.logo {
            margin-right: 1.3em;
        }
        
        .main.container {
            margin-top: 7em;
        }
        
        .wireframe {
            margin-top: 2em;
        }
        
        .ui.footer.segment {
            margin: 5em 0em 0em;
            padding: 5em 0em;
        }

    </style>
    <!-- End Styles perso -->
</head>
<body>
	<!-- HEADER START -->
	<div class="ui fixed menu">
        <div class="ui container">
            <div class="header item">
                <h2>Osiris</h2>
            </div>
            <div class="right item">
                <a href="http://demo.squamifer.ovh/osiris/" class="ui positive button" target="_blank">Support</a>
            </div>
        </div>
    </div>
	<!-- HEADER END -->
	<!-- BODY START -->
	<div class="ui main container animated fadeIn">
        <div class="ui three top attached ordered steps">
            <div class="active step">
                <div class="content">
                    <div class="title">Introduction</div>
                    <div class="description">Informations before installation</div>
                </div>
            </div>
            <div class="disabled step">
                <div class="content">
                    <div class="title">Installation</div>
                    <div class="description">Configure your MySQL and Personal settings</div>
                </div>
            </div>
            <div class="disabled step">
                <div class="content">
                    <div class="title">Success</div>
                    <div class="description">Checking parameters</div>
                </div>
            </div>
        </div>
        <div class="ui attached padded segment">
            <h1 class="ui center aligned header animated pulse">Osiris needs an installation!</h1>
            <center>
                <p>Describe here.</p>
                <p><i class="warning circular icon"></i> There are currently no forum configuration settings. Only registration of the Administrator account and MySQL IDs. (This will be added in an upcoming update)</p>
                <p><?php echo '<h3 class="header">Current PHP version</h3> <span class="ui blue label">' . phpversion() . '</span>'; ?></p>
                <p><h3 class="header">Recommended PHP Version</h3> <span class="ui orange label">PHP 5.x</span><span class="ui green label">PHP 7.x</span></p>
                <div class="ui divider"></div>
                <a href="./install.php?step=1" class="large ui black button">Start installation</a>
            </center>
        </div>
    </div>
	<!-- BODY END -->
	<!-- FOOTER CODE -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>  
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script type="text/javascript" src="./js/semantic.min.js"></script>
    <script type="text/javascript" src="./js/dropdown.min.js"></script>
	<!-- FOOTER CODE -->
</body>
</html>
<?php } ?>