<?php
require_once 'config/config.php';

session_destroy();
header('Location: /ram/login.php');
exit(); 