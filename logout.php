<?php
session_start();
unset($_SESSION['client_session_id'], $_SESSION['client_access_code']);
header('Location: index.php');
exit;
