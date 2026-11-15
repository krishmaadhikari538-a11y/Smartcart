<?php
// Starts the session once for the whole application.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL of the project - update the folder name if you rename the project folder.
define('BASE_URL', '/smartcart/');
