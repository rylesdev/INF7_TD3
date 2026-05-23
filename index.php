<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: http://' . $_SERVER['HTTP_HOST'] . $base . '/public/');
exit;
