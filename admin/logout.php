<?php
// PitWall F1 CMS - Logout
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

Auth::logout();

header('Location: login.php');
exit;
