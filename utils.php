<?php

function redirect(string $url, ?string $success_message = null, ?string $error_message = null): void
{
    header("Location: $url");
    if ($success_message) {
        $_SESSION["success_message"] = $success_message;
    }
    if ($error_message) {
        $_SESSION["error_message"] = $error_message;
    }
    die();
}

function read_success_message(): ?string
{
    if (isset($_SESSION["success_message"])) {
        $value = $_SESSION["success_message"];
        unset($_SESSION["success_message"]);
        return $value;
    }
    return null;
}

function read_error_message(): ?string
{
    if (isset($_SESSION["error_message"])) {
        $value = $_SESSION["error_message"];
        unset($_SESSION["error_message"]);
        return $value;
    }
    return null;
}

class AppException extends Exception {
}