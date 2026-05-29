<?php
require 'config.php';
unset($_SESSION['php_crud_user']);
session_destroy();
header('Location: login.php');
exit;
