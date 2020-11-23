<?php
// This function must be called before any html code
session_start();
if (file_exists("./install.php")) {
	header("Location: ./install.php");
}

// We then give a title to the page, then we call our file debut.php
$titre = "Board index";
include("includes/identifiants.php");

// Assigning session variables
$lvl=(isset($_SESSION['level']))?(int) $_SESSION['level']:1;
$id=(isset($_SESSION['id']))?(int) $_SESSION['id']:0;
$pseudo=(isset($_SESSION['pseudo']))?$_SESSION['pseudo']:'';

// Includes the remaining 2 pages
include("includes/functions.php");
include("includes/constants.php");
require_once("includes/JBBCode/Parser.php");

// Creating variables
$ip = ip2long($_SERVER['REMOTE_ADDR']);

// Request
$query=$db->prepare('INSERT INTO forum_whosonline VALUES(:id, :time,:ip)
ON DUPLICATE KEY UPDATE
online_time = :time , online_id = :id');
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->bindValue(':time',time(), PDO::PARAM_INT);
$query->bindValue(':ip', $ip, PDO::PARAM_INT);
$query->execute();
$query->CloseCursor();
$time_max = time() - (60 * 5);
$query=$db->prepare('DELETE FROM forum_whosonline WHERE online_time < :timemax');
$query->bindValue(':timemax',$time_max, PDO::PARAM_INT);
$query->execute();
$query->CloseCursor();

$query=$db->prepare('SELECT * FROM forum_membres WHERE membre_id=:id');
$query->bindValue(':id',$id, PDO::PARAM_INT);
$query->execute();
$recupinfo=$query->fetch();
$query->CloseCursor();

// Retrieving configuration variables
$query = $db->query('SELECT * FROM forum_config');
$config = array();
while($data=$query->fetch())
{
    $config[$data['config_nom']] = $data['config_valeur']; 
}
$query->CloseCursor();
?>
<!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Site Properties -->
    <title><?php echo $config['forum_titre']; ?> - Home</title>
    
    <link rel="icon" type="image/png" href="./images/logo.png">
    <link rel="stylesheet" type="text/css" href="css/semantic.min.css">
    <link rel="stylesheet" type="text/css" href="css/icon.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script src="js/semantic.min.js"></script>

    <script>
        $(document)
            .ready(function() {

                // fix menu when passed
                $('.masthead')
                    .visibility({
                        once: false,
                        onBottomPassed: function() {
                            $('.fixed.menu').transition('fade in');
                        },
                        onBottomPassedReverse: function() {
                            $('.fixed.menu').transition('fade out');
                        }
                    });

                // create sidebar and attach to menu open
                $('.ui.sidebar')
                    .sidebar('attach events', '.toc.item')
            });

        $(document)
            .ready(function() {
                $('.special.card .image').dimmer({
                    on: 'hover'
                });

                $('.card .dimmer')
                    .dimmer({
                        on: 'hover'
                    });
            });
    </script>
    <!-- End Javascripts -->

    <!-- Styles perso -->
    <style type="text/css">
        .hidden.menu {
            display: none;
        }
        .ui.menu .item img.logo {
            margin-right: 1.3em;
        }
        .masthead.segment {
  position: relative;
  overflow: hidden;
  background-color: #00B5AD;
  text-align: center;
  margin-top: 38px;
  
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 0px;
  border-bottom: none;
}
 .masthead.segment:before {
  background: #2d2731 url(./images/backgrounds/tile-bg<?php echo $config['forum_color']; ?>.png) repeat fixed 0% 0%;
  position: absolute;
  z-index: auto;
  width: 500%;
  height: 500%;
  top: 0px;
  left: 0px;
  content: '';

  -moz-transform-origin: 50% 50%;
  -o-transform-origin: 50% 50%;
  -ms-transform-origin: 50% 50%;
  transform-origin: 50% 50%;

  -webkit-animation-name: masthead;
  -moz-animation-name: masthead;
  -o-animation-name: masthead;
  animation-name: masthead;

  -webkit-animation-duration: 80s;
  -moz-animation-duration: 80s;
  -ms-animation-duration: 80s;
  -o-animation-duration: 80s;
  animation-duration: 80s;

  -webkit-animation-fill-mode: both;
  -moz-animation-fill-mode: both;
  -ms-animation-fill-mode: both;
  -o-animation-fill-mode: both;
  animation-fill-mode: both;

  animation-timing-function: linear;
  -webkit-animation-timing-function: linear;

  -webkit-animation-iteration-count: infinite;
  -moz-animation-iteration-count: infinite;
  -ms-animation-iteration-count: infinite;
  -o-animation-iteration-count: infinite;
  animation-iteration-count: infinite;
}
@keyframes masthead {
 0% {
    background-position: 0% 0%;
 }
 50% {
    background-position: -50% -100%;
 }
 100% {
    background-position: -100% -200%;
 }

}

@-moz-keyframes masthead {
 0% {
   background-position: 0% 0%;
 }
 50% {
   background-position: -50% -100%;
 }
 100% {
   background-position: -100% -200%;
 }

}

@-webkit-keyframes masthead {
 0% {
   background-position: 0% 0%;
 }
 50% {
   background-position: -50% -100%;
 }
 100% {
   background-position: -100% -200%;
 }

}

@-ms-keyframes masthead {
 0% {
   background-position: 0% 0%;
 }
 50% {
   background-position: -50% -100%;
 }
 100% {
   background-position: -100% -200%;
 }

}

@-o-keyframes masthead {
 0% {
   background-position: 0% 0%;
 }
 50% {
   background-position: -50% -100%;
 }
 100% {
   background-position: -100% -200%;
 }

}
        .masthead.segment {
            min-height: 300px;
            padding: 1em 0em;
        }
        
        .masthead .logo.item img {
            margin-right: 1em;
        }
        
        .masthead .ui.menu .ui.button {
            margin-left: 0.5em;
        }
        
        .masthead h1.ui.header {
            margin-top: 1.7em;
            margin-bottom: 0em;
        }
        
        .masthead h2 {
            font-size: 1.7em;
            font-weight: normal;
        }
        
        .ui.vertical.stripe .floated.image {
            clear: both;
        }
        
        .footer.segment {
            padding: 3em 0em;
        }

        
        .secondary.pointing.menu .toc.item {
            display: none;
        }
        
        @media only screen and (max-width: 350px) {
            .ui.fixed.menu {
                display: none !important;
            }
            .secondary.pointing.menu .item,
            .secondary.pointing.menu .menu {
                display: none;
            }
            .secondary.pointing.menu .toc.item {
                display: block;
            }
            .masthead.segment {
                min-height: 350px;
            }
            .masthead h1.ui.header {
                font-size: 2em;
                margin-top: 1.5em;
            }
            .masthead h2 {
                margin-top: 0.5em;
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="ui fixed stackable hidden menu">
        <div class="ui container">
            <div class="header item">
                <h4><?php echo $config['forum_titre']; ?></h4>
            </div>
            <?php if(isset($_SESSION['pseudo'])) { ?>
            <a href="./index.php" class="item active"><i class="home icon"></i>Home</a>
            <?php if (verif_auth(ADMIN) OR verif_auth(MODO)) { ?>
            <a href="./admin.php" class="item"><i class="dashboard icon"></i>Dashboard</a>
            <?php } ?>
            <div class="right menu">
                <div class="ui simple dropdown item">
                    <img class="ui avatar image" src="./images/avatars/<?php echo $recupinfo['membre_avatar']; ?>"> <span><?php echo $pseudo; ?></span><i class="dropdown icon"></i>
                    <div class="menu">
                        <a href="./voirprofil.php?m=<?php echo $data['membre_id']; ?>&action=consulter" class="item"><i class="user outline icon"></i>My profile</a>
                        <a href="./amis.php" class="item"><i class="address book outline icon"></i>My friends</a>
                        <a href="./messagesprives.php" class="item"><i class="mail outline icon"></i>My mails</a>
                        <div class="divider"></div>
                        <a href="./deconnexion.php" class="item"><i class="power red icon"></i>Sign out</a>
                    </div>
                </div>
            </div>
            <?php } else { ?>
            <a href="./index.php" class="item"><i class="home icon"></i>Home</a>
            <div class="right menu">
                <div class="item">
                    <a href="./register.php" class="ui primary button">Register</a>
                </div>
                <div class="item">
                    <a href="./connexion.php" class="ui button">Login</a>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <div class="pusher">
        <!-- EN-TETE START -->
        <div class="ui inverted vertical masthead center aligned segment" >
            <!-- HEADER CONTAINER START -->
            <div class="ui container">
                <div class="ui large secondary inverted menu">
                    <?php if(isset($_SESSION['pseudo'])) { ?>
                    <a href="./index.php" class="item active"><i class="home icon"></i> Home</a>
                    <?php if (verif_auth(ADMIN) OR verif_auth(MODO)) { ?>
                    <a href="./admin.php" class="item"><i class="dashboard icon"></i>Dashboard</a>
                    <?php } ?>
                    <div class="right item">
                        <a href="./voirprofil.php?m=<?php echo $id; ?>&action=consulter" class="item"><small><img class="ui avatar image" src="./images/avatars/<?php echo $recupinfo['membre_avatar']; ?>"></small> <span><?php echo $pseudo; ?></span></a>
                    </div>
                    <?php } else { ?>
                    <a href="./index.php" class="item active"><i class="home icon"></i> Home</a>
                    <div class="right item">
                        <a href="./register.php" class="ui inverted yellow button">Register</a>
                        <a href="./connexion.php" class="ui inverted button">Login</a>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!-- END HEADER CONTAINER -->
            <div class="ui text container animated bounce">
                <div class="ui grid">
                    <div class="column">
                        <h1 class="ui inverted header" style="font-size: 2em;">
                            <?php echo $config['forum_titre']; ?> <span style="color:#efdf25;">Forum</span>
                        </h1>
                        <h2 style="margin-top: 0.5em;
                        font-size: 1.5em;"><?php echo $config['forum_description']; ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <!-- END EN-TETE -->
        <div class="ui secondary vertical stripe black segment animated fadeIn">
            <div class="ui main container">
                <?php
                // Custom Announcement
                $query=$db->prepare('SELECT * FROM forum_announce');
                $query->execute();
                $data=$query->fetch();
                
                if ($data['statut'] == 'on')
                {
                    echo '<div class="ui icon '.$data['color'].' message"><i class="announcement icon"></i><div class="content"><div class="header">'.$data['header'].'</div><p>'.$data['message'].'</p></div></div>';
                }
                $query->CloseCursor(); // Close the loop
                
                echo '<div class="ui horizontal icon divider"><i class="circular comments icon"></i></div>';

                // Initializing two variables
                $totaldesmessages = 0;
                $categorie = NULL;

                $add1='';
                $add2='';
                if ($id!=0) // We are connected
                {
                    // First, select fields
                    $add1 = 'tv_id, tv_forum_id, tv_poste'; 
                    // Second, jointure
                    $add2 = 'LEFT JOIN forum_topic_view ON forum_topic.topic_id = forum_topic_view.tv_topic_id AND forum_topic_view.tv_id = :id';
                }
                // This query allows to get everything on the forum
                $query=$db->prepare('SELECT cat_id, cat_nom, forum_forum.forum_id, forum_name, forum_desc, forum_post, forum_topic, auth_view, forum_topic.topic_id, forum_topic.topic_post, post_id, post_time, post_createur, membre_pseudo, membre_id FROM forum_categorie LEFT JOIN forum_forum ON forum_categorie.cat_id = forum_forum.forum_cat_id LEFT JOIN forum_post ON forum_post.post_id = forum_forum.forum_last_post_id LEFT JOIN forum_topic ON forum_topic.topic_id = forum_post.topic_id LEFT JOIN forum_membres ON forum_membres.membre_id = forum_post.post_createur WHERE auth_view <= :lvl ORDER BY cat_ordre, forum_ordre DESC');
                $query->bindValue(':lvl',$lvl,PDO::PARAM_INT);
                $query->execute();

                // Beginning of the loop
                while($data = $query->fetch())
                {
                    // Each category is displayed
                    if ($categorie != $data['cat_id'])
                    {
                        // If it is a new category it is displayed
                        $categorie = $data['cat_id'];
                        echo '<table class="ui padded celled striped table">
                        <thead>
                            <tr>
                                <th colspan="2">'.stripslashes(htmlspecialchars($data['cat_nom'])).'</th>
                                <th>Topics</th>
                                <th>Messages</th>
                                <th class="collapsing">Last message</th>
                            </tr>
                        </thead>
                        <tbody>';
                    }
                    // Here we put the contents of each category
                    // This super echo of death displays all the forums in detail: description, number of answers etc...
                    echo '<tr><td class="collapsing"><center><i class="big icons"><i class="big '.$config['forum_color'].' circle icon"></i><i class="leaf inverted icon"></i></i></center></td><td><a href="./voirforum.php?f='.$data['forum_id'].'" id="voirforum">'.stripslashes(htmlspecialchars($data['forum_name'])).'</a><p>'.nl2br(stripslashes(htmlspecialchars($data['forum_desc']))).'</p></td><td class="right aligned collapsing"><center><div class="ui circular label">'.$data['forum_topic'].'</div></center></td><td class="right aligned collapsing"><center><div class="ui circular label">'.$data['forum_post'].'</div></center></td>';
                    if (verif_auth($data['auth_view']))
                    {
                        // Viewing forum
                        // Two possible cases: Either there is a new message or the forum is empty
                        if (!empty($data['forum_post']))
                        {
                            // Selection last message
                            $nombreDeMessagesParPage = 15;
                            $nbr_post = $data['topic_post'] +1;
                            $page = ceil($nbr_post / $nombreDeMessagesParPage);
                            echo '<td class="right aligned collapsing"><center><div class="ui inverted black label">'.date('H\hi \o\n d M Y',$data['post_time']).'<br><a href="./voirprofil.php?m='.stripslashes(htmlspecialchars($data['membre_id'])).'&action=consulter" id="voirforum2">'.$data['membre_pseudo'].'</a> <a href="./voirtopic.php?t='.$data['topic_id'].'&page='.$page.'#p_'.$data['post_id'].'" id="voirforum3"><i class="arrow right icon"></i></a></div></center></td></tr>';
                        }
                        else
                        {
                            echo '<td class="right aligned collapsing"><center><div class="ui black label">No new message</div></center></td></tr>';
                        }
                    } // End of authorization check
                    // This variable stores the number of messages, updates it
                    $totaldesmessages += $data['forum_post'];
                    // We close our loop and our tags
                } // End of loop
                $query->CloseCursor();
                echo '</tbody></table>';

                // Initializing the variable
                $count_online = 0;
                // Counting visitors
                $count_visiteurs=$db->query('SELECT COUNT(*) AS nbr_visiteurs FROM forum_whosonline WHERE online_id = 0')->fetchColumn();
                $query->CloseCursor();

                // Counting of members
                $texte_a_afficher = "<br /><i class='announcement icon'></i> List of people online: ";
                $time_max = time() - (60 * 5);
                $query=$db->prepare('SELECT membre_id, membre_pseudo FROM forum_whosonline LEFT JOIN forum_membres ON online_id = membre_id WHERE online_time > :timemax AND online_id <> 0');
                $query->bindValue(':timemax',$time_max, PDO::PARAM_INT);
                $query->execute();
                $count_membres=0;
                while ($data = $query->fetch())
                {
                    $count_membres ++;
                    $texte_a_afficher .= '<a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a> ,';
                }
                $texte_a_afficher = substr($texte_a_afficher, 0, -1);
                $count_online = $count_visiteurs + $count_membres;
                echo '<p><i class="fa fa-info"></i> There are <strong>'.$count_online.'</strong> online ('.$count_membres.' member(s) and '.$count_visiteurs.' guest(s))';
                echo $texte_a_afficher.'</p>';
                $query->CloseCursor();

                echo '</div><div class="ui divider hidden"></div>';
                echo '</div>';

                // The footer here:
                echo '<div class="ui stripe vertical quote segment"><div class="ui equal width stackable internally celled grid"><div class="center aligned row">';
                // Counting members
                $TotalDesMembres = $db->query('SELECT COUNT(*) FROM forum_membres')->fetchColumn();
                $query->CloseCursor();	
                $query = $db->query('SELECT membre_pseudo, membre_id FROM forum_membres ORDER BY membre_id DESC LIMIT 0, 1');
                $data = $query->fetch();
                $derniermembre = stripslashes(htmlspecialchars($data['membre_pseudo']));

                echo '<div class="column"><h3 class="ui center aligned icon header"><i class="circular comments icon"></i> Total messages</h3><div class="ui big black label">'.$totaldesmessages.'</div></div>';
                echo '<div class="column"><h3 class="ui center aligned icon header"><i class="circular users icon"></i> Total users</h3><div class="ui big black label">'.$TotalDesMembres.'</div></div>';
                echo '<div class="column"><h3 class="ui center aligned icon header"><i class="circular user icon"></i> Last user</h3><a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter" class="ui big black label">'.$derniermembre.'</a></div>';
                echo '</div></div>';
                $query->CloseCursor();
                
                echo '</div></div>';
                
                // Start footer
                echo '<div class="ui inverted vertical footer segment">
                <div class="ui center aligned container">
                    <img src="./images/logo.png" class="ui centered mini image">
                    <div class="ui horizontal inverted small divided link list">
                        <a class="item" href="#">About</a>
                        <a class="item" href="#">Terms and Conditions</a>
                        <a class="item" href="#">Privacy Policy</a>
                    </div>
                </div>
                </div>';
                ?>
</body>
</html>