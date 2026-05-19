<?php
session_start();
session_destroy();
header('Location: /intel/login.php');
exit;
