Bonjour,<br>
Vous trouverez ci-dessous joint le lien pour vous connecter à votre proposition commerciale :<br>
<?php
if(isset($link))
    echo $link;
else
	$failed = true;
?>
<br>
Bonne journée !
<?php
if(isset($id))
    echo "<img src=/mail/img/".$id.".png>";
else
	$failed = true;
?>