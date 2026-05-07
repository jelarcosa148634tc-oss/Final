<?php
session_start();
if(!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") { header("location: login.php"); exit; }
include_once "student_header.php";
?>
<div style="padding:40px; text-align:center; font-family:sans-serif;">
    <h2 style="color:#003366;">My Borrowed Books</h2>
    <p style="color:#666;">Your personal borrowing history will appear here soon.</p>
</div>