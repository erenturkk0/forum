<?php
session_start();
$titre="Friends management";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

$action = isset($_GET['action'])?htmlspecialchars($_GET['action']):'';

if ($id==0) erreur(ERR_IS_NOT_CO);

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">',
'<div class="ui center aligned segment"><h3 class="ui header">Friends Management</h3></div>',
'<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><div class="active section">Friends Management</div></div></center><div class="ui divider"></div>';


switch($action)
    {
        case "add": // We want to add a friend
        
        if (!isset($_POST['pseudo']))
            {
                echo '<form action="amis.php?action=add" method="post" class="ui form">
                <div class="field required">
                    <label for="pseudo">Enter the Username</label>
                    <div class="ui left icon input">
                        <i class="user add icon"></i>
                        <input type="text" name="pseudo" id="pseudo" required>
                    </div>
                </div>
                <button class="ui inverted green button" type="submit" value="Envoyer">Add friend</button>
                </form><div class="ui divider"></div>';
            }
        else
            {
                // We verify that the username returns something
                $pseudo_d = $_POST['pseudo'];

                $query=$db->prepare('SELECT membre_id, COUNT(*) AS nbr FROM forum_membres WHERE LOWER(membre_pseudo) = :pseudo GROUP BY membre_pseudo');
                $query->bindValue(':pseudo',strtolower($pseudo_d),PDO::PARAM_STR);
                $query->execute();
                $data = $query->fetch();
                $pseudo_exist = $data['nbr'];
                $i = 0;
                $id_to=$data['membre_id'];
            
                if(!$pseudo_exist) // If the username/member does not exist
                    {
                        echo '<div class="ui icon warning message"><i class="info icon"></i><div class="content"><div class="header">Oops!</div><p>This member does not appear to exist.</p><ul class="list"><li><a href="./amis.php?action=add">Click here to try again!</a></li></ul></div></div><div class="ui divider"></div>';
                        $i++;
                    }
                $query->CloseCursor();
            
                $query = $db->prepare('SELECT COUNT(*) AS nbr FROM forum_amis WHERE ami_from = :id AND ami_to = :id_to OR ami_from = :id AND ami_to = :id_to');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->bindValue(':id_to', $id_to, PDO::PARAM_INT);
                $query->execute();
                $deja_ami=$query->fetchColumn();
                $query->CloseCursor();

                if ($deja_ami != 0) // The member is already your friend or has already made a request that you have not accepted or refused
                    {
                        echo '<div class="ui icon warning message"><i class="info icon"></i><div class="content"><div class="header">Oops!</div><p>This member already belongs to your friends or has already proposed his / her friendship.</p><ul class="list"><li><a href="./amis.php?action=add">Click here to try again!</a></li></ul></div></div><div class="ui divider"></div>';
                        $i++;
                    }
                if ($id_to == $id) // If you try to add yourself
                    {
                        echo '<div class="ui icon negative message"><i class="remove icon"></i><div class="content"><div class="header">Oops!</div><p>You can not add yourself.</p><ul class="list"><li><a href="./amis.php?action=add">Click here to try again!</a></li></ul></div></div><div class="ui divider"></div>';
                        $i++;
                    }
                if ($i == 0)
                    {
                        $query=$db->prepare('INSERT INTO forum_amis (ami_from, ami_to, ami_confirm, ami_date) VALUES(:id, :id_to, :conf, :temps)');
                        $query->bindValue(':id',$id,PDO::PARAM_INT);
                        $query->bindValue(':id_to', $id_to, PDO::PARAM_INT);
                        $query->bindValue(':conf','0',PDO::PARAM_STR);
                        $query->bindValue(':temps', time(), PDO::PARAM_INT);
                        $query->execute();
                        $query->CloseCursor();

                        echo '<div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p><strong><a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter" target="_blank">'.stripslashes(htmlspecialchars($pseudo_d)).'</a></strong> has been added to your friends, however, he or she must agree.</p><ul class="list"><li><a href="./amis.php">Click here to return to your friends list.</a></li></ul></div></div><div class="ui divider"></div>';
                    }
            }


        case "check":
        
        $add = (isset($_GET['add']))?htmlspecialchars($_GET['add']):0;
        
        if (empty($add))
            {
                $query = $db->prepare('SELECT ami_from, ami_date, membre_avatar, membre_pseudo FROM forum_amis LEFT JOIN forum_membres ON membre_id = ami_from WHERE ami_to = :id AND ami_confirm = :conf ORDER BY ami_date DESC');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->bindValue(':conf','0',PDO::PARAM_STR);
                $query->execute();

                if ($query->rowCount() == 0)
                    {
                        echo '<div class="ui icon info message"><i class="meh loading icon"></i><div class="content"><div class="header">Hello!</div><p>You have no proposal.</p></div></div>';
                    }
                while ($data = $query->fetch())
                    {
                        echo '<table class="ui celled striped very basic table">
                        <thead>
                            <tr>
                                <th><i class="address card icon"></i> Username</th>
                                <th><i class="calendar card icon"></i> Date of request</th>
                                <th><blink><i class="legal loading icon"></i></blink> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="center aligned"><a href="./voirprofil.php?m='.$data['ami_from'].'&action=consulter" class="ui label" target="_blank"><img class="ui avatar image" src="./images/avatars/'.stripslashes(htmlspecialchars($data['membre_avatar'])).'" /> '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
                                <td class="center aligned"><div class="ui basic '.$config['forum_color'].' label">'.date('d/m/Y',$data['ami_date']).'</div></td>
                                <td class="center aligned"><div class="ui buttons"><a href="./amis.php?action=check&add=ok&m='.$data['ami_from'].'" class="ui animated fade positive button"><div class="visible content">Accept</div><div class="hidden content"><i class="user add icon"></i></div></a><a href="./amis.php?action=delete&m='.$data['ami_from'].'" class="ui animated fade negative button"><div class="visible content">Refuse</div><div class="hidden content"><i class="user remove icon"></i></div></a></div></td>
                            </tr>
                        </tbody>
                        </table>';
                    }
                $query->CloseCursor();
            }
        else
            {
                $membre = (int) $_GET['m'];
                $query = $db->prepare('UPDATE forum_amis SET ami_confirm = :conf WHERE ami_from = :membre AND ami_to = :id');
                $query->bindValue(':conf','1',PDO::PARAM_STR);
                $query->bindValue(':membre',$membre,PDO::PARAM_INT);
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->closeCursor();

                echo '<div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The member has been added to your friends list, <strong>you have a new friend</strong>.</p><ul class="list"><li><a href="./amis.php">Click here to return to your friends list.</a></li></ul></div></div><div class="ui divider"></div>';
            }
        break;


        case "delete":
        
        $membre = (int) $_GET['m'];
        
        if (!isset($_GET['ok']))
            {
                echo '<center><h1 class="ui center aligned icon header"><i class="circular warning sign orange icon"></i><div class="content animated rubberBand">Be careful!<div class="sub header">Removing a Friend.</div></div></h1></center><div class="ui icon warning message"><i class="warning icon"></i><div class="content"><div class="header">Warning!</div><p>Are you sure you want to <strong>delete this friend</strong>?</p></div></div><center><div class="ui buttons"><a href="./amis.php?action=delete&ok=ok&m='.$membre.'" class="ui positive button">Yes I have too much friend</a><a href="./amis.php" class="ui negative button">No I do not have enough friends</a></div></center>';
            }
        else
            {
                $query = $db->prepare('DELETE FROM forum_amis WHERE ami_from = :membre AND ami_to = :id');
                $query->bindValue(':membre',$membre,PDO::PARAM_INT);
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->closeCursor();

                $query = $db->prepare('DELETE FROM forum_amis WHERE ami_to = :membre AND ami_from = :id');
                $query->bindValue(':membre',$membre,PDO::PARAM_INT);
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->execute();
                $query->closeCursor();

                echo '<div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">One lost ten recovered!</div><p>You just <strong>removed a friend</strong>. It was successfully deleted from your friend list.</p><ul class="list"><li><a href="./amis.php">Click here to return to your friends list.</a></li></ul></div></div><div class="ui divider"></div>';
            }
        break;


        default:

        $query = $db->prepare('SELECT (ami_from + ami_to - :id) AS ami_id, ami_date, membre_pseudo, membre_avatar, online_id FROM forum_amis LEFT JOIN forum_membres ON membre_id = (ami_from + ami_to - :id) LEFT JOIN forum_whosonline ON online_id = membre_id WHERE (ami_from = :id OR ami_to = :id) AND ami_confirm = :conf ORDER BY membre_pseudo');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->bindValue(':conf','1',PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() == 0)
            {
                echo '<div class="ui icon info message"><i class="frown loading icon"></i><div class="content"><div class="header">Go fishing!</div><p>You do not have any friends yet.</p></div></div><div class="ui divider hidden"></div>';
            }
        echo '<div class="ui link cards">';
        while ($data = $query->fetch())
            {
                echo '<div class="ui centered card">';
                if (!empty($data['online_id']))
                echo '<a class="ui right corner green label">
                <blink><i class="heartbeat icon"></i></blink>
                </a>';
                else
                echo '<a class="ui right corner red label">
                <blink><i class="heartbeat icon"></i></blink>
                </a>';
                echo '<a class="image" href="./voirprofil.php?m='.$data['ami_id'].'&action=consulter" target="_blank">
                    <img src="./images/avatars/'.$data['membre_avatar'].'">
                </a>
                <div class="content">
                    <div class="header">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</div>
                    <div class="description"><div class="ui basic violet label">'.date('d/m/Y',$data['ami_date']).'</div><div class="ui left pointing  basic label">Date added</div></div>
                </div>
                <div class="ui two bottom attached buttons">
                    <a href="./messagesprives.php?action=repondre&dest='.$data['ami_id'].'" class="ui '.$config['forum_color'].' button" data-tooltip="Submit an PM" data-position="bottom center"><i class="mail icon"></i></a>
                    <a href="./amis.php?action=delete&m='.$data['ami_id'].'" class="ui red button" data-tooltip="Remove friend" data-position="bottom center"><i class="remove user icon"></i></a>
                </div>
                </div>';
            }
        echo '</div>';
        $query->CloseCursor();

        // We count the number of requests in progress and we put some links
        $query=$db->prepare('SELECT COUNT(*) FROM forum_amis WHERE ami_to = :id AND ami_confirm = :conf');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->bindValue(':conf','0', PDO::PARAM_STR);
        $query->execute();
        $demande_ami=$query->fetchColumn();

        // This line will display 0 rather than a blank
        if (empty($demande_ami)) $demande_ami=0;
        
        echo '<div class="ui divider"></div><div class="ui three column grid">
        <div class="column">
            <div class="ui fluid green link card">
                <a href="./messagesprives.php?action=nouveau"><br>
                    <div class="content">
                        <h2 class="ui center aligned icon header"><i class="circular mail forward icon"></i> New private message</h2>
                    </div>
                </a>
            </div>
        </div>
        <div class="column">
            <div class="ui fluid yellow link card">
                <a href="./amis.php?action=add"><br>
                    <div class="content">
                        <h2 class="ui center aligned icon header"><i class="circular add user icon"></i> Add a friend</h2>
                    </div>
                </a>
            </div>
        </div>
        <div class="column">
            <div class="ui fluid purple link card">
                <a href="./amis.php?action=check"><br>
                    <div class="content">
                        <h2 class="ui center aligned icon header"><i class="circular unhide icon"></i> New friend request</h2>
                        <div class="floating ui purple circular label">'.$demande_ami.'</div>
                    </div>
                </a>
            </div>
        </div>
        </div>';

        break;
    } // End of the switch

echo '</div><div class="ui secondary segment"><p><i class="circular quote right icon"></i> <span class="qoutes"></span> #<span class="author"></span></p></div></div></div>';
?>
</body>
</html>