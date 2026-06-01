<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("BASE_URL", "/Proyecto_cocha");


function require_login()
{
    if (!isset($_SESSION["persona_id"]) || empty($_SESSION["persona_id"])) {
        header("Location: " . BASE_URL . "/login/");
        exit;
    }
}


function redirect_if_authenticated()
{
    if (isset($_SESSION["persona_id"]) && !empty($_SESSION["persona_id"])) {
        header("Location: " . BASE_URL . "/dashboard.php");
        exit;
    }
}


function require_admin()
{
    require_login();

    if (!isset($_SESSION["persona_rol"]) || $_SESSION["persona_rol"] !== "administrador") {
        header("Location: " . BASE_URL . "/dashboard.php");
        exit;
    }
}