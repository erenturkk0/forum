<?php
session_start();
$titre="Profile";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");
// We retrieve the value of our variables passed by URL
$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'consulter';
$membre = isset($_GET['m'])?(int) $_GET['m']:'';

// We look at the value of the variable $action
switch($action)
    {
        case "consulter": // If it is "to consult"
        
        // We retrieve member info
        $query=$db->prepare('SELECT membre_id, membre_pseudo, membre_avatar, membre_email, membre_btag, membre_signature, membre_siteweb, membre_post, membre_inscrit, membre_localisation, membre_rang FROM forum_membres WHERE membre_id=:membre');
        $query->bindValue(':membre',$membre, PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        
        // Show member info
        $rang = array
            (0 => '<div class="ui large red label"><i class="shield icon"></i> Banned</div>',
             1 => '<div class="ui large label"><i class="shield icon"></i> Visitor</div>',
             2 => '<div class="ui large blue label"><i class="shield icon"></i> Member</div>',
             3 => '<div class="ui large green label"><i class="shield icon"></i> Moderator</div>',
             4 => '<div class="ui large purple label"><i class="shield icon"></i> Administrator</div>'); // Ce tableau associe numéro de droit et nom
        for($i=0;$i<5;$i++)

            if ($i == $data['membre_rang'])
                { 
                    echo '<div class="ui main text container animated fadeIn">';
                    echo '<img class="ui centered aligned circular small image animated bounce" src="./images/avatars/'.$data['membre_avatar'].'" alt="Ce membre n a pas d avatar"><h1 class="ui center aligned header animated bounce">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</h1><div class="ui horizontal segments">
                    <div class="ui grey segment">
                    <center>
                    <a href="mailto:'.stripslashes($data['membre_email']).'" class="ui large label"><i class="mail blue icon"></i> '.stripslashes(htmlspecialchars($data['membre_email'])).'</a>
                    </center>
                    <div class="ui hidden divider"></div>
                    <center>
                    <a href="'.stripslashes($data['membre_siteweb']).'" target="_blank" class="ui large label"><i class="globe blue icon"></i> '.stripslashes(htmlspecialchars($data['membre_siteweb'])).'</a>
                    </center>
                    <div class="ui hidden divider"></div>
                    </div>
                        <div class="ui grey segment">
                            <center>
                                <div class="ui large label"><i class="fire teal icon"></i> '.stripslashes(htmlspecialchars($data['membre_btag'])).'</div>
                            </center>
                            <div class="ui hidden divider"></div>
                            <center>
                                '.$rang[$i].'
                            </center>
                            <div class="ui hidden divider"></div>
                        </div>
                        <center>
                            <div class="ui bottom attached label">This member is registered since '.date('d/m/Y',$data['membre_inscrit']).' and has posted '.$data['membre_post'].' message(s).</div>
                        </center>
                    </div>
                    <div class="ui divider"></div>';
                
                    echo '<div class="dynamic no example"><div class="ui pointing secondary menu">
                    <a class="active item" data-tab="first"><i class="trophy icon"></i> Achievements</a>
                    <a class="item" data-tab="second"><i class="italic icon"></i> Signature</a>
                    </div>
                    <div class="ui active tab '.$config['forum_color'].' segment" data-tab="first">';
                    echo '<div class="ui horizontal list">';
                    // Show badges
                    include_once("./includes/badges.config.php");
                    echo '</div>';
                    echo '</div>
                    <div class="ui tab '.$config['forum_color'].' segment" data-tab="second">
                    '.stripslashes(htmlspecialchars($data['membre_signature'])).'
                    </div>
                    </div>';
                
                    echo "<script>
                    $('.ui.dropdown')
                    .dropdown()
                    ;
                    $('.dynamic.example .menu .item')
                    .tab({
                        cache: false,
                        // faking API request
                        apiSettings: {
                          loadingDuration : 300,
                          mockResponse    : function(settings) {
                            var response = {

                            };
                            return response[settings.urlData.tab];
                          }
                        },
                        context : 'parent',
                        auto    : true,
                        path    : '/'
                      })
                      ;
                      </script>";
                }
        
            // If the id of the member is that of the profile then one displays the button to modify its profile
            if (isset($_SESSION['id']) AND $data['membre_id'] == $_SESSION['id'])
                {
                    echo '<a href="voirprofil.php?action=modifier" class="ui fluid '.$config['forum_color'].' button"><i class="edit icon"></i> Edit my profile</a>';
                }

            echo'</div>';
        
        $query->CloseCursor();
        break;
        
        
        case "modifier": // If you choose to edit your profile
        
        if (empty($_POST['sent'])) // If the variable is empty, we can consider that we are on the form page
            {
                // First, make sure the member is logged in
                if ($id==0) erreur(ERR_IS_NOT_CO);

                // We take the member's info
                $query=$db->prepare('SELECT membre_pseudo, membre_mdp, membre_email, membre_siteweb, membre_signature, membre_btag, membre_localisation, membre_avatar FROM forum_membres WHERE membre_id=:id');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $data=$query->fetch();

                echo '<div class="ui main container animated fadeIn"><div class="ui segment">';
                echo '<div class="ui container">
                <div class="ui basic segment">
                    <h3 class="ui header"><i class="large icons"><i class="user outline icon"></i><i class=" corner edit icon"></i></i>
                        <div class="content"> Edit profile
                            <div class="sub header">Edit your account informations.</div>
                        </div>
                    </h3>
                    <div class="ui clearing divider"></div>
                    <center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><div class="ui breadcrumb"><a href="./voirprofil.php?m='.$id.'&action=consulter" class="section">My profile</a><div class="divider"> / </div><div class="active section">Edit profile</div></div></center><div class="ui divider hidden"></div>';
                
                echo '<form method="post" action="voirprofil.php?action=modifier" class="ui form" enctype="multipart/form-data">
                <h4 class="ui dividing header">Identifiants</h4>
                <div class="disabled field">
                    <label for="pseudo">Username</label>
                    <div class="ui left icon input">
                        <i class="user icon"></i>
                        <input type="text" id="pseudo" value="'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'" disabled="" placeholder="Your username..." tabindex="-1">
                    </div>
                </div>
                <div class="two fields">
                    <div class="required field">
                        <label for="password">New or current password</label>
                        <div class="ui left icon input">
                            <i class="lock icon"></i>
                            <input type="password" name="password" id="password" required>
                        </div>
                    </div>
                    <div class="required field">
                        <label for="confirm">Confirm password</label>
                        <div class="ui left icon input">
                            <i class="lock icon"></i>
                            <input type="password" name="confirm" id="confirm" required>
                        </div>
                    </div>
                </div>

                <h4 class="ui dividing header">Contacts</h4>
                <div class="field">
                    <label for="email">Your E-mail address</label>
                    <div class="ui left icon input">
                        <i class="at icon"></i>
                        <input type="text" name="email" id="email" value="'.stripslashes($data['membre_email']).'">
                    </div>
                </div>
                <div class="field">
                    <label for="btag">Your BattleTag</label>
                    <div class="ui left icon input">
                        <i class="fire icon"></i>
                        <input type="text" name="btag" id="btag" value="'.stripslashes($data['membre_btag']).'">
                    </div>
                </div>
                <div class="field">
                    <label for="website">Your Website</label>
                    <div class="ui left icon input">
                        <i class="globe icon"></i>
                        <input type="text" name="website" id="website" value="'.stripslashes($data['membre_siteweb']).'">
                    </div>
                </div>

                <h4 class="ui dividing header">Additional Information</h4>
                <div class="field">
                    <label for="localisation">Localization</label>
                    <div class="ui left icon input">
                        <i class="flag outline icon"></i>
                        <input type="text" name="localisation" id="localisation" value="'.stripslashes($data['membre_localisation']).'">
                    </div>
                </div>

                <h4 class="ui dividing header">Profile on the forum</h4>
                <div class="field">
                    <label for="avatar">Change your avatar</label>
                    <div class="ui left icon input">
                        <i class="id badge icon"></i>
                        <input type="file" name="avatar" id="avatar" class="ui button" />
                    </div>
                    <div class="ui pointing label">Max size is <strong style="color:red;">'.$config['avatar_maxsize'].'</strong> Octets.</div>
                </div>
                <div class="field">
                <img src="./images/avatars/'.$data['membre_avatar'].'" class="ui small circular image">
                    <div class="ui checkbox">
                        <input type="checkbox" name="delete" value="Delete">
                        <label>Remove the avatar?</label>
                    </div>
                </div>
                <div class="field">
                    <label for="signature">Signature</label>
                    <textarea name="signature" id="signature" rows="3">'.stripslashes($data['membre_signature']).'</textarea>
                </div>
                <div class="ui divider"></div>
                <button type="submit" class="ui fluid positive button">Save informations</button>
                <input type="hidden" id="sent" name="sent" value="1" />
                </form>';
                
                echo '</div></div></div><div class="ui divider hidden"></div>';
            
            $query->CloseCursor();   
            }
        else // Cases of treatment
            {
                // The variables are declared
                $mdp_erreur = NULL;
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
                $signature = $_POST['signature'];
                $email = $_POST['email'];
                $btag = $_POST['btag'];
                $website = $_POST['website'];
                $localisation = $_POST['localisation'];
                $pass = md5($_POST['password']);
                $confirm = md5($_POST['confirm']);

                // Checking the password
                if ($pass != $confirm || empty($confirm) || empty($pass))
                    {
                        $mdp_erreur = "<li>Your <strong>Password</strong> and your <strong>Confirm password</strong> will differ, or are empty.</li>";
                        $i++;
                    }

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
            // Verification of signature
            if (strlen($signature) > ($config['sign_maxl']))
                {
                    $signature_erreur = "<li>Your new <strong>Signature</strong> is too long.</li>";
                    $i++;
                }
            // Avatar verification
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

            echo '<div class="ui main container animated fadeIn"><div class="ui segment">';
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

                    echo '<div class="ui icon positive message"><i class="smile icon"></i><div class="content"><div class="header">Congratulations!</div><p>Your profile has been <strong>modified</strong> successfully.</p></div></div>
                    <div class="ui divider"></div>
                    <center>
                        <a href="./index.php" class="ui animated fade positive button" tabindex="0">
                            <div class="visible content">Go to the Homepage</div>
                            <div class="hidden content"><i class="wizard icon"></i> It is magic</div>
                        </a>
                    </center>
                    </div>';
 
                    // The table is modified
                    $query=$db->prepare('UPDATE forum_membres SET  membre_mdp = :mdp, membre_email=:mail, membre_btag=:btag, membre_siteweb=:website, membre_signature=:sign, membre_localisation=:loc WHERE membre_id=:id');
                    $query->bindValue(':mdp',$pass,PDO::PARAM_INT);
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
                    echo''.$mdp_erreur.'';
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
                        <a href="./voirprofil.php?action=modifier" class="ui animated fade negative button" tabindex="0">
                            <div class="visible content">Click here to start over</div>
                            <div class="hidden content"><i class="arrow left icon"></i></div>
                        </a>
                    </center>
                    </div>';
                }
            } // End of else
        break;
        
        
        default; // If it is none of those there is that there was a problem :o
        echo'<p>This action is impossible!</p>';
    } // End of switch
?>
</body>
</html>