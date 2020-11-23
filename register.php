<?php
session_start();
$titre="Registration";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

if ($id!=0) erreur(ERR_IS_CO);

if (empty($_POST['pseudo'])) // If the variable is empty, we can consider that we are on the form page
{
    echo '<div class="ui main container animated fadeIn">';
	echo '<div class="ui two top attached ordered steps">
    <div class="active step">
        <div class="content">
            <div class="title">Registration</div>
            <div class="description">Create your account on our forum</div>
        </div>
    </div>
    <div class="disabled step">
        <div class="content">
            <div class="title">Registration Successful</div>
            <div class="description">Successful registration, login to your account</div>
        </div>
    </div>
    </div>';
    echo '<div class="ui attached padded segment">
    <div class="ui icon info message"><i class="info circle icon"></i><div class="content"><div class="header">Important!</div><p>Fields preceded by a <strong style="color: red;">*</strong> are mandatory.</p></div></div>
    <form action="register.php" method="post" accept-charset="utf-8" class="ui form" enctype="multipart/form-data">
        <div class="ui horizontal icon divider"><i class="circular teal privacy icon"></i></div>
        <div class="field required">
            <label for="1">Username</label>
            <div class="ui left icon input">
                <i class="user outline icon"></i>
                <input name="pseudo" id="1" placeholder="Johndoe" type="text" autocomplete="off" required>
            </div>
            <div class="ui pointing label">The username must contain between <strong style="color:red;">'.$config['pseudo_minsize'].'</strong> and <strong style="color:red;">'.$config['pseudo_maxsize'].'</strong> characters.</div>
        </div>
        <div class="field required">
            <label for="2">Password</label>
            <div class="ui left icon input">
                <i class="lock icon"></i>
                <input name="password" id="2" placeholder="Your password..." type="password" autocomplete="off" required>
            </div>
        </div>
        <div class="field required">
            <label for="3">Confirm password</label>
            <div class="ui left icon input">
                <i class="lock icon"></i>
                <input name="confirm" id="3" placeholder="Your password..." type="password" autocomplete="off" required>
            </div>
        </div>
        <div class="ui horizontal icon divider"><i class="circular blue browser icon"></i></div>
        <div class="field required">
            <label for="4">E-mail</label>
            <div class="ui left icon input">
                <i class="mail outline icon"></i>
                <input name="email" id="4" placeholder="john.doe@domain.com" type="email" autocomplete="off" required>
            </div>
        </div>
        <div class="field">
            <label for="5">BattleTag</label>
            <input name="btag" id="5" placeholder="JohnDoe#6666" type="text" autocomplete="off">
        </div>
        <div class="field">
            <label for="6">Website</label>
            <div class="ui left icon input">
                <i class="globe icon"></i>
                <input name="website" id="6" placeholder="http://domain.com/" type="url" autocomplete="off">
            </div>
        </div>
        <div class="ui horizontal divider">AND</div>
        <div class="field">
            <label for="7">Localization</label>
            <div class="ui left icon input">
                <i class="flag outline icon"></i>
                <input name="localisation" id="7" placeholder="Where do you live? On Mars or on the Moon?" type="text" autocomplete="off">
            </div>
        </div>
        <div class="ui horizontal icon divider"><i class="circular red theme icon"></i></div>
        <div class="field">
            <label for="8">Choose your avatar</label>
            <div class="ui left icon input">
                <i class="id badge icon"></i>
                <input name="avatar" id="8" class="ui button" type="file" />
            </div>
            <div class="ui pointing label">Max size is <strong style="color:red;">'.$config['avatar_maxsize'].'</strong> Octets.</div>
        </div>
        <div class="field">
            <label for="9">Signature</label>
            <textarea name="signature" id="9" placeholder="Your signature that will appear on the forums..." rows="6" autocomplete="off"></textarea>
            <div class="ui pointing label">The signature is limited to <strong style="color:red;">'.$config['sign_maxl'].'</strong> characters.</div>
        </div>
        <div class="ui divider"></div>
        <input type="submit" value="Register on the forum" class="ui positive fluid button">
    </form>
    </div>';
    echo '<div class="ui divider hidden"></div></div>';
    echo '</body>
	</html>';
	
	
} // End of form part

