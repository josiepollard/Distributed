<?php
session_start();

//destory session data
session_destroy();

//redirect back to login page
header("Location: login.php");
exit;