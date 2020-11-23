<?php
session_start();
$titre="View forum";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

// Recovered the value of f
$forum = (int) $_GET['f'];

// From here, we will count the number of messages
// To show only the top 25
$query=$db->prepare('SELECT forum_name, forum_topic, auth_view, auth_topic FROM forum_forum WHERE forum_id = :forum');
$query->bindValue(':forum',$forum,PDO::PARAM_INT);
$query->execute();
$data=$query->fetch();

    if (!verif_auth($data['auth_view']))
    {
        erreur(ERR_AUTH_VIEW);
    }

    $totalDesMessages = $data['forum_topic'] + 1;
    $nombreDeMessagesParPage = $config['topic_par_page'];
    $nombreDePages = ceil($totalDesMessages / $nombreDeMessagesParPage);

    echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">',
    '<div class="ui center aligned segment"><h3 class="ui header">'.stripslashes(htmlspecialchars($data['forum_name'])).'</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><div class="active section">'.stripslashes(htmlspecialchars($data['forum_name'])).'</div></div></center><div class="ui divider"></div>';

    // Nomber of pages
    $page = (isset($_GET['page']))?intval($_GET['page']):1;

    // Pagination begins
    echo '<center><div class="ui tiny pagination menu"><a class="icon item"><i class="left arrow icon"></i></a>';
    echo get_list_page($page, $nombreDePages, './voirforum.php?f='.$forum);
    echo'<a class="icon item"><i class="right arrow icon"></i></a></div></center>';

    $premierMessageAafficher = ($page - 1) * $nombreDeMessagesParPage;


    if (verif_auth($data['auth_topic']))
    {
        // And the button to post a new topic
        echo '<a href="./poster.php?action=nouveautopic&f='.$forum.'" class="ui small primary labeled icon button"><i class="write icon"></i> Post new topic</a>';
    }
    $query->CloseCursor();


    // We take all that we have on Forum Announcements
    $add1='';
    $add2='';
    if ($id!=0) // We are connected
    {
        // First, select fields
        $add1 = 'tv_id, tv_post_id, tv_poste,'; 
        // Second, jointure
        $add2 = 'LEFT JOIN forum_topic_view ON forum_topic.topic_id = forum_topic_view.tv_topic_id AND forum_topic_view.tv_id = :id';
    }

    $query=$db->prepare('SELECT forum_topic.topic_id, topic_titre, topic_createur, topic_vu, topic_post, topic_time, topic_last_post, topic_locked, Mb.membre_pseudo AS membre_pseudo_createur, post_createur, post_time, Ma.membre_pseudo AS membre_pseudo_last_posteur, '.$add1.' post_id FROM forum_topic LEFT JOIN forum_membres Mb ON Mb.membre_id = forum_topic.topic_createur LEFT JOIN forum_post ON forum_topic.topic_last_post = forum_post.post_id LEFT JOIN forum_membres Ma ON Ma.membre_id = forum_post.post_createur '.$add2.' WHERE topic_genre = "Annonce" AND forum_topic.forum_id = :forum ORDER BY topic_last_post DESC');
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
    if ($id!=0) $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();

    // Managing the image to display
    if (!empty($id)) // If the member is logged in
    {
        if ($data['tv_id'] == $id) // If he read the topic
        {
            if ($data['tv_poste'] == '0') // If he has not posted
            {
                if ($data['tv_post_id'] == $data['topic_last_post']) // If there is no new message
                {
                    $ico_mess = '<center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center>';
                    $ico_messa = '<center><i class="big icons"><i class="big red circle icon"></i><i class="announcement inverted icon"></i></i></center>';
                }
                else
                {
                    $ico_mess = '<center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center>'; // If there is a new message
                    $ico_messa = '<center><i class="big icons"><i class="big yellow circle icon"></i><i class="announcement inverted icon"></i></i></center>';
                }
            }
            else // If he posted
            {
                if ($data['tv_post_id'] == $data['topic_last_post']) // If there is no new message
                {
                    $ico_mess = '<center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center>';
                    $ico_messa = '<center><i class="big icons"><i class="big blue circle icon"></i><i class="announcement inverted icon"></i></i></center>';
                }
                else // If there is a new message
                {
                    $ico_mess = '<center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center>';
                    $ico_messa = '<center><i class="big icons"><i class="big green circle icon"></i><i class="announcement inverted icon"></i></i></center>';
                }
            }
        }
        else // If he did not read the topic
        {
            $ico_mess = '<center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center>';
            $ico_messa = '<center><i class="big icons"><i class="big red circle icon"></i><i class="announcement inverted icon"></i></i></center>';
        }
    } // If it is not connected
    else
    {
        $ico_mess = '<center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center>';
        $ico_messa = '<center><i class="big icons"><i class="big red circle icon"></i><i class="announcement inverted icon"></i></i></center>';
    }

    // We run our table only if there are queries!
    if ($query->rowCount()>0)
    {
        echo '<table class="ui padded celled striped table">
        <thead>
            <tr>
                <th colspan="2">Announce</th>
                <th>Replies</th>
                <th>Views</th>
                <th>Author</th>
                <th class="collapsing">Last message</th>
            </tr>
        </thead>
        <tbody>';

        // Start the loop
        while ($data=$query->fetch())
        {
            // We retrieve the data for a topic locked
            $topic = $data['topic_id'];
            $lockedt = $db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id = :topic');
            $lockedt->bindValue(':topic',$topic,PDO::PARAM_INT);
            $lockedt->execute();
            $data1=$lockedt->fetch();
            // For each topic:
            // If the topic is an ad it is displayed at the top
            // Mega echo to fill everything
            echo '<tr><td class="collapsing">'.$ico_messa.'</td>
                <td id="titre">'; if ($data1['topic_locked'] == 1) { echo '<i class="small circular red lock icon"></i>'; } echo '<a href="./voirtopic.php?t='.$data['topic_id'].'" data-inverted="" data-tooltip="Topic started at '.date('H\hi \o\n d M y',$data['topic_time']).'" data-position="bottom left">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a></td>
                <td class="right aligned collapsing"><center><div class="ui circular label">'.$data['topic_post'].'</div></center></td>
                <td class="right aligned collapsing"><center><div class="ui circular label">'.$data['topic_vu'].'</div></center></td>
                <td class="right aligned collapsing"><center><a href="./voirprofil.php?m='.$data['topic_createur'].'&action=consulter" class="ui label">'.stripslashes(htmlspecialchars($data['membre_pseudo_createur'])).'</a></center></td>';

            // Selection last post
            $nombreDeMessagesParPage = 15;
            $nbr_post = $data['topic_post'] +1;
            $page = ceil($nbr_post / $nombreDeMessagesParPage);

            echo '<td class="right aligned collapsing"><center><div class="ui inverted black label">'.date('H\hi \o\n d M y',$data['post_time']).'<br><a href="./voirprofil.php?m='.$data['post_createur'].' &action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo_last_posteur'])).'</a> <a href="./voirtopic.php?t='.$data['topic_id'].'&page='.$page.'#p_'.$data['post_id'].'"><i class="arrow right icon"></i></a></div></center></td></tr>';
        }
        echo '</table>';
    }
    $query->CloseCursor();


    // We take all that we have on the normal topics of the forum
    $query=$db->prepare('SELECT forum_topic.topic_id, topic_titre, topic_createur, topic_vu, topic_post, topic_time, topic_last_post, Mb.membre_pseudo AS membre_pseudo_createur, post_id, post_createur, post_time, Ma.membre_pseudo AS membre_pseudo_last_posteur, '.$add1.' post_id FROM forum_topic LEFT JOIN forum_membres Mb ON Mb.membre_id = forum_topic.topic_createur LEFT JOIN forum_post ON forum_topic.topic_last_post = forum_post.post_id LEFT JOIN forum_membres Ma ON Ma.membre_id = forum_post.post_createur '.$add2.' WHERE topic_genre <> "Annonce" AND forum_topic.forum_id = :forum ORDER BY topic_last_post DESC LIMIT :premier ,:nombre');
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
    $query->bindValue(':premier',(int) $premierMessageAafficher,PDO::PARAM_INT);
    $query->bindValue(':nombre',(int) $nombreDeMessagesParPage,PDO::PARAM_INT);
    if ($id!=0) $query->bindValue(':id',$id,PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount()>0)
    {
        echo '<table class="ui padded celled striped table">
        <thead>
            <tr>
                <th colspan="2">Normal</th>
                <th>Replies</th>
                <th>Views</th>
                <th>Author</th>
                <th class="collapsing">Last message</th>
            </tr>
        </thead>
        <tbody>';
        
        // Start the loop
        while ($data = $query->fetch())
        {
            // We retrieve the data for a topic locked
            $topic = $data['topic_id'];
            $lockedt = $db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id = :topic');
            $lockedt->bindValue(':topic',$topic,PDO::PARAM_INT);
            $lockedt->execute();
            $data1=$lockedt->fetch();
            // Ah that here... here is the echo of crazy
            echo '<tr><td class="collapsing">'.$ico_mess.'</td>
                <td id="titre">'; if ($data1['topic_locked'] == 1) { echo '<i class="small circular red lock icon"></i>'; } echo '<a href="./voirtopic.php?t='.$data['topic_id'].'" data-inverted="" data-tooltip="Topic started at '.date('H\hi \o\n d M y',$data['topic_time']).'" data-position="bottom left">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a></td>
                <td class="right aligned collapsing"><center><div class="ui circular label">'.$data['topic_post'].'</div></center></td>
                <td class="right aligned collapsing"><center><div class="ui circular label">'.$data['topic_vu'].'</div></center></td>
                <td class="right aligned collapsing"><center><a href="./voirprofil.php?m='.$data['topic_createur'].'&action=consulter" class="ui label">'.stripslashes(htmlspecialchars($data['membre_pseudo_createur'])).'</a></center></td>';

            // Selection last post
            $nombreDeMessagesParPage = $config['post_par_page'];
            $nbr_post = $data['topic_post'] + 1;
            $page = ceil($nbr_post / $nombreDeMessagesParPage);

            echo '<td class="right aligned collapsing"><center><div class="ui inverted black label">'.date('H\hi \o\n d M y',$data['post_time']).'<br><a href="./voirprofil.php?m='.$data['post_createur'].' &action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo_last_posteur'])).'</a> <a href="./voirtopic.php?t='.$data['topic_id'].'&page='.$page.'#p_'.$data['post_id'].'"><i class="arrow right icon"></i></a></div></center></td></tr>';
        }
        echo '</table>';
    }
    else // If there is no message
    {
        echo '<div class="ui divider"></div><div class="ui icon info message"><i class="frown icon"></i><div class="content"><div class="header">Oops!</div><p>There are no topics in this forum.</p></div></div>';
    }
    $query->CloseCursor();
echo '</div>';

    // If the user is logged on
    echo '<div class="ui secondary segment">';
    if (isset($_SESSION['pseudo']))
    {
        echo '<p><i class="circular quote right icon"></i> <span class="qoutes"></span> #<span class="author"></span></p>';
    }
    else // If the user is logged off
    {
        echo '<p><i class="circular warning icon"></i> Post on the forum is only allowed for members with active accounts. Please <a href="./connexion.php">login</a> or <a href="./register.php">register</a> to post.</p>';
    }
echo '</div></div><div class="ui divider hidden"></div>';
?>
</body>
</html>