else // One is in the treatment case
{
    $pseudo_erreur1 = NULL;
    $pseudo_erreur2 = NULL;
    $mdp_erreur = NULL;
    $email_erreur1 = NULL;
    $email_erreur2 = NULL;
    $signature_erreur = NULL;
    $avatar_erreur = NULL;
    $avatar_erreur1 = NULL;
    $avatar_erreur2 = NULL;
    $avatar_erreur3 = NULL;


    // We retrieve the variables
    $i = 0;
    $temps = time(); 
    $pseudo=$_POST['pseudo'];
    $signature = $_POST['signature'];
    $email = $_POST['email'];
    $btag = $_POST['btag'];
    $website = $_POST['website'];
    $localisation = $_POST['localisation'];
    $pass = md5($_POST['password']);
    $confirm = md5($_POST['confirm']);
	
    // Checking the username
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
    $query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
    $query->execute();
    $pseudo_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    if(!$pseudo_free)
    {
        $pseudo_erreur1 = "<li>Your <strong>Username</strong> is already used by a member.</li>";
        $i++;
    }

    if (strlen($pseudo) < ($config['pseudo_minsize']) || strlen($pseudo) > ($config['pseudo_maxsize']))
    {
        $pseudo_erreur2 = "<li>Your <strong>Username</strong> is either too big or too small.</li>";
        $i++;
    }

    // Checking the password
    if ($pass != $confirm || empty($confirm) || empty($pass))
    {
        $mdp_erreur = "<li>Your <strong>Password</strong> and your <strong>Confirm password</strong> will differ, or are empty.</li>";
        $i++;
    }


    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
    $query->bindValue(':pseudo',$pseudo, PDO::PARAM_STR);
    $query->execute();
    $pseudo_free=($query->fetchColumn()==0)?1:0;


    // Checking email address
    // The email address has never been used
    $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_email =:mail');
    $query->bindValue(':mail',$email, PDO::PARAM_STR);
    $query->execute();
    $mail_free=($query->fetchColumn()==0)?1:0;
    $query->CloseCursor();
    
    if(!$mail_free)
    {
        $email_erreur1 = "<li>Your <strong>E-mail</strong> is already used by a member.</li>";
        $i++;
    }
    // We check the shape now
    if (!preg_match("#^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
    {
        $email_erreur2 = "<li>Your <strong>E-mail</strong> does not have a valid format.</li>";
        $i++;
    }
    // Verification of signature
    if (strlen($signature) > ($config['sign_maxl']))
    {
        $signature_erreur = "<li>Your <strong>Signature</strong> is too long.</li>";
        $i++;
    }

    
    // Verification of the avatar:
    if (!empty($_FILES['avatar']['size']))
    {
        // We define the variables:
        $maxsize = $config['avatar_maxsize']; // Weight of the image
        $maxwidth = $config['avatar_maxl']; // Width of the image
        $maxheight = $config['avatar_maxh']; // Height of the image
        $extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png', 'bmp' ); // List of valid extensions
        
        if ($_FILES['avatar']['error'] > 0)
        {
            $avatar_erreur = "Error when transferring the avatar: ";
        }
        if ($_FILES['avatar']['size'] > $maxsize)
        {
            $i++;
            $avatar_erreur1 = "<li>The file is too big: (<strong>".$_FILES['avatar']['size']." Octets</strong> against <strong>".$maxsize." Octets</strong>)</li>";
        }

        $image_sizes = getimagesize($_FILES['avatar']['tmp_name']);
        if ($image_sizes[0] > $maxwidth OR $image_sizes[1] > $maxheight)
        {
            $i++;
            $avatar_erreur2 = "<li>Image too large or too long: (<strong>".$image_sizes[0]."x".$image_sizes[1]."</strong> against <strong>".$maxwidth."x".$maxheight."</strong>)</li>";
        }
        
        $extension_upload = strtolower(substr(  strrchr($_FILES['avatar']['name'], '.')  ,1));
        if (!in_array($extension_upload,$extensions_valides) )
        {
            $i++;
            $avatar_erreur3 = "<li>Incorrect avatar extension</li>";
        }
    }

   if ($i==0)
   {
       echo '<div class="ui main container animated fadeIn">';
	   echo '<div class="ui two top attached ordered steps">
       <div class="completed step">
            <div class="content">
                <div class="title">Registration</div>
                <div class="description">Create your account on our forum</div>
            </div>
        </div>
        <div class="completed active step">
            <div class="content">
                <div class="title">Registration Successful</div>
                <div class="description">Successful registration, login to your account</div>
            </div>
        </div>
        </div>';
       echo '<div class="ui attached padded segment">
       <div class="ui icon positive message"><i class="smile icon"></i><div class="content"><div class="header">You have successfully registered!</div><p>Welcome <strong>'.stripslashes(htmlspecialchars($_POST['pseudo'])).'</strong>, you are now registered and logged in on the forum!</p></div></div>
       <div class="ui divider"></div>
       <center>
            <a href="./index.php" class="ui animated fade positive button" tabindex="0">
                <div class="visible content">Go to the Homepage</div>
                <div class="hidden content"><i class="wizard icon"></i> It is magic</div>
            </a>
        </center>
        </div>';
       echo '</div>';
	
       // If no avatar has been chosen then it is replaced by the default one
       $nomavatar = (!empty($_FILES['avatar']['size']))?move_avatar($_FILES['avatar']):''.$config['avatar_default'].'';
       
       // If everything is correct, insert everything in the database
       $query=$db->prepare('INSERT INTO forum_membres (membre_pseudo, membre_mdp, membre_email, membre_btag, membre_siteweb, membre_avatar,
        membre_signature, membre_localisation, membre_inscrit, membre_derniere_visite) VALUES (:pseudo, :pass, :email, :btag, :website, :nomavatar, :signature, :localisation, :temps, :temps)');
       $query->bindValue(':pseudo', $pseudo, PDO::PARAM_STR);
       $query->bindValue(':pass', $pass, PDO::PARAM_INT);
       $query->bindValue(':email', $email, PDO::PARAM_STR);
       $query->bindValue(':btag', $btag, PDO::PARAM_STR);
       $query->bindValue(':website', $website, PDO::PARAM_STR);
       $query->bindValue(':nomavatar', $nomavatar, PDO::PARAM_STR);
       $query->bindValue(':signature', $signature, PDO::PARAM_STR);
       $query->bindValue(':localisation', $localisation, PDO::PARAM_STR);
       $query->bindValue(':temps', $temps, PDO::PARAM_INT);
       $query->execute();

       // And we define the session variables
       $_SESSION['pseudo'] = $pseudo;
       $_SESSION['id'] = $db->lastInsertId();
       $_SESSION['level'] = 2;
       $query->CloseCursor();
   }
    else
    {
        echo '<div class="ui main container animated fadeIn">';
        echo '<div class="ui two top attached stackable steps">
        <div class="active step">
            <i class="remove red icon"></i>
            <div class="content">
                <div class="title">Registration</div>
                <div class="description">Create your account on our forum</div>
            </div>
        </div>
        <div class="disabled step">
            <i class="remove icon"></i>
            <div class="content">
                <div class="title">Registration Successful</div>
                <div class="description">Successful registration, login to your account</div>
            </div>
        </div>
        </div>';
        echo '<div class="ui attached padded segment">
        <div class="ui icon error message"><i class="frown icon"></i><div class="content"><div class="header">Registration interrupted!</div><p><strong style="color:red;">'.$i.'</strong> error(s) occurred during registration.<ul>';
        echo''.$pseudo_erreur1.'';
        echo''.$pseudo_erreur2.'';
        echo''.$mdp_erreur.'';
        echo''.$email_erreur1.'';
        echo''.$email_erreur2.'';
        echo''.$signature_erreur.'';
        echo''.$avatar_erreur.'';
        echo''.$avatar_erreur1.'';
        echo''.$avatar_erreur2.'';
        echo''.$avatar_erreur3.'';
        echo '</ul></p></div></div>
        <div class="ui divider"></div>
        <center>
            <a href="./register.php" class="ui animated fade negative button" tabindex="0">
                <div class="visible content">Click here to start over</div>
                <div class="hidden content"><i class="arrow left icon"></i></div>
            </a>
        </center>
        </div>';
        echo '</div>';
    }
}
?>
</body>
</html>