<?php
session_start();
//Para destruir una variable en específico
unset($_SESSION['sesion_system_03']);
unset($_SESSION['sesion_system_07']);
unset($_SESSION['sesion_system_06']);
unset($_SESSION['sesion_system_03_modo']);
session_destroy();
?>
<SCRIPT LANGUAGE=JavaScript>
		window.location = "../"
</SCRIPT>