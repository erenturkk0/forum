<?php
session_start();
$titre="Private Messages";
$balises = true;
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");
require_once("JBBCode/Parser.php");

$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):''; // We retrieve the value of the variable $action

if ($id==0) erreur(ERR_IS_NOT_CO);

// BBCode parser
$parser = new JBBCode\Parser();
$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
$parser->addBBCode("video", '<iframe width="560" height="315" src="https://www.youtube.com/embed/{param}" frameborder="0" allowfullscreen></iframe>');
$parser->addBBCode("center", '<center>{param}</center>');
$parser->addBBCode("list", '<div class="ui bulleted list">{param}</div>');
$parser->addBBCode("*", '<div class="item">{param}</div>');
$parser->addBBCode("quote", '<div class="ui black label"><i class="quote right icon"></i> {param}</div>');
$parser->addBBCode("code", '<div class="ui tiny basic red label">{param}</div>');

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">',
'<div class="ui center aligned segment"><h3 class="ui header">Private message</h3></div>',
'<div class="ui '.$config['forum_color'].' padded segment">';

switch($action)
    {
        case "consulter": // 1st case: we want to read a pm
        // Here we need the value of the id of the pm that we want to read
        
        echo '<center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./messagesprives.php" class="section">Private messaging</a><div class="divider"> / </div><div class="active section">Viewing a message</div></div></center><div class="ui divider"></div>';
        $id_mess = (int) $_GET['id']; // We retrieve the value of the id
        

        // The query allows us to get the info on this message:
        $query = $db->prepare('SELECT  mp_expediteur, mp_receveur, mp_titre, mp_time, mp_text, mp_lu, membre_id, membre_pseudo, membre_avatar, membre_localisation, membre_inscrit, membre_post, membre_signature, membre_rang FROM forum_mp LEFT JOIN forum_membres ON membre_id = mp_expediteur WHERE mp_id = :id');
        $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();

        // Warning ! Only the receiver of the pm can read it!
        if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
       
        // Answer button
        echo'<a href="./messagesprives.php?action=repondre&dest='.$data['mp_expediteur'].'" class="ui small '.$config['forum_color'].' labeled icon button"><i class="reply icon"></i> Reply to this message</a>';
        
        // Now we display the whole in a table
        echo '<table class="ui very basic padded celled table">
        <thead>
            <tr>
                <th>Author</th>
                <th>Content</th>
            </tr>
        </thead>
        <tbody>';
        
        // Recovers the rank of the member
        $rang = array
                (0 => "<div class='ui red label'>Banned</div>",
                1 => "<div class='ui label'>Visitor</div>", 
                2 => "<div class='ui blue label'>Member</div>", 
                3 => "<div class='ui green label'>Modo</div>", 
                4 => "<div class='ui violet label'>Admin</div>"); // This table associates role number and name
            for($i=0;$i<5;$i++)
                
                if ($i == $data['membre_rang'])
                    
        echo '<tr><td class="collapsing"><span data-tooltip="Messages: '.$data['membre_post'].' / Joined: '.date('d/m/Y',$data['membre_inscrit']).'" data-position="left center"><img class="ui centered tiny circular image" src="./images/avatars/'.$data['membre_avatar'].'"></span><br><center>'.$rang[$i].'</center></td>
        
        <td id="p_'.$data['post_id'].'"><h5 class="ui left floated header"><small><i class="clock icon"></i> Posted at '.date('H\hi \o\n d M y',$data['mp_time']).'</small></h5><h5 class="ui right floated header"><small>by <a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter" id="voirProfil" data-tooltip="Messages: '.$data['membre_post'].' / Joined: '.date('d/m/Y',$data['membre_inscrit']).'" data-position="right center">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></small></h5><div class="ui divider clearing"></div>';
        
        // Message with BBCode
        echo '<p>';
        $text_contenu = nl2br(stripslashes(htmlspecialchars($data['mp_text'])));
        $text_signature = nl2br(stripslashes(htmlspecialchars($data['membre_signature'])));
        
        $parser->parse($text_contenu, $text_signature);
        echo $parser->getAsHtml();
        echo '</p><div style="border-top: 1px dashed #EBEEF2;" class="ui divider"></div><small><h5>'.$text_signature.'</h5></small></td></tr>';
        
        echo '</tbody></table>';
       
        if ($data['mp_lu'] == 0) // If the message has never been read, then it is already read
            {
                $query->CloseCursor();
                $query=$db->prepare('UPDATE forum_mp SET mp_lu = :lu WHERE mp_id= :id');
                $query->bindValue(':id',$id_mess, PDO::PARAM_INT);
                $query->bindValue(':lu','1', PDO::PARAM_STR);
                $query->execute();
                $query->CloseCursor();
            }
        
        break;
        
        
        case "nouveau": // 2nd case: we want to post a new pm
        // Here we need the value of any variable :p
        
        // We show where we are
        echo '<center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./messagesprives.php" class="section">Private messaging</a><div class="divider"> / </div><div class="active section">Write a new message</div></div></center><div class="ui divider"></div>';

        // We display the form
        echo '<form method="post" action="postok.php?action=nouveaump" name="formulaire" class="ui form">
        <h4 class="ui dividing header">Form to write a new message</h4>
        <div class="field required">
            <label for="to">Send to</label>
            <input type="text" size="30" id="to" name="to" placeholder="e.g: Johndoe" required>
        </div>
        <div class="field required">
            <label for="titre">Title</label>
            <input type="text" size="80" id="titre" name="titre" placeholder="Title of the PM" required>
        </div>
        <div class="field required">
            <label for="editor">Message</label>
            <textarea id="editor" name="message"></textarea>
        </div>
        <button type="reset" name="Effacer" class="ui basic labeled icon button"><i class="erase icon"></i> Erase</button>
        <button type="submit" name="submit" class="ui primary right labeled icon button">Send <i class="share icon"></i></button>
        </form>';
        
        break;
        
        
        case "repondre": // 3rd case: we want to answer a pm received
        // Here we need the value of id of the member who posted us a pm
        
        // We show where we are
        echo '<center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./messagesprives.php" class="section">Private messaging</a><div class="divider"> / </div><div class="active section">Reply to a private message</div></div></center><div class="ui divider"></div>';
        
        // We display the form
        $dest = (int) $_GET['dest'];
        echo '<form method="post" action="postok.php?action=repondremp&dest='.$dest.'" name="formulaire" class="ui form">
        <h4 class="ui dividing header">Form to reply to a private message</h4>
        <div class="field required">
            <label for="titre">Title</label>
            <input type="text" size="80" id="titre" name="titre" placeholder="Title of the PM" required>
        </div>
        <div class="field required">
            <label for="editor">Message</label>
            <textarea id="editor" name="message"></textarea>
        </div>
        <button type="reset" name="Effacer" class="ui basic labeled icon button"><i class="erase icon"></i> Erase</button>
        <button type="submit" name="submit" class="ui primary right labeled icon button">Send <i class="share icon"></i></button>
        </form>';
        
        break;
        
        
        case "supprimer": // 4th case: we want to delete a received pm
        // Here we need the value of the id of the mp to remove
        
        // We retrieve the value of the id
        $id_mess = (int) $_GET['id'];
        
        // It should be checked that the member is the one who received the message
        $query=$db->prepare('SELECT mp_receveur FROM forum_mp WHERE mp_id = :id');
        $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        
        // Otherwise the sanction is terrible :p
        if ($id != $data['mp_receveur']) erreur(ERR_WRONG_USER);
        $query->CloseCursor();

        // 2 cases for this part: we are sure to delete or we are not
        $sur = (int) $_GET['sur'];
        // Not sure yet
        if ($sur == 0)
            {
                echo '
                    <h1 class="ui center aligned icon header"><blink><i class="circular orange warning sign icon"></i> Warning!</blink></h1><div class="ui divider"></div><div class="ui icon warning message"><i class="warning sign icon"></i><div class="content"><div class="header">Really?</div><p>Are you sure you want to <strong>delete</strong> this private message?</p></div></div><div class="two ui buttons"><a href="./messagesprives.php?action=supprimer&id='.$id_mess.'&sur=1" class="ui positive button">Yes I am sure</a><a href="./messagesprives.php" class="ui negative button">No I am not sure</a></div>';
            }
        // We are certain
        else
            {
                $query=$db->prepare('DELETE from forum_mp WHERE mp_id = :id');
                $query->bindValue(':id',$id_mess,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                echo '<div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>private message</strong> has been deleted!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./messagesprives.php">Click here to return to messaging.</a></li></ul></div></div>';
            }
        
        break;
        
        
        default; // If nothing is requested or if there is an error in the url, we display the pm box.
        
        // We show where we are
        echo '<center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><div class="active section">Private messaging</div></div></center><div class="ui divider"></div>';

        // We retrieve all that it has to take to display the list of private messages received
        $query=$db->prepare('SELECT mp_lu, mp_id, mp_expediteur, mp_titre, mp_time, membre_id, membre_pseudo, membre_avatar FROM forum_mp LEFT JOIN forum_membres ON forum_mp.mp_expediteur = forum_membres.membre_id WHERE mp_receveur = :id ORDER BY mp_id DESC');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        
        // Button for a new private message
        echo '<p><a href="./messagesprives.php?action=nouveau" class="ui small primary labeled icon button"><i class="write icon"></i> New message</a></p>';
        
        // If we have a message, it displays a beautiful table with its contents
        if ($query->rowCount()>0)
            {
                echo '
                <table class="ui very basic padded celled table">
                <thead>
                    <tr>
                        <th></th>
                        <th><strong>Title</strong></th>
                        <th><strong>Sender</strong></th>
                        <th><strong>Date</strong></th>
                        <th><strong>Action</strong></th>
                        </tr>
                </thead>
                <tbody>';
            
                // Loop and fill the table
                while ($data = $query->fetch())
                    {
                        echo'<tr>';
                        // Pm never read, we display the icon in question
                        if($data['mp_lu'] == 0)
                            {
                                echo '<td class="collapsing"><center><i class="big icons" data-tooltip="Unread" data-position="left center"><i class="big red circle icon"></i><i class="mail inverted icon"></i></i></center></td>';
                            }
                        else // Otherwise the icon already read
                            {
                                echo '<td class="collapsing"><center><i class="big icons" data-tooltip="Already read" data-position="left center"><i class="big green circle icon"></i><i class="open envelope inverted icon"></i></i></center></td>';
                            }
                        echo '<td class="center aligned">
                        <a href="./messagesprives.php?action=consulter&id='.$data['mp_id'].'" class="ui large label">
                        '.stripslashes(htmlspecialchars($data['mp_titre'])).'</a></td>
                        <td class="center aligned">
                        <a href="./voirprofil.php?action=consulter&m='.$data['membre_id'].'" class="ui large basic image label"><img src="./images/avatars/'.stripslashes(htmlspecialchars($data['membre_avatar'])).'">
                        '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
                        <td class="center aligned"><div class="ui large '.$config['forum_color'].' basic label">'.date('H\hi \o\n d M Y',$data['mp_time']).'</div></td>
                        <td class="center aligned"><div class="ui  basic icon buttons">
                        <a href="./messagesprives.php?action=consulter&id='.$data['mp_id'].'" class="ui button"><i class="unhide green icon"></i></a>
                        <a href="./messagesprives.php?action=supprimer&id='.$data['mp_id'].'&sur=0" class="ui button"><i class="trash red icon"></i></a>
                        </div></td></tr>';
                    } // End of loop
            
                $query->CloseCursor();
                echo '</table>';
            
            } // End of the if
        else // There are no private messages
            {
                echo '<div class="ui icon info message"><i class="frown icon"></i><div class="content"><div class="header">Oops..!</div><p>Sorry, you have no <strong>private messages</strong> at this time.</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li></ul></div></div>';
            }
        
} // End of switch

echo '</div><div class="ui secondary segment"><p><i class="circular quote right icon"></i> <span class="qoutes"></span> #<span class="author"></span></p></div></div></div>';
?>
</body>
</html>