<?php
session_start();
$titre="View topic";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");
require_once("JBBCode/Parser.php");
 
// Recovey the value of t
$topic = (int) $_GET['t'];


// BBCode parser
$parser = new JBBCode\Parser();
$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
$parser->addBBCode("video", '<iframe width="560" height="315" src="https://www.youtube.com/embed/{param}" frameborder="0" allowfullscreen></iframe>');
$parser->addBBCode("center", '<center>{param}</center>');
$parser->addBBCode("list", '<div class="ui bulleted list">{param}</div>');
$parser->addBBCode("*", '<div class="item">{param}</div>');
$parser->addBBCode("quote", '<div class="ui black label"><i class="quote right icon"></i> {param}</div>');
$parser->addBBCode("code", '<div class="ui tiny basic red label">{param}</div>');

 
// From here, we will count the number of messages to display only the first 15
$query=$db->prepare('SELECT topic_titre, topic_post, topic_genre, forum_topic.forum_id, topic_last_post, forum_name, auth_view, auth_topic, auth_post FROM forum_topic LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id WHERE topic_id = :topic');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();
    if (!verif_auth($data['auth_view']))
    {
        erreur(ERR_AUTH_VIEW);
    }
    $forum=$data['forum_id']; 
    $totalDesMessages = $data['topic_post'] + 1;
    $nombreDeMessagesParPage = $config['post_par_page'];
    $nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);

    echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">';

    // If the topic is an Announce on
    if ($data['topic_genre'] == 'Annonce')
    {
        echo '<a class="ui red right corner label"><blink><i class="announcement icon"></i></blink></a>';
    }

    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./voirforum.php?f='.$forum.'" class="section">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><div class="divider"> / </div><div class="active section">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</div></div></center><div class="ui divider"></div>';

    // We retrieve the data for a topic locked
    $lockedt = $db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id = :topic');
    $lockedt->bindValue(':topic',$topic,PDO::PARAM_INT);
    $lockedt->execute();
    $data1=$lockedt->fetch();

    if ($data1['topic_locked'] == 1) // Topic locked!
    {
        echo '<div class="ui icon warning message"><i class="lock icon"></i><div class="content"><div class="header">Warning!</div><p>This topic is locked.</p></div></div><div class="ui divider"></div>';
    }
    $lockedt->CloseCursor();

    // Number of pages
    $page = (isset($_GET['page']))?intval($_GET['page']):1;

    $premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;

    if (verif_auth($data['auth_post']))
    {
        // We show the answer button
        echo '<a href="./poster.php?action=repondre&t='.$topic.'" id="repondreTopic" class="ui small '.$config['forum_color'].' labeled icon button"><i class="reply icon"></i> Reply to this topic</a>';
    }

    if (verif_auth($data['auth_topic']))
    {
        // We show the button new topic
        echo '<a href="./poster.php?action=nouveautopic&f='.$data['forum_id'].'" id="nouveauTopic" class="ui small primary labeled icon button"><i class="write icon"></i> Post new topic</a><br/><br/>';
    }
    $query->CloseCursor();

    // Finally we start the loop!
    $query=$db->prepare('SELECT post_id, post_createur, post_texte, post_time, membre_id, membre_pseudo, membre_inscrit, membre_avatar, membre_localisation, membre_rang, membre_post, membre_btag, membre_signature FROM forum_post LEFT JOIN forum_membres ON forum_membres.membre_id = forum_post.post_createur WHERE topic_id =:topic ORDER BY post_id LIMIT :premier, :nombre');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->bindValue(':premier',(int) $premierMessageAafficher,PDO::PARAM_INT);
    $query->bindValue(':nombre',(int) $nombreDeMessagesParPage,PDO::PARAM_INT);
    $query->execute();
 
    // We check that the request has returned messages
    if ($query->rowCount() < 1)
    {
        echo '<div class="ui icon info message"><i class="meh icon"></i><div class="content"><div class="header">Meh !</div><p>There is no post on this topic, check the url and try again!</p></div></div>';
    }
    else
    {
        // If all goes well we display our table then we fill with a loop
        echo '<table class="ui very basic padded celled table">
        <thead>
            <tr>
                <th>Author</th>
                <th>Content</th>
            </tr>
        </thead>
        <tbody>';

        while ($data = $query->fetch())
        {
            // We start to display the nickname of the creator of the message:
            // We check the rights of Member
            
            // Information about the person's rank
            $rang = array
                (0 => "<div class='ui red label'>Banned</div>",
                1 => "<div class='ui label'>Visitor</div>", 
                2 => "<div class='ui blue label'>Member</div>", 
                3 => "<div class='ui green label'>Modo</div>", 
                4 => "<div class='ui violet label'>Admin</div>"); // This table associates role number and name
            for($i=0;$i<5;$i++)
                
                if ($i == $data['membre_rang'])
                    
                    // Details on the member who posted
                    echo '<tr><td class="collapsing"><span data-tooltip="Messages: '.$data['membre_post'].' / Joined: '.date('d/m/Y',$data['membre_inscrit']).'" data-position="left center"><img class="ui centered tiny circular image" src="./images/avatars/'.$data['membre_avatar'].'"></span><br><center>'.$rang[$i].'</center></td>';
           
            /* If you are the author of the message, you will see links to Moderate it.
            Moderators can also do it! */
            
            if ($id == $data['post_createur'] OR verif_auth(MODO))
            {
                echo '<td id="p_'.$data['post_id'].'"><h5 class="ui left floated header"><small><i class="clock icon"></i> Posted at '.date('H\hi \o\n d M y',$data['post_time']).'</small> <a href="./poster.php?p='.$data['post_id'].'&action=delete" id="supprimerM" class="ui tiny red label">Remove</a><a href="./poster.php?p='.$data['post_id'].'&action=edit" id="editM" class="ui tiny '.$config['forum_color'].' label">Edit</a></h5><h5 class="ui right floated header"><small>by <a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter" id="voirProfil" data-tooltip="Messages: '.$data['membre_post'].' / Joined: '.date('d/m/Y',$data['membre_inscrit']).'" data-position="right center">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></small></h5><div class="ui divider clearing"></div>';
            }
            else
            {
                echo '<td id="p_'.$data['post_id'].'"><h5 class="ui left floated header"><small><i class="clock icon"></i> Posted at '.date('H\hi \o\n d M y',$data['post_time']).'</small></h5><h5 class="ui right floated header"><small>by <a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter" id="voirProfil" data-tooltip="Messages: '.$data['membre_post'].' / Joined: '.date('d/m/Y',$data['membre_inscrit']).'" data-position="right center">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></small></h5><div class="ui divider clearing"></div>';
            }
            
            // Message with BBCode
            echo '<p>';
            $texte_contenu = nl2br(stripslashes(htmlspecialchars($data['post_texte'])));
            $texte_signature = nl2br(stripslashes(htmlspecialchars($data['membre_signature'])));
            
            $parser->parse($texte_contenu, $texte_signature);
            echo $parser->getAsHtml();
            echo '</p><div style="border-top: 1px dashed #EBEEF2;" class="ui divider"></div><small><h5>'.$texte_signature.'</h5></small></td></tr>';
        } // End of the loop! \o/
        $query->CloseCursor();

        echo '</tbody></table>';
        echo '<div class="ui divider"></div>';

        // Pagination begins
        echo '<center><div class="ui tiny pagination menu"><a class="icon item"><i class="left arrow icon"></i></a>';
        echo get_list_page($page, $nombreDePages, './voirtopic.php?t='.$topic);
        echo'<a class="icon item"><i class="right arrow icon"></i></a></div></center>';

        // Add 1 to the number of visits to this topic
        $query=$db->prepare('UPDATE forum_topic SET topic_vu = topic_vu + 1 WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

    } // End of if if that checked the topic contained at least one message

    // We retrieve the data to unlock / Unlock a topic
    $query = $db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id = :topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();

    if (verif_auth(MODO))
    {
        if ($data['topic_locked'] == 1) // Topic locked!
        {
            echo '<div class="ui divider hidden"></div><center><a href="./postok.php?action=unlock&t='.$topic.'" class="ui orange labeled icon button" id="unlockSujet"><i class="unlock icon"></i> Unlock this topic</a></center>';
        }
        else // Otherwise the topic is unlocked!
        {
            echo '<div class="ui divider hidden"></div><center><a href="./postok.php?action=lock&t='.$topic.'" class="ui orange labeled icon button" id="lockSujet"><i class="lock icon"></i> Lock this topic</a></center>';
        }
        $query->CloseCursor();
        
        // We retrieve the data from the forums
        $query=$db->prepare('SELECT forum_id, forum_name FROM forum_forum WHERE forum_id <> :forum');
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);
        $query->execute();

        // $forum has been defined at the top of the page!
        echo '<div class="ui divider"></div>';
        echo '<div class="ui form"><div class="two fields">
        <div class="field">
            <label for="dest">Move to</label>
            <form method="post" action=postok.php?action=deplacer&t='.$topic.'>
                <select name="dest" id="dest" class="ui fluid dropdown">';               
        while($data=$query->fetch())
        {
            echo '<option value="'.$data['forum_id'].'" id="'.$data['forum_id'].'">'.$data['forum_name'].'</option>';
        }
        $query->CloseCursor();
        echo '</select>
        <div class="ui fitted divider"></div>
            <input type="hidden" name="from" value='.$forum.'>
            <input type="submit" name="submit" class="ui fluid inverted violet button" value="Envoyer" />
        </form></div>';
        
        // The recording of the fast response (automatic)
        echo '<div class="field">
        <label for="rep">Auto reply with auto-lock</label>
        <form method="post" action=postok.php?action=autorep&t='.$topic.'>
            <select name="rep" id="rep" class="ui fluid dropdown">';
        $query=$db->query('SELECT automess_id, automess_titre FROM forum_automess');
        while ($data = $query->fetch())
        {
            echo '<option value="'.$data['automess_id'].'">'.$data['automess_titre'].'</option>';
        }
        echo '</select>
        <div class="ui fitted divider"></div>
            <input type="submit" name="submit" class="ui fluid inverted violet button" value="Envoyer" />
        </form></div></div></div>';
        $query->CloseCursor();
        echo "<script>
        $('.ui.dropdown')
            .dropdown()
        ;
        </script>";
    } // End of if for moderation

    if (isset($_SESSION['pseudo']))
    {
        echo '</div>';
        echo '<div class="ui secondary segment"><p><i class="circular quote right icon"></i> <span class="qoutes"></span> #<span class="author"></span></p></div>';
    }
    else
    {
        echo '</div>';
        echo '<div class="ui secondary segment"><p><i class="circular warning icon"></i> Post on the forum is only allowed for members with active accounts. Please <a href="./connexion.php">login</a> or <a href="./register.php">register</a> to post.</p></div>';
    }

echo '</div>';
echo '<div class="ui divider hidden"></div>';

if (isset($_SESSION['pseudo'])) {
// Topic already viewed?
$query=$db->prepare('SELECT COUNT(*) FROM forum_topic_view WHERE tv_topic_id = :topic AND tv_id = :id');
$query->bindValue(':topic',$topic,PDO::PARAM_INT);
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->execute();
$nbr_vu=$query->fetchColumn();
$query->CloseCursor();
    if ($nbr_vu == 0) // If this is the first time you insert a whole line
    {
        $query=$db->prepare('INSERT INTO forum_topic_view (tv_id, tv_topic_id, tv_forum_id, tv_post_id) VALUES (:id, :topic, :forum, :last_post)');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->bindValue(':forum',$forum,PDO::PARAM_INT);
        $query->bindValue(':last_post',$data['topic_last_post'],PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();
    }
    else // Otherwise, it simply updates
    {
        $query=$db->prepare('UPDATE forum_topic_view SET tv_post_id = :last_post WHERE tv_topic_id = :topic AND tv_id = :id');
        $query->bindValue(':last_post',$data['topic_last_post'],PDO::PARAM_INT);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();
    }
}
?>
</body>
</html>