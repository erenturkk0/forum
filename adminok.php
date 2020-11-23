<?php
session_start();
$titre="Administration";
$balises = true;
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);

$cat = htmlspecialchars($_GET['cat']); // We retrieve in the url the variable cat

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">';

switch($cat) // 1st switch
    {
        case "config": // Configuration
        
        // We retrieve the values and the name of each entry of the table
        $query=$db->query('SELECT config_nom, config_valeur FROM forum_config');
        // With this loop, we will be able to control the result to see if it has changed
        while($data = $query->fetch())
            {
            if ($data['config_valeur'] != $_POST[$data['config_nom']])
                {
                // It is then updated
                $valeur = htmlspecialchars($_POST[$data['config_nom']]);
                $query=$db->prepare('UPDATE forum_config SET config_valeur = :valeur WHERE config_nom = :nom');
                $query->bindValue(':valeur', $valeur, PDO::PARAM_STR);
                $query->bindValue(':nom', $data['config_nom'],PDO::PARAM_STR);
                $query->execute();
                }
            }
        $query->CloseCursor();
        
        // And the message!
        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Forum settings</h3></div>',
        '<div class="ui '.$config['forum_color'].' padded segment">
        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>New configurations have been <strong>updated</strong>!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
        
        break;
        
        
        case "configannounce": // Announcement Configuration
        
        // We retrieve the values and the name of each entry of the table
        $header = $_POST['header'];
	    $message = $_POST['message'];
	    $color = $_POST['color'];
        $statut = $_POST['statut'];

        // Update
        $query=$db->prepare('UPDATE forum_announce SET header = :header, message = :message, color = :color, statut = :statut');
        $query->bindValue(':header',$header,PDO::PARAM_STR);
        $query->bindValue(':message',$message,PDO::PARAM_STR);
        $query->bindValue(':color',$color,PDO::PARAM_STR);
        $query->bindValue(':statut',$statut,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        $query->CloseCursor();
        // And the message!
        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Announce settings</h3></div>',
        '<div class="ui '.$config['forum_color'].' padded segment">
        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>New announce configurations have been <strong>updated</strong>!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
        
        break;
        
        
        case "forum": // Here Forums
        
        $action = htmlspecialchars($_GET['action']); // On récupère la valeur de action
        
        switch($action) // 2nd switch
            {
                case "creer":
                
                // We start with forums
                if ($_GET['c'] == "f")
                    {
                        $titre = $_POST['nom'];
                        $desc = $_POST['desc'];
                        $cat = (int) $_POST['cat'];

                        $query=$db->prepare('INSERT INTO forum_forum (forum_cat_id, forum_name, forum_desc) VALUES (:cat, :titre, :desc)');
                        $query->bindValue(':cat',$cat,PDO::PARAM_INT);
                        $query->bindValue(':titre',$titre, PDO::PARAM_STR);
                        $query->bindValue(':desc',$desc,PDO::PARAM_STR);
                        $query->execute();
                    
                        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Create a forum</h3></div>',
                        '<div class="ui '.$config['forum_color'].' padded segment">
                        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The forum was created! <strong>Do not forget to modify the rights of this forum</strong>.</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                    
                        $query->CloseCursor();
                    }
                // Then by categories
                elseif ($_GET['c'] == "c")
                    {
                        $titre = $_POST['nom'];
                        $query=$db->prepare('INSERT INTO forum_categorie (cat_nom) VALUES (:titre)');
                        $query->bindValue(':titre',$titre, PDO::PARAM_STR);
                        $query->execute();
                    
                        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Create a category</h3></div>',
                        '<div class="ui '.$config['forum_color'].' padded segment">
                        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>category</strong> was created!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                    
                        $query->CloseCursor();
                    }
                
                break;
                
                
                case "edit":
        
                if($_GET['e'] == "editf")
                    {
                        // Retrieving informations
                        $titre = $_POST['nom'];
                        $desc = $_POST['desc'];
                        $cat = (int) $_POST['depl'];

                        // Verification
                        $query=$db->prepare('SELECT COUNT(*) FROM forum_forum WHERE forum_id = :id');
                        $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
                        $query->execute();
                        $forum_existe=$query->fetchColumn();
                        $query->CloseCursor();
                    
                        if ($forum_existe == 0) erreur(ERR_FOR_EXIST);

                        // Update
                        $query=$db->prepare('UPDATE forum_forum SET forum_cat_id = :cat, forum_name = :name, forum_desc = :desc WHERE forum_id = :id');
                        $query->bindValue(':cat',$cat,PDO::PARAM_INT);
                        $query->bindValue(':name',$titre,PDO::PARAM_STR);
                        $query->bindValue(':desc',$desc,PDO::PARAM_STR);
                        $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
                        $query->execute();
                        $query->CloseCursor();
                    
                        // Message
                        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Editing a forum</h3></div>',
                        '<div class="ui '.$config['forum_color'].' padded segment">
                        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>forum</strong> has been modified!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                    }
                elseif($_GET['e'] == "editc")
                    {
                        // Retrieving informations
                        $titre = $_POST['nom'];

                        // Verification
                        $query=$db->prepare('SELECT COUNT(*) FROM forum_categorie WHERE cat_id = :cat');
                        $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
                        $query->execute();
                        $cat_existe=$query->fetchColumn();
                        $query->CloseCursor();
                        if ($cat_existe == 0) erreur(ERR_CAT_EXIST);

                        // Update
                        $query=$db->prepare('UPDATE forum_categorie SET cat_nom = :name WHERE cat_id = :cat');
                        $query->bindValue(':name',$titre,PDO::PARAM_STR);
                        $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
                        $query->execute();
                        $query->CloseCursor();

                        // Message
                        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Editing a category</h3></div>',
                        '<div class="ui '.$config['forum_color'].' padded segment">
                        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>category</strong> has been modified!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                    }
                elseif($_GET['e'] == "ordref")
                    {
                        // We retrieve the id and order of all forums
                        $query=$db->query('SELECT forum_id, forum_ordre FROM forum_forum');

                        // We loop the results
                        while($data= $query->fetch())
                            {
                            $ordre = (int) $_POST[$data['forum_id']]; 

                            // If and only if the order is different from the old one, it is updated
                            if ($data['forum_ordre'] != $ordre)
                                {
                                    $query=$db->prepare('UPDATE forum_forum SET forum_ordre = :ordre WHERE forum_id = :id');
                                    $query->bindValue(':ordre',$ordre,PDO::PARAM_INT);
                                    $query->bindValue(':id',$data['forum_id'],PDO::PARAM_INT);
                                    $query->execute();
                                    $query->CloseCursor();
                                }
                            } 
                        $query->CloseCursor();
                    
                        // Message
                        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Sort a forum</h3></div>',
                        '<div class="ui '.$config['forum_color'].' padded segment">
                        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>order</strong> has been changed!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                    }
                elseif($_GET['e'] == "ordrec")
                    {
                        // We retrieve ids and orders from all categories
                        $query=$db->query('SELECT cat_id, cat_ordre FROM forum_categorie');
        
                        // Loop all
                        while($data = $query->fetch())
                        {
                            $ordre = (int) $_POST[$data['cat_id']];

                            // It is updated if the order has changed
                            if($data['cat_ordre'] != $ordre)
                            {
                                $query=$db->prepare('UPDATE forum_categorie SET cat_ordre = :ordre WHERE cat_id = :id');
                                $query->bindValue(':ordre',$ordre,PDO::PARAM_INT);
                                $query->bindValue(':id',$data['cat_id'],PDO::PARAM_INT);
                                $query->execute();
                                $query->CloseCursor();
                            }
                        }
                    // Message
                    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Sort a category</h3></div>',
                    '<div class="ui '.$config['forum_color'].' padded segment">
                    <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>order</strong> has been changed!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                    }
                
                break;
                
                
                case "droits":
                
                // Retrieving informations
                $auth_view = (int) $_POST['auth_view'];
                $auth_post = (int) $_POST['auth_post'];
                $auth_topic = (int) $_POST['auth_topic'];
                $auth_annonce = (int) $_POST['auth_annonce'];
                $auth_modo = (int) $_POST['auth_modo'];

                // Update
                $query=$db->prepare('UPDATE forum_forum SET auth_view = :view, auth_post = :post, auth_topic = :topic, auth_annonce = :annonce, auth_modo = :modo WHERE forum_id = :id');
                $query->bindValue(':view',$auth_view,PDO::PARAM_INT);
                $query->bindValue(':post',$auth_post,PDO::PARAM_INT);
                $query->bindValue(':topic',$auth_topic,PDO::PARAM_INT);
                $query->bindValue(':annonce',$auth_annonce,PDO::PARAM_INT);
                $query->bindValue(':modo',$auth_modo,PDO::PARAM_INT);
                $query->bindValue(':id',(int) $_POST['forum_id'],PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();

                // Message
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Rights settings</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>rights</strong> have been changed!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                
                break;
            } // End of the switch
        break;
        
        
        case "membres":
        
        if (empty($_POST['sent'])) // If the variable is empty, we can consider that we are on the form page
            {
                // The nickname must be unique!
                // It is therefore necessary to check if it has been modified, if this is the case, one verifies the uniqueness
                $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_pseudo =:pseudo');
                $query->execute(array('pseudo'=>$pseudo));
                $pseudo_free=($query->fetchColumn()==0)?1:0;

                if(!$pseudo_free)
                    {
                        $pseudo_erreur1 = "<li>Your username is already used by a member</li>";
                        $i++;
                    }
                $query->CloseCursor(); 
        
                // The variables are declared 
                $pseudo_erreur1 = NULL;
                $email_erreur1 = NULL;
                $email_erreur2 = NULL;
                $signature_erreur = NULL;
                $avatar_erreur = NULL;
                $avatar_erreur1 = NULL;
                $avatar_erreur2 = NULL;
                $avatar_erreur3 = NULL;

                // Again and again our beautiful variable $i
                $i = 0;
                $temps = time();
                $id = $_POST['membre_id'];
                $pseudo1 = $_POST['pseudo1'];
                $signature = $_POST['signature'];
                $email = $_POST['email'];
                $btag = $_POST['btag'];
                $website = $_POST['website'];
                $localisation = $_POST['localisation'];

                // Checking email address
                // The email address must never have been used (unless it has not been modified)
                // So we start by retrieving the mail
                $query=$db->prepare('SELECT membre_email FROM forum_membres WHERE membre_id =:id');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $data=$query->fetch();
                
                if (strtolower($data['membre_email']) != strtolower($email))
                    {
                        // The email address has never been used
                        $query=$db->prepare('SELECT COUNT(*) AS nbr FROM forum_membres WHERE membre_email =:mail');
                        $query->bindValue(':mail',$email,PDO::PARAM_STR);
                        $query->execute();
                        $mail_free=($query->fetchColumn()==0)?1:0;
                        $query->CloseCursor();
                    
                        if(!$mail_free)
                            {
                                $email_erreur1 = "<li>Your <strong>E-mail</strong> address is <strong>already used</strong> by a member.</li>";
                                $i++;
                            }
                        // We check the shape now
                        if (!preg_match("#^[a-z0-9A-Z._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email) || empty($email))
                            {
                                $email_erreur2 = "<li>Your new <strong>E-Mail</strong> address does not have a valid format.</li>";
                                $i++;
                            }
                    }
                // Verification of the signature
                if (strlen($signature) > ($config['sign_maxl']))
                    {
                        $signature_erreur = "<li>Your new <strong>Signature</strong> is too long.</li>";
                        $i++;
                    }
                // Verification of the avatar
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

                echo '<div class="ui segment">';
                echo '<div class="ui container">
                <div class="ui basic segment">
                    <h3 class="ui header"><i class="large icons"><i class="user outline icon"></i><i class=" corner edit icon"></i></i>
                        <div class="content"> Edit profile
                            <div class="sub header">Edit your account informations.</div>
                        </div>
                    </h3>
                    <div class="ui clearing divider"></div>
                    <center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><div class="ui breadcrumb"><a href="./voirprofil.php?m='.$id.'&action=consulter" class="section">My profile</a><div class="divider"> / </div><div class="active section">Edit profile</div></div></center><div class="ui divider hidden"></div>';

                if ($i == 0) // If $i is empty, there is no error
                    {
                        if (!empty($_FILES['avatar']['size']))
                            {
                                $nomavatar=move_avatar($_FILES['avatar']);
                                $query=$db->prepare('UPDATE forum_membres SET membre_avatar = :avatar WHERE membre_id = :id');
                                $query->bindValue(':avatar',$nomavatar,PDO::PARAM_STR);
                                $query->bindValue(':id',$id,PDO::PARAM_INT);
                                $query->execute();
                                $query->CloseCursor();
                            }

                        // A novelty here: one can choose to remove the avatar
                        if (isset($_POST['delete']))
                            {
                                $avatardefault = $config['avatar_default'];
                                $query=$db->prepare('UPDATE forum_membres SET membre_avatar = :avatardefault WHERE membre_id = :id');
                                $query->bindValue(':id',$id,PDO::PARAM_INT);
                                $query->bindValue(':avatardefault',$avatardefault,PDO::PARAM_STR);
                                $query->execute();
                                $query->CloseCursor();
                            }

                        echo '<div class="ui icon positive message"><i class="smile icon"></i><div class="content"><div class="header">Congratulations!</div><p>His profile has been <strong>modified</strong> successfully.</p></div></div>
                        <div class="ui divider"></div>
                        <center>
                            <a href="./admin.php" class="ui animated fade positive button" tabindex="0">
                                <div class="visible content">Go to the Dashboard</div>
                                <div class="hidden content"><i class="wizard icon"></i> It is magic</div>
                            </a>
                        </center>
                        </div></div>';

                        // The table is modified
                        $query=$db->prepare('UPDATE forum_membres SET  membre_pseudo = :pseudo, membre_email=:mail, membre_btag=:btag, membre_siteweb=:website, membre_signature=:sign, membre_localisation=:loc WHERE membre_id=:id');
                        $query->bindValue(':pseudo',$pseudo1,PDO::PARAM_INT);
                        $query->bindValue(':mail',$email,PDO::PARAM_STR);
                        $query->bindValue(':btag',$btag,PDO::PARAM_STR);
                        $query->bindValue(':website',$website,PDO::PARAM_STR);
                        $query->bindValue(':sign',$signature,PDO::PARAM_STR);
                        $query->bindValue(':loc',$localisation,PDO::PARAM_STR);
                        $query->bindValue(':id',$id,PDO::PARAM_INT);
                        $query->execute();
                        $query->CloseCursor();
                    }
                else
                    {
                        echo'<div class="ui icon error message"><i class="frown icon"></i><div class="content"><div class="header">Editing interrupted!</div><p><strong style="color:red;">'.$i.'</strong> error(s) occurred while editing the profile.<ul>';
                        echo''.$pseudo_erreur1.'';
                        echo''.$email_erreur1.'';
                        echo''.$email_erreur2.'';
                        echo''.$signature_erreur.'';
                        echo''.$avatar_erreur.'';
                        echo''.$avatar_erreur1.'';
                        echo''.$avatar_erreur2.'';
                        echo''.$avatar_erreur3.'';
                        echo'</ul></p></div></div>
                        <div class="ui divider"></div>
                        <center>
                            <a href="./admin.php?cat=membres&action=edit" class="ui animated fade negative button" tabindex="0">
                                <div class="visible content">Click here to start over</div>
                                <div class="hidden content"><i class="arrow left icon"></i></div>
                            </a>
                        </center>
                        </div></div>';
                    }
            }
        $query->CloseCursor();    
        
        
    case "droits":
        
	$membre = $_POST['pseudo'];
	$rang = (int) $_POST['droits'];
    
	$query=$db->prepare('UPDATE forum_membres SET membre_rang = :rang WHERE LOWER(membre_pseudo) = :pseudo');
    $query->bindValue(':rang',$rang,PDO::PARAM_INT);
    $query->bindValue(':pseudo',strtolower($membre), PDO::PARAM_STR);
    $query->execute();
    $query->CloseCursor();
        
    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Rights settings</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment">
    <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The member\'s <strong>level</strong> has been changed!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
    

    case "ban":
        
    // Banishment at first
    // If we have not left the field empty for the username
        if (isset($_POST['membre']) AND !empty($_POST['membre']))
        {
            $membre = $_POST['membre'];
            $query=$db->prepare('SELECT membre_id FROM forum_membres WHERE LOWER(membre_pseudo) = :pseudo');
            $query->bindValue(':pseudo',strtolower($membre), PDO::PARAM_STR);
            $query->execute();
            
            // If member exists
            if ($data = $query->fetch())
            {
                // It is banned
                $query=$db->prepare('UPDATE forum_membres SET membre_rang = 0 WHERE membre_id = :id');
                $query->bindValue(':id',$data['membre_id'], PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                echo '<div class="ui icon positive message"><i class="legal icon"></i><div class="content"><div class="header">Congratulations!</div><p>The member <strong>'.stripslashes(htmlspecialchars($membre)).'</strong> has been banned!</p><ul class="list"><li><a href="./admin.php?cat=membres&action=ban">Click here to go back to the management of banishments.</a></li></ul></div></div>';
            }
            else 
            {
                echo '<div class="ui icon warning message"><i class="help icon"></i><div class="content"><div class="header">Oops!</div><p>Sorry, the member <strong>'.stripslashes(htmlspecialchars($membre)).'</strong> does not exist!</p><ul class="list"><li><a href="./admin.php?cat=membres&action=ban">Click here to try again.</a></li></ul></div></div>';
            }
        }
    
        // Unban here   
        $query = $db->query('SELECT membre_id FROM forum_membres WHERE membre_rang = 0');
        // If you want to unban at least one member
        if ($query->rowCount() > 0)
            {
                $i=0;
                while($data= $query->fetch())
                {
                    if(isset($_POST[$data['membre_id']]))
                    {
                        $i++;
                        // We put our rank back to 2
                        $query=$db->prepare('UPDATE forum_membres SET membre_rang = 2 WHERE membre_id = :id');
                        $query->bindValue(':id',$data['membre_id'],PDO::PARAM_INT);
                        $query->execute();
                        $query->CloseCursor();
                    }
                }
            if ($i!=0)
                echo'<div class="ui icon positive message"><i class="legal icon"></i><div class="content"><div class="header">Success!</div><p>Members have been debanned!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
            }
        
        break;
    }
echo '</div>';
echo '<div class="ui secondary segment"><p><i class="circular quote right icon"></i> <span class="qoutes"></span> #<span class="author"></span></p></div></body></html>';
?>