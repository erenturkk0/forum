<?php
session_start();
$titre="Log in";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

if ($id!=0) erreur(ERR_IS_CO);

echo '<div class="ui main container animated fadeIn">';
echo '<div class="ui raised clearing segments">
<div class="ui center aligned segment">
    <h3 class="ui header">Login to your account</h3>
</div>
<div class="ui purple padded segment">
    <center>
        <div class="ui breadcrumb">
            <a href="" class="section">Accueil</a>
            <div class="divider"> / </div>
            <div class="active section">Log in</div>
        </div>
    </center>
    <div class="ui divider"></div>';

// Here we want to make sure that the visitor who arrives on this page is not already connected, that's why we check the value of the variable $ id (I remind that it contains 0 if the visitor does not already log in, Is not logged in, the id of the member otherwise)
if (!isset($_POST['pseudo'])) // We are in the form page
    {
        echo '<form method="post" class="ui form" action="connexion.php">
        <div class="field required">
            <label for="1">Username</label>
            <div class="ui left icon input">
                <i class="user outline icon"></i>
                <input type="text" name="pseudo" id="1" placeholder="JohnDoe" required>
            </div>
        </div>
        <div class="field required">
            <label for="2">Password</label>
            <div class="ui left icon input">
                <i class="lock icon"></i>
                <input type="password" name="password" id="2" placeholder="Your password" required>
            </div>
        </div>
        <button class="ui fluid positive button" type="submit"><i class="sign in icon"></i> Login</button>
        </form>';

        echo '</div>
        <div class="ui secondary segment">
            <p><i class="circular warning icon"></i> You are not registered yet? <a href="./register.php">Sign up</a>.</p>
        </div>';
        echo '</div></body></html>';
    }
else // The rest of the code
    {
        $message='';
        if (empty($_POST['pseudo']) || empty($_POST['password']) ) // Forgotten Field
            {
                $message = '<div class="ui icon error message"><i class="frown icon"></i><div class="content"><div class="header">An error occurred during your login!</div><p>You must correct the errors below and try again:<ul><li>You must fill <strong>all</strong> fields.</li></ul></p></div></div><div class="ui divider"></div><center><a href="./connexion.php" class="ui animated fade negative button" tabindex="0"><div class="visible content">Click here to start over</div><div class="hidden content"><i class="arrow left icon"></i></div></a></center>';
            }
        else // We check the password
            {
                $query=$db->prepare('SELECT membre_mdp, membre_id, membre_rang, membre_pseudo FROM forum_membres WHERE membre_pseudo = :pseudo');
                $query->bindValue(':pseudo',$_POST['pseudo'], PDO::PARAM_STR);
                $query->execute();
                $data=$query->fetch();
            if ($data['membre_mdp'] == md5($_POST['password'])) // Access OK!
                {
                if ($data['membre_rang'] == 0) // The member is banned
                    {
                        $message='<div class="ui icon error message"><i class="remove icon"></i><div class="content"><div class="header">An error occurred during your login!</div><p>You have been banned, you can not connect to this forum.</p></div></div>';
                    }
                else // Otherwise it's ok, we connect
                    {
                        $_SESSION['pseudo'] = $data['membre_pseudo'];
                        $_SESSION['level'] = $data['membre_rang'];
                        $_SESSION['id'] = $data['membre_id'];
                        $message = '<div class="ui icon positive message"><i class="smile icon"></i><div class="content"><div class="header">You have successfully logged in!</div><p>Welcome <strong>'.$data['membre_pseudo'].'</strong>, you are now logged in on the forum!</p></div></div><div class="ui divider"></div><center><div class="ui buttons"><a href="./index.php" class="ui button">Go to the Homepage</a><div class="or"></div><a href="./voirprofil.php?m='.$data['membre_id'].'" class="ui positive button">Go to my Profile</a></div></center>';
                    }
                }
            else // Access not OK!
                {
                    $message = '<div class="ui icon error message"><i class="frown icon"></i><div class="content"><div class="header">An error occurred during your login!</div><p>You must correct the errors below and try again:<ul><li>The <strong>Password</strong> or <strong>Username</strong> entered is incorrect.</li></ul></p></div></div><div class="ui divider"></div><center><a href="./connexion.php" class="ui animated fade negative button" tabindex="0"><div class="visible content">Click here to start over</div><div class="hidden content"><i class="arrow left icon"></i></div></a></center>';
                }
            $query->CloseCursor();
            }
        echo $message.'</div><div class="ui secondary segment"><p><i class="circular warning icon"></i> You are not registered yet? <a href="./register.php">Sign up</a>.</p></div></div></body></html>';
    }
