<?php
session_start();

if ($_SESSION['sesion_vista']!=0)
{
unset($_SESSION['sesion_vista']);
$_SESSION['sesion_vista']=0;
}
else
{
unset($_SESSION['sesion_vista']);
$_SESSION['sesion_vista']=1;
}

header('Location: ../index.php');
?>
