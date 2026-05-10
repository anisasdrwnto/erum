<?php
require_once 'db.php';
sessionStart();
session_destroy();
header('Location: login.php?error=logout');
exit;
