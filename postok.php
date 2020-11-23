<?php
session_start();
$titre="Post";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");
// We retrieve the value of the action variable
$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

// If the member is not logged in, he got here by mistake
if ($id==0) erreur(ERR_IS_NOT_CO);

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">';

switch($action)
    {
        // First case: new topic
        case "nouveautopic":
        if (!verif_auth($data['auth_annonce']) && isset($_POST['mess']))
            {
            exit('</div></body></html>');
            }
        // The message is passed in a series of functions
        $message = htmlspecialchars($_POST['message']);
        $message = utf8_encode($message);
        $message = str_replace('ï»¿', '', $message);
        $message = utf8_decode($message);
        $mess = htmlspecialchars($_POST['mess']);

        // Same for the title
        $titre = htmlspecialchars($_POST['titre']);
        $titre = utf8_encode($titre);
        $titre = str_replace('ï»¿', '', $titre);
        $titre = utf8_decode($titre);

        // Here only, now that it is on it exists, one recovers the value of the variable f
        $forum = (int) $_GET['f'];
        $temps = time();

        if (empty($message) || empty($titre))
            {
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Be careful</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon warning message"><i class="warning icon"></i><div class="content"><div class="header">Warning!</div><p>Your <strong>message</strong> or your <strong>title</strong> is empty!</p><ul class="list"><li><a href="./poster.php?action=nouveautopic&f='.$forum.'">Click here to start over.</a></li></ul></div></div>';
            }
        else // If the message is not empty
            {
            // We enter the topic in the database leaving the topic_last_post field at 0
            $query=$db->prepare('INSERT INTO forum_topic (forum_id, topic_titre, topic_createur, topic_vu, topic_time, topic_genre) VALUES(:forum, :titre, :id, 1, :temps, :mess)');
            $query->bindValue(':forum', $forum, PDO::PARAM_INT);
            $query->bindValue(':titre', $titre, PDO::PARAM_STR);
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':temps', $temps, PDO::PARAM_INT);
            $query->bindValue(':mess', $mess, PDO::PARAM_STR);
            $query->execute();

            $nouveautopic = $db->lastInsertId(); // Our famous function!
            $query->CloseCursor();

            // Then you enter the message
            $query=$db->prepare('INSERT INTO forum_post (post_createur, post_texte, post_time, topic_id, post_forum_id) VALUES (:id, :mess, :temps, :nouveautopic, :forum)');
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':mess', $message, PDO::PARAM_STR);
            $query->bindValue(':temps', $temps,PDO::PARAM_INT);
            $query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
            $query->bindValue(':forum', $forum, PDO::PARAM_INT);
            $query->execute();

            $nouveaupost = $db->lastInsertId(); // Still our famous function!
            $query->CloseCursor();

            // Here we update as expected the value of topic_last_post and topic_first_post
            $query=$db->prepare('UPDATE forum_topic SET topic_last_post = :nouveaupost, topic_first_post = :nouveaupost WHERE topic_id = :nouveautopic');
            $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);
            $query->bindValue(':nouveautopic', (int) $nouveautopic, PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();

            // Finally we update the tables forum_forum and forum_members
            $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 ,forum_topic = forum_topic + 1, forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
            $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);
            $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
    
            $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
        
            // Add a line in the forum_topic_view table
            $query=$db->prepare('INSERT INTO forum_topic_view (tv_id, tv_topic_id, tv_forum_id, tv_post_id, tv_poste) VALUES(:id, :topic, :forum, :post, :poste)');
            $query->bindValue(':id',$id,PDO::PARAM_INT);
            $query->bindValue(':topic',$nouveautopic,PDO::PARAM_INT);
            $query->bindValue(':forum',$forum ,PDO::PARAM_INT);
            $query->bindValue(':post',$nouveaupost,PDO::PARAM_INT);
            $query->bindValue(':poste','1',PDO::PARAM_STR);
            $query->execute();
            $query->CloseCursor();

            // And a little message
            echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your new topic</h3></div>',
            '<div class="ui '.$config['forum_color'].' padded segment">
            <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>Your <strong>new topic</strong> has been added!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$nouveautopic.'">Click here to see your new topic.</a></li></ul></div></div>';
            }
        break; // Houra!
        
        
        // Case 2: Answer
        case "repondre":
        $message = htmlspecialchars($_POST['message']);
        $message = utf8_encode($message);
        $message = str_replace('ï»¿', '', $message);
        $message = utf8_decode($message);

        // Here only, now that it is on it exists, we recover the value of the variable t
        $topic = (int) $_GET['t'];
        $query=$db->prepare('SELECT topic_locked FROM forum_topic WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        if ($data['topic_locked'] != 0)
            {
                erreur(ERR_TOPIC_VERR); // We display our constant
            }
        $query->CloseCursor();
        $temps = time();

        if (empty($message))
            {
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Be careful</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon warning message"><i class="warning icon"></i><div class="content"><div class="header">Warning!</div><p>Your <strong>message</strong> is empty!</p><ul class="list"><li><a href="./poster.php?action=repondre&t='.$topic.'">Click here to start over.</a></li></ul></div></div>';
            }
        else // Otherwise, if the message is not empty
            {
            // We retrieve the id of the forum
            $query=$db->prepare('SELECT forum_id, topic_post FROM forum_topic WHERE topic_id = :topic');
            $query->bindValue(':topic', $topic, PDO::PARAM_INT);
            $query->execute();
            $data=$query->fetch();
            $forum = $data['forum_id'];

            // Then you enter the message
            $query=$db->prepare('INSERT INTO forum_post (post_createur, post_texte, post_time, topic_id, post_forum_id) VALUES(:id,:mess,:temps,:topic,:forum)');
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->bindValue(':mess', $message, PDO::PARAM_STR);
            $query->bindValue(':temps', $temps, PDO::PARAM_INT);
            $query->bindValue(':topic', $topic, PDO::PARAM_INT);
            $query->bindValue(':forum', $forum, PDO::PARAM_INT);
            $query->execute();

            $nouveaupost = $db->lastInsertId();
            $query->CloseCursor();

            // We change the table a little forum_topic
            $query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post + 1, topic_last_post = :nouveaupost WHERE topic_id =:topic');
            $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);
            $query->bindValue(':topic', (int) $topic, PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();

            // Then even fight on the other 2 tables
            $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1 , forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
            $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);
            $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();

            $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
            $query->bindValue(':id', $id, PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();

            // And a little message
            $nombreDeMessagesParPage = $config['post_par_page'];
            $nbr_post = $data['topic_post'] + 1;
            $page = ceil($nbr_post / $nombreDeMessagesParPage);
            // We update the table forum_topic_view
            $query=$db->prepare('UPDATE forum_topic_view SET tv_post_id = :post, tv_poste = :poste WHERE tv_id = :id AND tv_topic_id = :topic');
            $query->bindValue(':post',$nouveaupost,PDO::PARAM_INT);
            $query->bindValue(':poste','1',PDO::PARAM_STR);
            $query->bindValue(':id',$id,PDO::PARAM_INT);
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();
            
            echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your answer</h3></div>',
            '<div class="ui '.$config['forum_color'].' padded segment">
            <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>Your <strong>reply</strong> has been successfully added!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'&page='.$page.'#p_'.$nouveaupost.'">Click here to see your reply.</a></li></ul></div></div>';
            }// End of else
        break;
        
        
        case "edit": // If you want to edit the post
        // We retrieve the value of p
        $post = (int) $_GET['p'];
 
        // We retrieve the message
        $message = htmlspecialchars($_POST['message']);
        $message = utf8_encode($message);
        $message = str_replace('ï»¿', '', $message);
        $message = utf8_decode($message);

        // Then we check that the member has the right to be here (either the creator or a mod/admin)
        $query=$db->prepare('SELECT post_createur, post_texte, post_time, topic_id, auth_modo FROM forum_post LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id WHERE post_id=:post');
        $query->bindValue(':post',$post,PDO::PARAM_INT);
        $query->execute();
        $data1 = $query->fetch();
        $topic = $data1['topic_id'];

        // We retrieve the place of the message in the topic (for the link)
        $query = $db->prepare('SELECT COUNT(*) AS nbr FROM forum_post WHERE topic_id = :topic AND post_time < '.$data1['post_time']);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data2=$query->fetch();

        if (!verif_auth($data1['auth_modo']) && $data1['post_createur'] != $id)
            {
                // If this condition is not fulfilled it will barder
                erreur(ERR_AUTH_EDIT);
            }
        else // Otherwise it rolls and continues
            {
            $query=$db->prepare('UPDATE forum_post SET post_texte = :message WHERE post_id = :post');
            $query->bindValue(':message',$message,PDO::PARAM_STR);
            $query->bindValue(':post',$post,PDO::PARAM_INT);
            $query->execute();
            $nombreDeMessagesParPage = $config['post_par_page'];
            $nbr_post = $data2['nbr'] + 1;
            $page = ceil($nbr_post / $nombreDeMessagesParPage);
            
            echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your change</h3></div>',
            '<div class="ui '.$config['forum_color'].' padded segment">
            <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>Your <strong>message</strong> has been successfully edited!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'&page='.$page.'#p_'.$post.'">Click here to see your post edited.</a></li></ul></div></div>';
            
            $query->CloseCursor();
            }
        break;
        
        
        case "delete": // If you want to delete the post
        // We retrieve the value of p
        $post = (int) $_GET['p'];
        $query=$db->prepare('SELECT post_createur, post_texte, forum_id, topic_id, auth_modo FROM forum_post LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id WHERE post_id=:post');
        $query->bindValue(':post',$post,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        $topic = $data['topic_id'];
        $forum = $data['forum_id'];
        $poster = $data['post_createur'];

        // Then we check that the member has the right to be here (either the creator or a mod/admin)
        if (!verif_auth($data['auth_modo']) && $poster != $id)
            {
                // If this condition is not fulfilled it will bard
                erreur(ERR_AUTH_DELETE);
            }
        else // Otherwise it rolls and continues
        {
            // Here we check several things: is this a first post? Latest Post or Classic Post?
            $query = $db->prepare('SELECT topic_first_post, topic_last_post FROM forum_topic WHERE topic_id = :topic');
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $data_post=$query->fetch();
            
            // A distinction is now made between
            if ($data_post['topic_first_post'] == $post) // If the message is the first
                {
                    // Permissions have changed!
                    // Normal, only a modo can decide to delete a whole topic
                    if (!verif_auth($data['auth_modo']))
                        {
                            erreur(ERR_AUTH_DELETE_TOPIC);
                        }
                    // Make sure that this is not a mistake
                    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Be careful</h3></div>',
                    '<div class="ui '.$config['forum_color'].' padded segment">
                    <h1 class="ui center aligned icon header"><blink><i class="circular orange warning sign icon"></i> Warning!</blink></h1><div class="ui divider"></div><div class="ui icon warning message"><i class="warning sign icon"></i><div class="content"><div class="header">Really?</div><p>You have chosen to delete a <strong>post</strong>. However this post is the <strong>first of the topic</strong>. Do you want to <strong>delete the topic</strong>?</p></div></div><div class="two ui buttons"><a href="./postok.php?action=delete_topic&t='.$topic.'" class="ui positive button">Yes I am sure</a><a href="./voirtopic.php?t='.$topic.'" class="ui negative button">No I am not sure</a></div>';
                $query->CloseCursor();                     
                }
            elseif ($data_post['topic_last_post'] == $post)  // If the message is the last
                {
                    // We delete the post
                    $query=$db->prepare('DELETE FROM forum_post WHERE post_id = :post');
                    $query->bindValue(':post',$post,PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();

                    // We modify the value of topic_last_post for this we retrieve the id of the most recent message of this topic
                    $query=$db->prepare('SELECT post_id FROM forum_post WHERE topic_id = :topic ORDER BY post_id DESC LIMIT 0,1');
                    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                    $query->execute();
                    $data=$query->fetch();
                    $last_post_topic=$data['post_id'];
                    $query->CloseCursor();

                    // We do the same for forum_last_post_id
                    $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :forum ORDER BY post_id DESC LIMIT 0,1');
                    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
                    $query->execute();
                    $data=$query->fetch();
                    $last_post_forum=$data['post_id'];
                    $query->CloseCursor();

                    // We update the value of topic_last_post
                    $query=$db->prepare('UPDATE forum_topic SET topic_last_post = :last WHERE topic_last_post = :post');
                    $query->bindValue(':last',$last_post_topic,PDO::PARAM_INT);
                    $query->bindValue(':post',$post,PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();
 
                    // We remove 1 to the number of forum posts and we update forum_last_post
                    $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - 1, forum_last_post_id = :last WHERE forum_id = :forum');
                    $query->bindValue(':last',$last_post_forum,PDO::PARAM_INT);
                    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();
                    
                    // We remove 1 to the number of topics
                    $query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post - 1 WHERE topic_id = :topic');
                    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();
                    
                    // We remove 1 to the messages of this member
                    $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post - 1 WHERE membre_id = :id');
                    $query->bindValue(':id',$poster,PDO::PARAM_INT);
                    $query->execute();
                    $query->CloseCursor();
                    
                    // Finally, the message
                    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your choice</h3></div>',
                    '<div class="ui '.$config['forum_color'].' padded segment">
                    <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>message</strong> has been deleted!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'">Click here to return to the topic.</a></li></ul></div></div>';
                }
            else // If this is a classic post
            {
                // We delete the post
                $query=$db->prepare('DELETE FROM forum_post WHERE post_id = :post');
                $query->bindValue(':post',$post,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                // We remove 1 to the number of forum posts
                $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - 1 WHERE forum_id = :forum');
                $query->bindValue(':forum',$forum,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                // We remove 1 to the number of topics
                $query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post - 1 WHERE topic_id = :topic');
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                // We remove 1 to the messages of this member
                $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post - 1 WHERE membre_id = :id');
                $query->bindValue(':id',$data['post_createur'],PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                // Finally, the message
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your choice</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>message</strong> has been deleted!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'">Click here to return to the topic.</a></li></ul></div></div>';
            }
        } // End of else
        break;
        
        
        case "delete_topic":
        $topic = (int) $_GET['t'];
        $query=$db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic LEFT JOIN forum_forum ON forum_topic.forum_id = forum_forum.forum_id WHERE topic_id=:topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        $forum = $data['forum_id'];
        
        // Then we check that the member has the right to be here that is to say if it is a mod/admin
        if (!verif_auth($data['auth_modo']))
            {
                erreur(ERR_AUTH_DELETE_TOPIC);
            }
        else // Otherwise it rolls and continues
            {
            $query->CloseCursor();
                // We count the post number of the topic
                $query=$db->prepare('SELECT topic_post FROM forum_topic WHERE topic_id = :topic');
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $data = $query->fetch();
                $nombrepost = $data['topic_post'] + 1;
                $query->CloseCursor();

                // We delete the topic
                $query=$db->prepare('DELETE FROM forum_topic WHERE topic_id = :topic');
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();

                // We remove the number of posts posted by each member in the topic
                $query=$db->prepare('SELECT post_createur, COUNT(*) AS nombre_mess FROM forum_post WHERE topic_id = :topic GROUP BY post_createur');
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();

                while($data = $query->fetch())
                    {
                        $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post - :mess WHERE membre_id = :id');
                        $query->bindValue(':mess',$data['nombre_mess'],PDO::PARAM_INT);
                        $query->bindValue(':id',$data['post_createur'],PDO::PARAM_INT);
                        $query->execute();
                    }
                    $query->CloseCursor();
            
                // And we delete posts!
                $query=$db->prepare('DELETE FROM forum_post WHERE topic_id = :topic');
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();

                // Last thing, we retrieve the last post of the forum
                $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :forum ORDER BY post_id DESC LIMIT 0,1');
                $query->bindValue(':forum',$forum,PDO::PARAM_INT);
                $query->execute();
                $data = $query->fetch();
 
                // Then we modify certain values:
                $query=$db->prepare('UPDATE forum_forum SET forum_topic = forum_topic - 1, forum_post = forum_post - :nbr, forum_last_post_id = :id WHERE forum_id = :forum');
                $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
                $query->bindValue(':id',$data['post_id'],PDO::PARAM_INT);
                $query->bindValue(':forum',$forum,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();

                // Finally, the message
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your choice</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>topic</strong> and its <strong>last message</strong> have been deleted!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li></ul></div></div>';

            } // End of else
        break;
        
        
        case "lock": // If you want to lock the topic
        // We retrieve the value of t
        $topic = (int) $_GET['t'];
        
        $query = $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();

        // Then we check that the member has the right to be here
        if (!verif_auth($data['auth_modo']))
            {
                // If this condition is not fulfilled it will bard
                erreur(ERR_AUTH_VERR);
            }
        else // Otherwise it rolls and continues
            {
            // We update the value of topic_locked
            $query->CloseCursor();
            $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock WHERE topic_id = :topic');
            $query->bindValue(':lock',1,PDO::PARAM_STR);
            $query->bindValue(':topic',$topic,PDO::PARAM_INT);
            $query->execute();
            $query->CloseCursor();

            echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your edition</h3></div>',
            '<div class="ui '.$config['forum_color'].' padded segment">
            <div class="ui icon positive message"><i class="lock icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>topic</strong> has been locked!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'">Click here to return to the topic.</a></li></ul></div></div>';
            }
        break;
        
        
        case "unlock": // If you want to unlock the topic
        // We retrieve the value of t
        $topic = (int) $_GET['t'];
        
        $query = $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        
        // Then we check that the member has the right to be here
        if (!verif_auth($data['auth_modo']))
            {
                // If this condition is not fulfilled it will bard
                erreur(ERR_AUTH_VERR);
            }
        else // Otherwise it rolls and continues
            {
                // We update the value of topic_locked
                $query->CloseCursor();
                $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock WHERE topic_id = :topic');
                $query->bindValue(':lock',0,PDO::PARAM_STR);
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your edition</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon positive message"><i class="unlock icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>topic</strong> has been unlocked!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'">Click here to return to the topic.</a></li></ul></div></div>';
            }
        break;
        
        
        case "deplacer": // If you want to move the topic
        // We retrieve the value of t
        $topic = (int) $_GET['t'];
        
        $query= $db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        
        // Then we check that the member has the right to be here
        if (!verif_auth($data['auth_modo']))
            {
                // If this condition is not fulfilled it will bard
                erreur(ERR_AUTH_MOVE);
            }
        else // Otherwise it rolls and continues
            {
                $query->CloseCursor();
                $destination = (int) $_POST['dest'];
                $origine = (int) $_POST['from'];
                
                // We move the topic
                $query=$db->prepare('UPDATE forum_topic SET forum_id = :dest WHERE topic_id = :topic');
                $query->bindValue(':dest',$destination,PDO::PARAM_INT);
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
 
                // Moving posts
                $query=$db->prepare('UPDATE forum_post SET post_forum_id = :dest WHERE topic_id = :topic');
                $query->bindValue(':dest',$destination,PDO::PARAM_INT);
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();
                
                // We take care of adding / removing post / topic numbers to the origin and destination forums
                // For this we count the number of post moved
                $query=$db->prepare('SELECT COUNT(*) AS nombre_post FROM forum_post WHERE topic_id = :topic');
                $query->bindValue(':topic',$topic,PDO::PARAM_INT);
                $query->execute();
                $data = $query->fetch();
                $nombrepost = $data['nombre_post'];
                $query->CloseCursor();
                
                // We also have to check that we have not moved a post that was the oldest post in the forum (forum_last_post_id field)
                $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :ori ORDER BY post_id DESC LIMIT 0,1');
                $query->bindValue(':ori',$origine,PDO::PARAM_INT);
                $query->execute();
                $data=$query->fetch();
                $last_post=$data['post_id'];
                $query->CloseCursor();
                
                // Then we update the forum of origin
                $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post - :nbr, forum_topic = forum_topic - 1, forum_last_post_id = :id WHERE forum_id = :ori');
                $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
                $query->bindValue(':ori',$origine,PDO::PARAM_INT);
                $query->bindValue(':id',$last_post,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();

                // Before updating the destination forum, check the value of forum_last_post_id
                $query=$db->prepare('SELECT post_id FROM forum_post WHERE post_forum_id = :dest ORDER BY post_id DESC LIMIT 0,1');
                $query->bindValue(':dest',$destination,PDO::PARAM_INT);
                $query->execute();
                $data=$query->fetch();
                $last_post=$data['post_id'];
                $query->CloseCursor();

                // And we finally update!
                $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + :nbr, forum_topic = forum_topic + 1, forum_last_post_id = :last WHERE forum_id = :forum');
                $query->bindValue(':nbr',$nombrepost,PDO::PARAM_INT);
                $query->bindValue(':last',$last_post,PDO::PARAM_INT);
                $query->bindValue(':forum',$destination,PDO::PARAM_INT);
                $query->execute();
                $query->CloseCursor();

                // It's won! The message
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your edition</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>topic</strong> has been moved!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'">Click here to return to the topic.</a></li></ul></div></div>';
            }
        break;
        
        
        case "autorep": // If we want to use our quick response (auto)
        // We retrieve the value of t
        $topic = (int) $_GET['t'];
        
        $query=$db->prepare('SELECT forum_topic.forum_id, auth_modo FROM forum_topic LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id WHERE topic_id = :topic');
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();
        $forum=$data['forum_id'];
        
        // Then we verify that the member has the right to do this
        if (!verif_auth($data['auth_modo']))
            {
                erreur(ERR_AUTH_MODO);
            }
            $query->CloseCursor();
        
        $rep = (int) $_POST['rep'];
        $query=$db->prepare('SELECT automess_mess FROM forum_automess WHERE automess_id = :rep');
        $query->bindValue(':rep',$rep,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
        $message = $data['automess_mess'];
        $query->CloseCursor();

        $query=$db->prepare('INSERT INTO forum_post (post_createur, post_texte, post_time, topic_id, post_forum_id) VALUES(:id,:mess,:temps,:topic,:forum)');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->bindValue(':mess', $message, PDO::PARAM_STR);
        $query->bindValue(':temps', time(), PDO::PARAM_INT);
        $query->bindValue(':topic', $topic, PDO::PARAM_INT);
        $query->bindValue(':forum', $forum, PDO::PARAM_INT);
        $query->execute();

        $nouveaupost = $db->lastInsertId();
        $query->CloseCursor();

        // We change the table a little forum_topic
        $query=$db->prepare('UPDATE forum_topic SET topic_post = topic_post + 1, topic_last_post = :nouveaupost WHERE topic_id =:topic');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);
        $query->bindValue(':topic', (int) $topic, PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        // Then even fight on the other 2 tables
        $query=$db->prepare('UPDATE forum_forum SET forum_post = forum_post + 1, forum_last_post_id = :nouveaupost WHERE forum_id = :forum');
        $query->bindValue(':nouveaupost', (int) $nouveaupost, PDO::PARAM_INT);
        $query->bindValue(':forum', (int) $forum, PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        $query=$db->prepare('UPDATE forum_membres SET membre_post = membre_post + 1 WHERE membre_id = :id');
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        $query=$db->prepare('UPDATE forum_topic SET topic_locked = :lock WHERE topic_id = :topic');
        $query->bindValue(':lock','1',PDO::PARAM_STR);
        $query->bindValue(':topic',$topic,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();

        // Finally, the message
        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your edition</h3></div>',
        '<div class="ui '.$config['forum_color'].' padded segment">
        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>The <strong>automatic reply</strong> has been sent! <strong>And the topic has been locked</strong>.</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./voirtopic.php?t='.$topic.'">Click here to return to the topic.</a></li></ul></div></div>';
        
        break;
        
        
        case "repondremp": // If you want to reply to the private message
        // We retrieve the message and the title
        $message = htmlspecialchars($_POST['message']);
        $message = utf8_encode($message);
        $message = str_replace('ï»¿', '', $message);
        $message = utf8_decode($message);
        $titre = htmlspecialchars($_POST['titre']);
        $titre = utf8_encode($titre);
        $titre = str_replace('ï»¿', '', $titre);
        $titre = utf8_decode($titre);
        $temps = time();

        // The value of the recipient's id is retrieved
        $dest = (int) $_GET['dest'];

        // Finally we can send the message
        $query=$db->prepare('INSERT INTO forum_mp (mp_expediteur, mp_receveur, mp_titre, mp_text, mp_time, mp_lu) VALUES(:id, :dest, :titre, :txt, :tps, "0")');
        $query->bindValue(':id',$id,PDO::PARAM_INT);
        $query->bindValue(':dest',$dest,PDO::PARAM_INT);
        $query->bindValue(':titre',$titre,PDO::PARAM_STR);
        $query->bindValue(':txt',$message,PDO::PARAM_STR);
        $query->bindValue(':tps',$temps,PDO::PARAM_INT);
        $query->execute();
        $query->CloseCursor();
        
        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your private message</h3></div>',
        '<div class="ui '.$config['forum_color'].' padded segment">
        <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>Your <strong>private message</strong> has been successfully sent!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./messagesprives.php">Click here to return to messaging.</a></li></ul></div></div>';

        break;
        
        
        case "nouveaump": // We send a new private message
        // We retrieve the message and the title
        $message = htmlspecialchars($_POST['message']);
        $message = utf8_encode($message);
        $message = str_replace('ï»¿', '', $message);
        $message = utf8_decode($message);
        $titre = htmlspecialchars($_POST['titre']);
        $titre = utf8_encode($titre);
        $titre = str_replace('ï»¿', '', $titre);
        $titre = utf8_decode($titre);
        $temps = time();
        $dest = htmlspecialchars($_POST['to']);

        // We retrieve the value of the recipient's id, we must already verify the name
        $query=$db->prepare('SELECT membre_id FROM forum_membres WHERE LOWER(membre_pseudo) = :dest');
        $query->bindValue(':dest',$dest,PDO::PARAM_STR);
        $query->execute();
        if($data = $query->fetch())
            {
                $query=$db->prepare('INSERT INTO forum_mp (mp_expediteur, mp_receveur, mp_titre, mp_text, mp_time, mp_lu) VALUES (:id, :dest, :titre, :txt, :tps, :lu)');
                $query->bindValue(':id',$id,PDO::PARAM_INT);
                $query->bindValue(':dest',(int) $data['membre_id'],PDO::PARAM_INT);
                $query->bindValue(':titre',$titre,PDO::PARAM_STR);
                $query->bindValue(':txt',$message,PDO::PARAM_STR);
                $query->bindValue(':tps',$temps,PDO::PARAM_INT);
                $query->bindValue(':lu','0',PDO::PARAM_STR);
                $query->execute();
                $query->CloseCursor();

                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your private message</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon positive message"><i class="checkmark icon"></i><div class="content"><div class="header">Congratulations!</div><p>Your <strong>private message</strong> has been successfully sent!</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./messagesprives.php">Click here to return to messaging.</a></li></ul></div></div>';
            }
        // Otherwise the user does not exist!
        else
            {
                echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">About your private message</h3></div>',
                '<div class="ui '.$config['forum_color'].' padded segment">
                <div class="ui icon warning message"><i class="meh icon"></i><div class="content"><div class="header">Oops..!</div><p>Sorry this <strong>member does not exist</strong>, please check and try again. (He may have been captured by aliens)</p><ul class="list"><li><a href="./index.php">Click here to go back to the forum homepage.</a></li><li><a href="./messagesprives.php">Click here to return to messaging.</a></li></ul></div></div>';
            }
        break;
        
        
        default;
        // A default message is displayed if this is not possible
        echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Be careful</h3></div>',
        '<div class="ui '.$config['forum_color'].' padded segment">
        <h1 class="ui center aligned icon header"><i class="circular red remove icon"></i> Impossible!</h1><div class="ui divider"></div><div class="ui icon negative message"><i class="warning sign icon"></i><div class="content"><div class="header">Warning!</div><p>This <strong>action</strong> is impossible!</p></div></div><a href="./index.php" class="ui positive fluid button">Click here to go back to the forum homepage</a>';
    } // End of Switch

echo '</div>';
echo '<div class="ui secondary segment"><p><i class="circular quote right icon"></i> <span class="qoutes"></span> #<span class="author"></span></p></div>';
?>
</body>
</html>