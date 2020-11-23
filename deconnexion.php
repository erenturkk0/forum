<?php
session_start();
$titre="Sign Out";
include("includes/identifiants.php");
include("includes/debut.php");
include("includes/menu.php");
session_destroy();
$query=$db->prepare('DELETE FROM forum_whosonline WHERE online_id = :id');
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->execute();
$query->CloseCursor();
// Update last visit
$temps = time();
$query=$db->prepare('UPDATE forum_membres SET membre_derniere_visite = :temps WHERE membre_id = :id');
$query->bindValue(':id',$id,PDO::PARAM_INT);
$query->bindValue(':temps', $temps, PDO::PARAM_INT);
$query->execute();
$query->CloseCursor();

if ($id==0) erreur(ERR_IS_NOT_CO);

echo '<div class="ui main container animated fadeIn"><div class="ui raised clearing segments"><div class="ui center aligned segment"><h1 class="ui center aligned icon header"><i class="circular checkmark green icon"></i><div class="content">You are now offline<div class="sub header"><a href="./index.php">Back to Homepage</a> or <a href="'.htmlspecialchars($_SERVER['HTTP_REFERER']).'">Return to previous page</a>.</div></div></h1></div></body></html>';
