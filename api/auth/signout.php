<?php

session_start();
include_once "../../utils.php";

unset($_SESSION["id"]);
redirect("../../index.php", success_message: "Logged out successfully");