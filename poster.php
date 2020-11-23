<?php
session_start();
$titre="Post";
$balises = true;
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");
include("includes/bbcode.php");

$action = (isset($_GET['action']))?htmlspecialchars($_GET['action']):'';

// You must be logged in to post!
if ($id==0) erreur(ERR_IS_NOT_CO);

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">';

// If we want to post a new topic, the variable f is in the url,
// Some values are recovered
if (isset($_GET['f']))
{
    $forum = (int) $_GET['f'];
    $query= $db->prepare('SELECT forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_forum WHERE forum_id =:forum');
    $query->bindValue(':forum',$forum,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    
    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">New topic</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./voirforum.php?f='.$forum.'" class="section">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><div class="divider"> / </div><div class="active section">New topic</div></div></center><div class="ui divider hidden"></div>';
}
 
// Otherwise it is a new message, we have the variable t and f
// We retrieve f by means of a query
elseif (isset($_GET['t']))
{
    $topic = (int) $_GET['t'];
    $query=$db->prepare('SELECT topic_titre, forum_topic.forum_id,
    forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_topic
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE topic_id =:topic');
    $query->bindValue(':topic',$topic,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();
    $forum = $data['forum_id'];
    
    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Reply to topic</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./voirforum.php?f='.$forum.'" class="section">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><div class="divider"> / </div><a href="./voirtopic.php?t='.$topic.'" class="section">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a><div class="divider"> / </div><div class="active section">Reply to topic</div></div></center><div class="ui divider hidden"></div>';
}
 
// Finally, otherwise it is about moderation (we shall see later in detail)
// We only know the post, we must look for the rest
elseif (isset ($_GET['p']))
{
    $post = (int) $_GET['p'];
    $query=$db->prepare('SELECT post_createur, forum_post.topic_id, topic_titre, forum_topic.forum_id,
    forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo
    FROM forum_post
    LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id
    LEFT JOIN forum_forum ON forum_forum.forum_id = forum_topic.forum_id
    WHERE forum_post.post_id =:post');
    $query->bindValue(':post',$post,PDO::PARAM_INT);
    $query->execute();
    $data=$query->fetch();

    $topic = $data['topic_id'];
    $forum = $data['forum_id'];
    
    echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Edition</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><a href="./voirforum.php?f='.$forum.'" class="section">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><div class="divider"> / </div><a href="./voirtopic.php?t='.$topic.'" class="section">'.stripslashes(htmlspecialchars($data['topic_titre'])).'</a><div class="divider"> / </div><div class="active section">Edition message</div></div></center><div class="ui divider hidden"></div>';
}
$query->CloseCursor();

// Preparation of the different methods
switch($action)
    {
    case "repondre": // First case: we want to answer
        
        // Here you can view the response form
        echo '<form method="post" action="postok.php?action=repondre&t='.$topic.'" name="formulaire" class="ui form">
        <h4 class="ui dividing header">Form to answer</h4>
        <div class="field required">
            <label for="editor">Message</label>
            <textarea id="editor" name="message" placeholder="Content of your answer"></textarea>
        </div>
        <button type="reset" name="Effacer" class="ui basic labeled icon button"><i class="erase icon"></i> Erase</button>
        <button type="submit" name="submit" class="ui primary right labeled icon button">Send <i class="share icon"></i></button>
        </form>';
        
        break;

    case "nouveautopic": // Second case: we want to create a new topic
        
        // Here we show the form of a new topic
        echo '<form method="post" action="postok.php?action=nouveautopic&f='.$forum.'" name="formulaire" class="ui form">
        <h4 class="ui dividing header">Form for a new topic</h4>
        <div class="field required">
            <label for="title">Title</label>
            <input type="text" id="title" name="titre" placeholder="Topic title" />
        </div>
        <div class="field required">
            <label for="editor">Message</label>
            <textarea id="editor" name="message" placeholder="Content of your topic"></textarea>
        </div>';
        
        // Verification of moderation
        if (verif_auth($data['auth_annonce']))
        {
            echo '<div class="inline fields">
            <label>Topic type</label>
            <div class="field">
                <div class="ui radio checkbox">
                    <input type="radio" name="mess" id="1" checked="checked" value="Message" /> <label for="1"><span class="ui tiny label">Normal</span></label>
                </div>
            </div>
            <div class="field">
                <div class="ui radio checkbox">
                    <input type="radio" name="mess" id="2" value="Annonce" /> <label for="2"><span class="ui tiny red label">Announce</span></label>
                </div>
            </div>
            </div>';
            echo '<div class="ui divider"></div>';
        }
        
        echo '<button type="reset" name="Effacer" class="ui basic labeled icon button"><i class="erase icon"></i> Erase</button>
        <button type="submit" name="submit" class="ui primary right labeled icon button">Send <i class="share icon"></i></button>
        </form>';
        
        break;

    case "edit": // If you want to edit the post
        
        // We retrieve the value of p
        $post = (int) $_GET['p'];
 
        // We finally launch our request
        $query=$db->prepare('SELECT post_createur, post_texte, auth_modo FROM forum_post LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id WHERE post_id=:post');
        $query->bindValue(':post',$post,PDO::PARAM_INT);
        $query->execute();
        $data=$query->fetch();

        $text_edit = $data['post_texte']; // We retrieve the message

        // Then we check that the member has the right to be here (either the creator or a mod/admin) 
        if (!verif_auth($data['auth_modo']) && $data['post_createur'] != $id)
        {
            // If this condition is not fulfilled it will bard
            erreur(ERR_AUTH_EDIT);
        }
        else // Otherwise it rolls and displays the sequence
        {
            // The posting form
            echo '<form method="post" action="postok.php?action=edit&p='.$post.'" name="formulaire" class="ui form">
            <h4 class="ui dividing header">Form to edit</h4>
            <div class="field required">
                <label for="editor">Message</label>
                <textarea id="editor" name="message" placeholder="Content of the edition">'.$text_edit.'</textarea>
            </div>
            <button type="reset" name="Effacer" class="ui basic labeled icon button"><i class="erase icon"></i> Erase</button>
            <button type="submit" name="submit" class="ui primary right labeled icon button">Edit <i class="edit icon"></i></button>
            </form>';
        }
        
        break; // End of this case
    
    case "delete": // If you want to delete the post
        
        // We retrieve the value of p
        $post = (int) $_GET['p'];
        
        // Then we check that the member has the right to be here
        $query=$db->prepare('SELECT post_createur, auth_modo FROM forum_post LEFT JOIN forum_forum ON forum_post.post_forum_id = forum_forum.forum_id WHERE post_id= :post');
        $query->bindValue(':post',$post,PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch();
 
        if (!verif_auth($data['auth_modo']) && $data['post_createur'] != $id)
        {
            // If this condition is not fulfilled it will bard
            erreur(ERR_AUTH_DELETE); 
        }
        else // Otherwise it rolls and displays the sequence
        {
            echo '<h1 class="ui center aligned icon header"><blink><i class="circular orange warning sign icon"></i> Warning!</blink></h1><div class="ui divider"></div>';
            echo '<div class="ui icon warning message"><i class="warning sign icon"></i><div class="content"><div class="header">Really?</div><p>Are you sure you want to <strong>delete this post</strong>?</p></div></div>';
            echo '<div class="two ui buttons"><a href="./postok.php?action=delete&p='.$post.'" class="ui positive button">Yes I am sure</a><a href="./index.php" class="ui negative button">No I am not sure</a></div>';
        }
        $query->CloseCursor();
        
        break;

    default: // If ever it is none of those there is that there has been a problem
        
        echo '<div class="ui divider"></div><div class="ui icon negative message"><i class="remove icon"></i><div class="content"><div class="header">Oops!</div><p>This action is impossible!</p></div></div>';
        
    } // End of switch

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
?>
</body>
</html>