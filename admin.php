<?php
session_start();
$titre="Administration";
$balises = true;
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

// You will find where you are
$cat = (isset($_GET['cat']))?htmlspecialchars($_GET['cat']):'';

if (!verif_auth(ADMIN)) erreur(ERR_AUTH_ADMIN);

echo '<div class="ui main container animated fadeIn"><div class="ui segment">
<div class="sidebar-container">
    <div class="ui visible fixed inverted left vertical sidebar labeled icon menu">
        <div class="ui divider hidden"></div><div class="ui divider hidden"></div><div class="ui divider hidden"></div>
        <div class="item">
            <div class="header">
                <i class="dashboard icon"></i> Control panel
            </div>
        </div>
        <div class="item">
            <a class="item" href="./admin.php?cat=config">
                <i class="settings icon"></i> Site/Forum settings
            </a>
            <a class="active item" href="./admin.php?cat=forum">
                <i class="laptop icon"></i> Forums Management
            </a>
            <a class="item" href="./admin.php?cat=membres">
                <i class="users icon"></i> Members Administration
            </a>
        </div>
        <div class="item">
            <div class="header">
                <button class="ui inverted support-button button" href=""><i class="book icon"></i> Support</button>
            </div>
        </div>
    </div>
</div>
<div class="ui basic save-modal modal">
    <div class="ui active">
        <div class="ui large text loader">Just a second, we prepare the way for you!</div>
    </div>
</div>
<script>
$(".support-button").click(function(){
  $(".save-modal").modal("show");
  var URL="http://demo.squamifer.ovh/osiris/docs/";
  setTimeout(function(){ window.location = URL; }, 2500 );  
});
</script>';

echo '<div class="pusher">';

