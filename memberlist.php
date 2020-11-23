<?php
session_start();
$titre="List of members";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");

// From here, we will count the number of members to display only the first 15
$query=$db->query('SELECT COUNT(*) AS nbr FROM forum_membres');
$data = $query->fetch();
$total = $data['nbr'] +1;
$query->CloseCursor();
$MembreParPage = 15;
$NombreDePages = ceil($total / $MembreParPage);

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments">';
echo '<div class="ui center aligned segment"><h3 class="ui header animated rubberBand">Our members</h3></div>',
    '<div class="ui '.$config['forum_color'].' padded segment"><center><div class="ui breadcrumb"><a href="./index.php" class="section">Home</a><div class="divider"> / </div><div class="active section">List of members</div></div></center><div class="ui divider"></div>';


// Number of pages
$page = (isset($_GET['page']))?intval($_GET['page']):1;

// Pagination begins
echo '<center><div class="ui tiny pagination menu"><a class="icon item"><i class="left arrow icon"></i></a>';
echo get_list_page($page, $NombreDePages, './memberlist.php?page='.$premier);
echo'<a class="icon item"><i class="right arrow icon"></i></a></div></center>';

$premier = ($page - 1) * $MembreParPage;


// Sorting
$convert_order = array('membre_pseudo', 'membre_inscrit', 'membre_post', 'membre_derniere_visite'); 
$convert_tri = array('ASC', 'DESC');

// We get the value of s
if (isset ($_POST['s'])) $sort = $convert_order[$_POST['s']];
else $sort = $convert_order[0];

// We get the value of t
if (isset ($_POST['t'])) $tri = $convert_tri[$_POST['t']];
else $tri = $convert_tri[0];

echo '<div class="ui divider hidden"></div>';
echo '<form action="memberlist.php" class="ui form" method="post">
<h4 class="ui dividing header"><i class="users '.$config['forum_color'].' icon"></i> Table showing members</h4>
        <select name="s" id="s" class="ui dropdown">
            <option value="0" name="0">Username</option>
            <option value="1" name="1">Registration</option>
            <option value="2" name="2">Messages</option>
            <option value="3" name="3">Last visite</option>
        </select>
        <select name="t" id="t" class="ui dropdown">
            <option value="0" name="0">Ascending</option>
            <option value="1" name="1">Descending</option>
        </select>
        <button type="submit" class="ui basic right labeled icon button" value="Sort">Sort <i class="sort icon"></i></button>
</form>';
echo "<script>
$('.ui.dropdown')
.dropdown()
;
</script>";


// Request
$query = $db->prepare('SELECT membre_id, membre_pseudo, membre_inscrit, membre_post, membre_derniere_visite, membre_avatar, online_id FROM forum_membres LEFT JOIN forum_whosonline ON online_id = membre_id ORDER BY '.$sort.', online_id '.$tri.' LIMIT :premier, :membreparpage');
$query->bindValue(':premier',$premier,PDO::PARAM_INT);
$query->bindValue(':membreparpage',$MembreParPage, PDO::PARAM_INT);
$query->execute();

if ($query->rowCount() > 0)
    {
        echo '<table class="ui celled striped inverted table">
        <thead>
            <tr>
                <th><i class="address card icon"></i> Username</th>
                <th><i class="comment icon"></i> Message(s)</th>
                <th><i class="checked calendar icon"></i> Registered since</th>
                <th><i class="history icon"></i> Last visit</th>
                <th><blink><i class="heartbeat icon"></i></blink> Statut</th>
            </tr>
        </thead>
        <tbody>';
    // Start the loop
    while ($data = $query->fetch())
        {
            echo '<tr>
            <td class="center aligned"><a href="./voirprofil.php?m='.$data['membre_id'].'&action=consulter" class="ui label"><img class="ui avatar image" src="./images/avatars/'.stripslashes(htmlspecialchars($data['membre_avatar'])).'" /> '.stripslashes(htmlspecialchars($data['membre_pseudo'])).'</a></td>
            <td class="center aligned"><div class="ui basic '.$config['forum_color'].' circular label">'.$data['membre_post'].'</div></td>
            <td class="center aligned"><div class="ui basic label">'.date('d/m/Y',$data['membre_inscrit']).'</div></td>
            <td class="center aligned"><div class="ui basic label">'.date('d/m/Y',$data['membre_derniere_visite']).'</div></td>';
            if (empty($data['online_id'])) echo '<td class="center aligned"><div class="ui red label">Offline</div></td>';
            else echo '<td class="center aligned"><div class="ui green label">Online</div></td>';
            echo '</tr>';
        }
       $query->CloseCursor();
  echo '</tbody>
  </table>';
    }
else // If there is no member
    {
        echo '<div class="ui divider"></div><div class="ui icon info message"><i class="frown icon"></i><div class="content"><div class="header">Oops!</div><p>This forum currently has no members.</p></div></div>';
    }

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