switch($cat) // 1st switch
    {
        case "config": // Configuration
        
        echo '<div class="ui container">
        <div class="ui basic segment">
            <h3 class="ui header"><i class="settings red icon"></i>
                <div class="content"> Site/Forum settings
                    <div class="sub header">Configurable parameters throughout the site.</div>
                </div>
            </h3>
            <div class="ui clearing divider"></div>
            <center><div class="ui breadcrumb"><a href="./admin.php" class="section">Dashboard</a><div class="divider"> / </div><div class="active section">Site/Forum settings</div></div></center><div class="ui divider hidden"></div>';
            
            echo '<div class="dynamic no example">
            <div class="ui pointing secondary menu">
                <a class="active item" data-tab="first"><i class="settings icon"></i> Site/forum settings</a>
                <a class="item" data-tab="second"><i class="announcement icon"></i> Announcement settings</a>
            </div>';

            echo '<div class="ui active tab red basic segment" data-tab="first">';
            echo '<form method="post" action="adminok.php?cat=config" class="ui form">';
            echo '<h4 class="ui dividing header">Configurable parameters</h4>';

            // The associative array
            $config_name = array(
                "forum_titre" => "Site/Forum title",
                "forum_description" => "Site/Forum description",
                "forum_color" => "Site/Forum color (view Support doc for colors)",
                "avatar_default" => "Default avatar",
                "avatar_maxsize" => "Maximum size of the avatar",
                "avatar_maxh" => "Maximum height of the avatar",
                "avatar_maxl" => "Maximum width of the avatar",
                "sign_maxl" => "Maximum size of the signature",
                "auth_bbcode_sign" => "Allow bbcode in signature?",
                "pseudo_maxsize" => "Maximum username size",
                "pseudo_minsize" => "Minimum username size",
                "topic_par_page" => "Number of topics per page",
                "post_par_page" => "Number of posts per page",
                "member_par_page" => "Number of members per page",
                "temps_flood" => "Anti flood in sec"
            );
            $query = $db->query('SELECT config_nom, config_valeur FROM forum_config');
            while($data=$query->fetch())
            {
                echo '<div class="field">
                <label for='.$data['config_nom'].'>'.$config_name[$data['config_nom']].'</label>
                    <input type="text" id="'.$data['config_nom'].'" value="'.$data['config_valeur'].'" name="'.$data['config_nom'].'" placeholder="The field is empty">
                </div>';
            }
            echo '<div class="ui divider"></div>';
            echo '<button class="ui fluid positive toggle right labeled icon button">Save configuration <i class="checkmark icon"></i></button>';
            echo '</form></div>';
            $query->CloseCursor();
            
            
            echo '<div class="ui tab black basic segment" data-tab="second">';
            echo '<form method="post" action="adminok.php?cat=configannounce" class="ui form">';
            echo '<h4 class="ui dividing header">Announcement custom message</h4>';

            $query = $db->query('SELECT * FROM forum_announce');
            while($data=$query->fetch())
            {
                echo '<div class="field">
                <label for="header">Header title</label>
                    <input type="text" id="header" value="'.$data['header'].'" name="header" placeholder="The field is empty">
                </div>
                <div class="field">
                    <label for="message">Message content</label>
                        <input type="text" id="message" value="'.$data['message'].'" name="message" placeholder="The field is empty">
                </div>
                <div class="field">
                    <label for="color">Choose your color</label>
                    <select id="color" name="color" class="ui dropdown">
                        <option name="color" value="info">Info</option>
                        <option name="color" value="warning">Warning</option>
                        <option name="color" value="negative">Error</option>
                        <option name="color" value="positive">Success</option>
                        <option name="color" value="black">Black</option>
                        <option name="color" value="brown">Brown</option>
                        <option name="color" value="pink">Pink</option>
                        <option name="color" value="purple">Purple</option>
                        <option name="color" value="yellow">Yellow</option>
                        <option name="color" value="teal">Teal</option>
                        <option name="color" value="olive">Olive</option>
                        <option name="color" value="orange">Orange</option>
                        <option name="color" value="red">Red</option>
                    </select>
                </div>
                <div class="field">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut" class="ui dropdown">
                        <option name="statut" value="on">On</option>
                        <option name="statut" value="off">Off</option>
                    </select>
                </div>';
            }
            echo '<div class="ui divider"></div>';
            echo '<button class="ui fluid positive toggle right labeled icon button">Save configuration <i class="checkmark icon"></i></button>';
            echo '</form></div>';
            $query->CloseCursor();
            
            echo '</div>';
            
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
            
            echo '</div></div></div></div><div class="ui divider hidden"></div>';
        
        break;
 
        case "forum": // Here forum
        
        $action = htmlspecialchars($_GET['action']); // The action value
        switch($action) //2eme switch
            {
            case "creer": // Creating a forum
        
            // 1st case: no variable c
            if(empty($_GET['c']))
                {
            echo '<div class="ui container">
            <div class="ui basic segment">
                <h3 class="ui header"><i class="laptop orange icon"></i>
                    <div class="content"> Forums Management
                        <div class="sub header">Create a forum or a category.</div>
                    </div>
                </h3>
                <div class="ui clearing divider"></div>
                <div class="ui two column grid">
                    <div class="column">
                        <div class="ui fluid black link card">
                            <a href="./admin.php?cat=forum&action=creer&c=f"><br>
                                <div class="content">
                                    <h2 class="ui center aligned icon header"><i class="circular plus icon"></i> Create a forum</h2>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="column">
                    <div class="ui fluid teal link card">
                        <a href="./admin.php?cat=forum&action=creer&c=c"><br>
                            <div class="content">
                                <h2 class="ui center aligned icon header"><i class="circular plus icon"></i> Create a category</h2>
                            </div>
                        </a>
                    </div>
                    </div>
                </div>';
                }

            // 2nd case: we try to create a forum (c = f)
            elseif($_GET['c'] == "f")
                {
                    $query=$db->query('SELECT cat_id, cat_nom FROM forum_categorie ORDER BY cat_ordre DESC');
            
                    echo '<div class="ui container">
                    <div class="ui basic segment">
                        <h3 class="ui header"><i class="plus black icon"></i>
                            <div class="content"> Creating a forum
                                <div class="sub header">Fill in with your settings.</div>
                            </div>
                        </h3>
                        <div class="ui clearing divider"></div>';

                    echo '<form method="post" action="./adminok.php?cat=forum&action=creer&c=f" class="ui form">';
                    echo '<div class="field required">
                    <label>Name</label>
                    <input type="text" id="nom" name="nom" required>
                    </div>
                    <div class="field">
                    <label>Description</label>
                    <textarea rows="3" name="desc" id="desc"></textarea>
                    </div>
                    <div class="field required">
                    <label>Category</label>
                    <select name="cat" class="ui dropdown">';
                    while($data = $query->fetch())
                        {
                            echo '<option value="'.$data['cat_id'].'">'.$data['cat_nom'].'</option>';
                        }
                    echo '</select></div>
                    <button type="submit" class="ui black button">Create</button></form>';
            
                    echo "<script>
                    $('.ui.dropdown')
                        .dropdown()
                    ;
                    </script>";
		          $query->CloseCursor();
                }
            // 3rd case: we want to create a category (c = c)
            elseif($_GET['c'] == "c")
                {
                    echo '<div class="ui container">
                    <div class="ui basic segment">
                        <h3 class="ui header"><i class="plus teal icon"></i>
                            <div class="content"> Creating a category
                                <div class="sub header">Fill in with your settings.</div>
                            </div>
                        </h3>
                        <div class="ui clearing divider"></div>';
                    echo '<form method="post" action="./adminok.php?cat=forum&action=creer&c=c" class="ui form">';
                    echo '<div class="field required">
                    <label>Name of the Category</label>
                    <input type="text" id="nom" name="nom" required>
                    </div>
                    <button type="submit" class="ui black button">Create</button></form>';
                }
                
                break;
        
                case "edit": // Editing a forum
                
                if(!isset($_GET['e']))
                    {
                        echo '<div class="ui container">
                        <div class="ui basic segment">
                            <h3 class="ui header"><i class="laptop brown icon"></i>
                                <div class="content"> Forums Management
                                    <div class="sub header">Editing forums, categories and their rights.</div>
                                </div>
                            </h3>
                            <div class="ui clearing divider"></div>
                            <div class="ui four column grid">
                                <div class="column">
                                    <div class="ui fluid red link card">
                                        <a href="./admin.php?cat=forum&action=edit&e=editf"><br>
                                            <div class="content">
                                                <h2 class="ui center aligned icon header"><i class="circular edit icon"></i> Editing a forum</h2>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="column">
                                <div class="ui fluid blue link card">
                                    <a href="./admin.php?cat=forum&action=edit&e=editc"><br>
                                        <div class="content">
                                            <h2 class="ui center aligned icon header"><i class="circular edit icon"></i> Editing a category</h2>
                                        </div>
                                    </a>
                                </div>
                                </div>
                                <div class="column">
                                    <div class="ui fluid green link card">
                                        <a href="./admin.php?cat=forum&action=edit&e=ordref"><br>
                                            <div class="content">
                                                <h2 class="ui center aligned icon header"><i class="circular sort icon"></i> Change the order of the forums</h2>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="column">
                                    <div class="ui fluid yellow link card">
                                        <a href="./admin.php?cat=forum&action=edit&e=ordrec"><br>
                                            <div class="content">
                                                <h2 class="ui center aligned icon header"><i class="circular sort icon"></i> Change the order of categories</h2>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>';
                        }
                    elseif($_GET['e'] == "editf")
                        {
                            // First we show the list of forums
                            if(!isset($_POST['forum']))
                            {
                                $query=$db->query('SELECT forum_id, forum_name FROM forum_forum ORDER BY forum_ordre DESC');
                
                                echo '<div class="ui container">
                                <div class="ui basic segment">
                                    <h3 class="ui header"><i class="edit red icon"></i>
                                        <div class="content"> Editing a forum
                                            <div class="sub header">Choose your forum to edit.</div>
                                        </div>
                                    </h3>
                                    <div class="ui clearing divider"></div>';
			   
                                echo '<form method="post" action="admin.php?cat=forum&action=edit&e=editf" class="ui form">';
                                echo '<div class="field">
                                <label>Choose a forum</label>
                                <div class="inline field">
                                <select name="forum" class="ui dropdown">';
				   
                                while($data = $query->fetch())
                                    {
                                        echo '<option value="'.$data['forum_id'].'">'.stripslashes(htmlspecialchars($data['forum_name'])).'</option>';
                                    }
                                echo '</select><button type="submit" class="ui red inverted button">Select</button></div></div></form>';
                
                                echo "<script>
                                $('.ui.dropdown')
                                    .dropdown()
                                ;
                                </script>";
                                $query->CloseCursor();
                            }
                        // Then, the information on the chosen forum is displayed
                        else
                            {
                                $query = $db->prepare('SELECT forum_id, forum_name, forum_desc, forum_cat_id FROM forum_forum WHERE forum_id = :forum');
                                $query->bindValue(':forum',(int) $_POST['forum'],PDO::PARAM_INT);
                                $query->execute();
                                $data1 = $query->fetch();

                                echo '<div class="ui container">
                                <div class="ui basic segment">
                                    <h3 class="ui header"><i class="edit red icon"></i>
                                        <div class="content"> Editing of the forum '.stripslashes(htmlspecialchars($data1['forum_name'])).'
                                            <div class="sub header">Fill in with your settings.</div>
                                        </div>
                                    </h3>
                                    <div class="ui clearing divider"></div>';
                                echo '<h4 class="ui dividing header">Editing of the forum <strong>'.stripslashes(htmlspecialchars($data1['forum_name'])).'</strong></h4>';

                                echo '<form method="post" action="adminok.php?cat=forum&action=edit&e=editf" class="ui form">
                                <div class="field required">
                                <label>Forum Name</label>
                                <input type="text" id="nom" name="nom" value="'.$data1['forum_name'].'" required>
                                </div>
                                <div class="field">
                                <label>Description</label>
                                <textarea rows="3" name="desc" id="desc">'.$data1['forum_desc'].'</textarea>
                                </div>';
                            $query->CloseCursor();
                            
                            // From here, all categories are looped,
                            // We will show first the forum
                            $query = $db->query('SELECT cat_id, cat_nom FROM forum_categorie ORDER BY cat_ordre DESC');
                
                            echo '<div class="field">
                            <label>Move the forum to</label>
                            <select name="depl" class="ui dropdown">';
                            while($data2 = $query->fetch())
                                {
                                    if($data2['cat_id'] == $data1['forum_cat_id'])
                                        {
                                            echo '<option value="'.$data2['cat_id'].'" selected="selected">'.stripslashes(htmlspecialchars($data2['cat_nom'])).' 
                                            </option>';
                                        }
                                    else
                                        {
                                            echo '<option value="'.$data2['cat_id'].'">'.$data2['cat_nom'].'</option>';
                                        }
                                }
                            
                            echo '</select></div>
                            <input type="hidden" name="forum_id" value="'.$data1['forum_id'].'">
                            <button type="submit" class="ui inverted red button">Edit</button></form>';
                            
                            echo "<script>
                            $('.ui.dropdown')
                                .dropdown()
                            ;
                            </script>";
                            $query->CloseCursor();
                            }
                        }
                    elseif($_GET['e'] == "editc")
                        {
                            // We start by displaying the list of categories
                            if(!isset($_POST['cat']))
                                {
                                    $query = $db->query('SELECT cat_id, cat_nom FROM forum_categorie ORDER BY cat_ordre DESC');
                
                                    echo '<div class="ui container">
                                    <div class="ui basic segment">
                                        <h3 class="ui header"><i class="edit blue icon"></i>
                                            <div class="content"> Editing a category
                                                <div class="sub header">Choose your category to edit.</div>
                                            </div>
                                        </h3>
                                        <div class="ui clearing divider"></div>';
                                    echo '<form method="post" action="admin.php?cat=forum&action=edit&e=editc" class="ui form">';
                                    echo '<div class="field required">
                                    <label>Choose a category</label>
                                    <div class="inline field">
                                        <select name="cat" class="ui dropdown">';
                                    while($data = $query->fetch())
                                        {
                                            echo '<option value="'.$data['cat_id'].'">'.$data['cat_nom'].'</option>';
                                        }
                                echo'</select>
                                <button type="submit" class="ui inverted blue button">Select</button></div></div></form>';
                                echo "<script>
                                $('.ui.dropdown')
                                    .dropdown()
                                ;
                                </script>";
                                $query->CloseCursor();
                                }
                            // Then the form
                            else
                                {
                                    $query = $db->prepare('SELECT cat_nom FROM forum_categorie WHERE cat_id = :cat');
                                    $query->bindValue(':cat',(int) $_POST['cat'],PDO::PARAM_INT);
                                    $query->execute();
                                    $data = $query->fetch();
                
                                    echo '<div class="ui container">
                                    <div class="ui basic segment">
                                        <h3 class="ui header"><i class="edit blue icon"></i>
                                            <div class="content"> Editing a category
                                                <div class="sub header">Choose a name for your category.</div>
                                            </div>
                                        </h3>
                                        <div class="ui clearing divider"></div>';
                                    echo '<form method="post" action="./adminok.php?cat=forum&action=edit&e=editc" class="ui form">';
                                    echo '<div class="field required">
                                    <label>Enter the name of the new category</label>
                                    <input type="text" id="nom" name="nom" value="'.stripslashes(htmlspecialchars($data['cat_nom'])).'" required>
                                    </div>
                                    <input type="hidden" name="cat" value="'.$_POST['cat'].'" />
                                    <button type="submit" class="ui inverted blue button">Edit</button></form>';
                                    $query->CloseCursor();
                                }
                        }
                    elseif($_GET['e'] == "ordref")
                        {
                            $categorie="";
                            $query = $db->query('SELECT forum_id, forum_name, forum_ordre, forum_cat_id, cat_id, cat_nom FROM forum_categorie LEFT JOIN forum_forum ON cat_id = forum_cat_id ORDER BY cat_ordre DESC');
            
                            echo '<div class="ui container">
                            <div class="ui basic segment">
                                <h3 class="ui header"><i class="sort green icon"></i>
                                    <div class="content"> Change the order of the forums
                                        <div class="sub header">A high number to be at the top, a lower number to be at the bottom.</div>
                                    </div>
                                </h3>
                                <div class="ui clearing divider"></div>';

                            echo '<form method="post" action="adminok.php?cat=forum&action=edit&e=ordref" class="ui form">';

                            echo '<table class="ui celled striped table">';

                            while($data = $query->fetch())
                                {
                                    if($categorie !== $data['cat_id'])
                                        {
                                            $categorie = $data['cat_id'];
                                            echo '<thead>
                                            <tr>       
                                                <th><i class="tag icon"></i> '.stripslashes(htmlspecialchars($data['cat_nom'])).'</th>
                                            <th><i class="sort icon"></i> Sort</th>
                                            </tr>
                                            </thead>';
                                        }
                                    echo '<tbody>
                                    <tr>
                                    <td><a href="./voirforum.php?f='.$data['forum_id'].'">'.$data['forum_name'].'</a></td>
                                    <td><input type="text" value="'.$data['forum_ordre'].'" name="'.$data['forum_id'].'" /></td>
                                    </tr>
                                    </tbody>';
                                }
                            echo '</table>
                            <button type="submit" class="ui fluid positive button">Send modification</button></form>';
                        }
                    elseif($_GET['e'] == "ordrec")
                        {
                            $query = $db->query('SELECT cat_id, cat_nom, cat_ordre FROM forum_categorie ORDER BY cat_ordre DESC');

                            echo '<div class="ui container">
                            <div class="ui basic segment">
                                <h3 class="ui header"><i class="sort yellow icon"></i>
                                    <div class="content"> Change the order of the categories
                                        <div class="sub header">A high number to be at the top, a lower number to be at the bottom.</div>
                                    </div>
                                </h3>
                                <div class="ui clearing divider"></div>';
 
                            echo '<form method="post" action="adminok.php?cat=forum&action=edit&e=ordrec" class="ui form">';
                            while($data = $query->fetch())
                                {
                                    echo '<div class="field">
                                    <label><i class="tag icon"></i> '.stripslashes(htmlspecialchars($data['cat_nom'])).'</label>
                                    <input type="text" value="'.$data['cat_ordre'].'" name="'.$data['cat_id'].'">
                                    </div>';
                                }
                            echo '<button type="submit" class="ui fluid positive button">Edit modification</button></form>';
                            $query->CloseCursor();
                        }
                break;
                
                
                case "droits": // Rights management
        
                echo '<div class="ui container">
                <div class="ui basic segment">
                    <h3 class="ui header"><i class="law pink icon"></i>
                        <div class="content"> Editing Rights
                            <div class="sub header">Modify the rights of a forum.</div>
                        </div>
                    </h3>
                    <div class="ui clearing divider"></div>';
       
                if(!isset($_POST['forum']))
                    {
                        $query=$db->query('SELECT forum_id, forum_name FROM forum_forum ORDER BY forum_ordre DESC');
                    
                        echo '<form method="post" action="admin.php?cat=forum&action=droits" class="ui form">';
                        echo '<div class="field"><label>Select a forum</label>
                        <div class="inline field"><select name="forum" class="ui dropdown">';
                        while($data = $query->fetch())
                            {
                                echo '<option value="'.$data['forum_id'].'">'.$data['forum_name'].'</option>';
                            }
                        echo '</select><button type="submit" class="ui primary labeled icon button"><i class="hand pointer icon"></i> Select</button></div></div></form>';
                    
                        $query->CloseCursor();

                        echo "<script>
                        $('.ui.dropdown')
                            .dropdown()
                        ;
                          </script>";
                    }
                else
                    {
                        $query = $db->prepare('SELECT forum_id, forum_name, auth_view, auth_post, auth_topic, auth_annonce, auth_modo FROM forum_forum WHERE forum_id = :forum');
                        $query->bindValue(':forum',(int) $_POST['forum'], PDO::PARAM_INT);
                        $query->execute();

                            echo '<form method="post" action="adminok.php?cat=forum&action=droits" class="ui form">
                            <table><tr>
                            <th>Read/See</th>
                            <th>Reply</th>
                            <th>To Post</th>
                            <th>Announce</th>
                            <th>Moderate</th>
                            </tr>';
                    
                            $data = $query->fetch();
                            // These two tables will make it possible to display the results
                            $rang = array(
                            VISITEUR=>"Visitor",
                            INSCRIT=>"Member", 
                            MODO=>"Moderator",
                            ADMIN=>"Administrator");
                            $list_champ = array("auth_view", "auth_post", "auth_topic","auth_annonce", "auth_modo");

                        // We Loop
                        foreach($list_champ as $champ)
                            {
                                echo '<td><select name="'.$champ.'" class="ui dropdown">';
                                for($i=1;$i<5;$i++)
                                    {
                                        if ($i == $data[$champ])
                                            {
                                                echo '<option value="'.$i.'" selected="selected">'.$rang[$i].'</option>';
                                            }	
                                        else
                                            {
                                                echo '<option value="'.$i.'">'.$rang[$i].'</option>';
                                            }
                                    }
                                echo '</td></select>';
                            }	
                        echo'<input type="hidden" name="forum_id" value="'.$data['forum_id'].'" />
                        <button type="submit" class="ui labeled icon green button"><i class="edit icon"></i> Edit</button></form>';
                    
                        $query->CloseCursor();

                        echo "<script>
                        $('.ui.dropdown')
                            .dropdown()
                        ;
                          </script>";
                    }
                echo '</table>';
                
                break;
        
            default; // If the action is not completed, we display the menu
            echo '<div class="ui container">
            <div class="ui basic segment">
                <h3 class="ui header"><i class="laptop blue icon"></i>
                    <div class="content"> Forums Management
                        <div class="sub header">Create, edit forums and categories and their positions. And the rights to view or post on a forum/topic.</div>
                    </div>
                </h3>
                <div class="ui clearing divider"></div>
                <div class="ui three column grid">
                    <div class="column">
                        <div class="ui fluid orange link card">
                            <a href="./admin.php?cat=forum&action=creer"><br>
                                <div class="content">
                                    <h2 class="ui center aligned icon header"><i class="circular plus icon"></i> Creations</h2>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="column">
                    <div class="ui fluid brown link card">
                        <a href="./admin.php?cat=forum&action=edit"><br>
                            <div class="content">
                                <h2 class="ui center aligned icon header"><i class="circular edit icon"></i> Modifications</h2>
                            </div>
                        </a>
                    </div>
                    </div>
                    <div class="column">
                        <div class="ui fluid pink link card">
                            <a href="./admin.php?cat=forum&action=droits"><br>
                                <div class="content">
                                    <h2 class="ui center aligned icon header"><i class="circular law icon"></i> Change rights</h2>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>';
                break;
        }
        break;
        
        
    case "membres": // Here members

    $action = htmlspecialchars($_GET['action']); // The action value
    switch($action) // 2nd switch
        {
            case "edit":

            if(!isset($_POST['membre'])) //Si la variable $_POST['membre'] n'existe pas
                {

                    echo '<div class="ui container">
                    <div class="ui basic segment">
                        <h3 class="ui header"><i class="edit green icon"></i>
                            <div class="content"> Choose a member to edit
                                <div class="sub header">Choose a member to edit his profile.</div>
                            </div>
                        </h3>
                        <div class="ui clearing divider"></div>';
                    echo '<form method="post" action="./admin.php?cat=membres&action=edit" class="ui form">
                    <div class="field required">
                    <label for="membre">Enter the username</label> 
                    <div class="inline field">
                        <input type="text" id="membre" name="membre">
                        <button type="submit" class="ui labeled icon primary button" name="Search"><i class="search icon"></i> Search</button>
                    </div></div>
                    </form>';
                }
            else // Then
                {
                    $pseudo_d = $_POST['membre'];
                    // Query that retrieves information about the member from its username
                    $query = $db->prepare('SELECT membre_id, membre_pseudo, membre_email, membre_siteweb, membre_signature, membre_btag, membre_localisation, membre_avatar FROM forum_membres WHERE LOWER(membre_pseudo)=:pseudo');
                    $query->bindValue(':pseudo',strtolower($pseudo_d),PDO::PARAM_STR);
                    $query->execute();
                
                    // If the query returns a trick, the member exists
                    if ($data = $query->fetch())
                        {
                            echo '<div class="ui container">
                            <div class="ui basic segment">
                                <h3 class="ui header"><i class="large icons"><i class="user outline green icon"></i><i class="corner edit icon"></i></i>
                                    <div class="content"> Edit profile
                                        <div class="sub header">Editing a member\'s profile.</div>
                                    </div>
                                </h3>
                                <div class="ui clearing divider"></div>';
                                echo '<center><div class="ui breadcrumb"><a href="./admin.php" class="section">Dashboard</a><div class="divider"> / </div><div class="ui breadcrumb"><a href="./admin.php?cat=membres&action=edit" class="section">Choose Profile</a><div class="divider"> / </div><div class="active section">Edit Profile</div></div></center><div class="ui divider hidden"></div>';

                            echo '<form method="post" action="adminok.php?cat=membres&action=edit" enctype="multipart/form-data" class="ui form">
                            <h4 class="ui dividing header">Identifiants</h4>
                            <div class="field">
                                <label for="pseudo1">Username</label>
                                <div class="ui left icon input">
                                    <i class="user icon"></i>
                                    <input type="text" id="pseudo1" name="pseudo1" value="'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'" placeholder="e.g: Johndoe">
                                </div>
                            </div>

                            <h4 class="ui dividing header">Contacts</h4>
                            <div class="field">
                                <label for="email">His E-mail address</label>
                                <div class="ui left icon input">
                                    <i class="at icon"></i>
                                    <input type="text" name="email" id="email" value="'.stripslashes($data['membre_email']).'">
                                </div>
                            </div>
                            <div class="field">
                                <label for="btag">His BattleTag</label>
                                <div class="ui left icon input">
                                    <i class="fire icon"></i>
                                    <input type="text" name="btag" id="btag" value="'.stripslashes($data['membre_btag']).'">
                                </div>
                            </div>
                            <div class="field">
                                <label for="website">His Website</label>
                                <div class="ui left icon input">
                                    <i class="globe icon"></i>
                                    <input type="text" name="website" id="website" value="'.stripslashes($data['membre_siteweb']).'">
                                </div>
                            </div>

                            <h4 class="ui dividing header">Additional Information</h4>
                            <div class="field">
                                <label for="localisation">His Localization</label>
                                <div class="ui left icon input">
                                    <i class="flag outline icon"></i>
                                    <input type="text" name="localisation" id="localisation" value="'.stripslashes($data['membre_localisation']).'">
                                </div>
                            </div>

                            <h4 class="ui dividing header">Profile on the forum</h4>
                            <div class="field">
                                <label for="avatar">Change his avatar</label>
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
                                <label for="signature">His Signature</label>
                                <textarea name="signature" id="signature" rows="3">'.stripslashes($data['membre_signature']).'</textarea>
                            </div>
                            <div class="ui divider"></div>
                            <input type="hidden" value="'.stripslashes($data['membre_id']).'" name="membre_id">
                            <input type="hidden" value="'.stripslashes($pseudo_d).'" name="pseudo_d">
                            <button type="submit" name="sent" class="ui fluid positive button">Modify informations</button>
                            </form></div>';

                            $query->CloseCursor();

                            echo '</div></div></div><div class="ui divider hidden"></div>';
                        }
                    else echo '<div class="ui icon negative message"><i class="help icon"></i><div class="content"><div class="header">Error!</div><p>This <strong>member</strong> does not exist!</p><ul class="list"><li><a href="./admin.php">Click here to go back to the dashboard.</a></li></ul></div></div>';
                }
            
            break;

        case "droits":
            //Droits d'un membre (rang)
            echo '<div class="ui container">
                    <div class="ui basic segment">
                        <h3 class="ui header"><i class="shield violet icon"></i>
                            <div class="content"> Membership Rights Management
                                <div class="sub header">Edit the rank of a member.</div>
                            </div>
                        </h3>
                        <div class="ui clearing divider"></div>';

            if(!isset($_POST['membre']))
            {
                    echo'<h4 class="ui dividing header">Which member do you want to change the rights to?</h4>';
                    echo'<form method="post" action="./admin.php?cat=membres&action=droits" class="ui form">
                    <div class="field required">
                    <label for="membre">Enter the username</label> 
                    <div class="inline field">
                        <input type="text" id="membre" name="membre">
                        <button type="submit" class="ui labeled icon primary button"><i class="search icon"></i> Search</button>
                    </div></div>
                    </form>';
            }
            else
            {
                $pseudo_d = $_POST['membre'];
                $query = $db->prepare('SELECT membre_pseudo,membre_rang FROM forum_membres WHERE LOWER(membre_pseudo) = :pseudo');
            $query->bindValue(':pseudo',strtolower($pseudo_d),PDO::PARAM_STR);
                $query->execute();
            if ($data = $query->fetch())
                {       
                    echo'<form action="./adminok.php?cat=membres&action=droits" method="post" class="ui form">';
                $rang = array
                    (0 => "Banned",
                    1 => "Visitor", 
                    2 => "Member", 
                    3 => "Moderator", 
                    4 => "Administrator"); //Ce tableau associe numéro de droit et nom
                    echo'<div class="field"><label>'.$data['membre_pseudo'].'</label>';
                    echo'<div class="inline field"><select name="droits" class="ui dropdown">';
                    for($i=0;$i<5;$i++)
                    {
                if ($i == $data['membre_rang'])
                    {
                    echo'<option value="'.$i.'" selected="selected">'.$rang[$i].'</option>';
                }
                else
                {
                    echo'<option value="'.$i.'">'.$rang[$i].'</option>';
                }
                    }
            echo'</select>
            <input type="hidden" value="'.stripslashes($pseudo_d).'" name="pseudo">               
            <button type="submit" class="ui positive labeled icon button" value="Edit"><i class="edit icon"></i> Edit</button></div></div></form>';
                    $query->CloseCursor();
                }
                else echo' <p>Erreur : Ce membre n existe pas, <br />
                cliquez <a href="./admin.php?cat=membres&amp;action=edit">ici</a> pour réessayer</p>';
            }
                echo "<script>
                $('.ui.dropdown')
                    .dropdown()
                ;
                  </script>";
        break;

            case "ban": // Ban

            echo '<div class="ui container">
            <div class="ui basic segment">
                <h3 class="ui header"><i class="legal olive icon"></i>
                    <div class="content"> Management of banishments
                        <div class="sub header">Ban/Unban a member registered on your forum.</div>
                    </div>
                </h3>
                <div class="ui clearing divider"></div>';

            // Text box to ban the member
            echo '<h4 class="ui dividing header">Which member would you ban?</h4>';
            echo '<form method="post" action="./adminok.php?cat=membres&action=ban" class="ui form">
            <div class="field required">
                <label for="membre">Enter the username</label> 
                <input type="text" id="membre" name="membre" placeholder="e.g: Johndoe">
            </div>
            <button type="submit" class="ui red right labeled icon button">Banish <i class="legal icon"></i></button>';

            // Here we loop: for each member banned, we display a checkbox that proposes to unban
            $query = $db->query('SELECT membre_id, membre_pseudo, membre_avatar FROM forum_membres WHERE membre_rang = 0');

            // Of course, we do not start the sequel unless there are banned members!
            if ($query->rowCount() > 0)
                {
                    while($data = $query->fetch())
                        {
                            echo '<div class="ui divider"></div>';
                            echo '<div class="inline fields"><a href="./voirprofil.php?action=consulter&m='.$data['membre_id'].'" class="ui large basic image label" target="_blank"><img src="./images/avatars/'.stripslashes(htmlspecialchars($data['membre_avatar'])).'">'.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a>
                            <div class="field">
                            <div class="ui checkbox">
                            <input type="checkbox" name="'.$data['membre_id'].'" id="'.$data['membre_id'].'">
                            <label for="'.$data['membre_id'].'">Débannir</label>
                            </div></div></div>';
                        }
                    echo '<button type="submit" class="ui positive right labeled icon button">Unban <i class="legal icon"></i></button>
                    </form>';
                }
            else echo '<div class="ui divider"></div><div class="ui info message"><div class="header">Information</div><p>There are no members currently banned.</p></div>';
            $query->CloseCursor();
            
            break;

        default; // If the action is not completed, we display the menu

        echo '<div class="ui container">
        <div class="ui basic segment">
            <h3 class="ui header"><i class="users yellow icon"></i>
                <div class="content"> Members Administration
                    <div class="sub header">Create, edit forums and categories and their positions. And the rights to view or post on a forum/topic.</div>
                </div>
            </h3>
            <div class="ui clearing divider"></div>
            <div class="ui three column grid">
                <div class="column">
                    <div class="ui fluid green link card">
                        <a href="./admin.php?cat=membres&action=edit"><br>
                            <div class="content">
                                <h2 class="ui center aligned icon header"><i class="circular edit icon"></i> Edit profile of a member</h2>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="column">
                <div class="ui fluid violet link card">
                    <a href="./admin.php?cat=membres&action=droits"><br>
                        <div class="content">
                            <h2 class="ui center aligned icon header"><i class="circular shield icon"></i> Edit the rights of a member</h2>
                        </div>
                    </a>
                </div>
                </div>
                <div class="column">
                    <div class="ui fluid olive link card">
                        <a href="./admin.php?cat=membres&action=ban"><br>
                            <div class="content">
                                <h2 class="ui center aligned icon header"><i class="circular legal icon"></i> Ban/Unban a member</h2>
                            </div>
                        </a>
                    </div>
                </div>
            </div>';
            
            break;
        }
break;
default; //cat n'est pas remplie, on affiche le menu général
echo '<div class="ui container">
<div class="ui basic segment">
    <h3 class="ui header"><i class="dashboard violet icon"></i>
        <div class="content"> Dashboard
            <div class="sub header">Welcome to the homepage of your administration, <strong>'.$_SESSION['pseudo'].'</strong> <i class="smile icon"></i></div>
        </div>
    </h3>
    <div class="ui clearing divider"></div>
    <div class="ui three column grid">
        <div class="column">
            <div class="ui fluid red link card">
                <a href="./admin.php?cat=config"><br>
                    <div class="content">
                        <h2 class="ui center aligned icon header"><i class="circular settings icon"></i> Site/Forum settings</h2>
                    </div>
                </a>
            </div>
        </div>
        <div class="column">
        <div class="ui fluid blue link card">
            <a href="./admin.php?cat=forum"><br>
                <div class="content">
                    <h2 class="ui center aligned icon header"><i class="circular laptop icon"></i> Forums Management</h2>
                </div>
            </a>
        </div>
        </div>
        <div class="column">
            <div class="ui fluid yellow link card">
                <a href="./admin.php?cat=membres"><br>
                    <div class="content">
                        <h2 class="ui center aligned icon header"><i class="circular users icon"></i> Members Administration</h2>
                    </div>
                </a>
            </div>
        </div>
    </div>';
            
echo '</div></div></div>';
echo '</div></div></body></html>';
break;
}
?>