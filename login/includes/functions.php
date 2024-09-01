<?php
function connectDB() {
    // Replace with your actual database connection details
    $dbHost = "localhost";
    $dbUsername = "admin";
    $dbPassword = "admin";
    $dbName = "users";

    $conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function checkLogin($username, $password) {
    $conn = connectDB();

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }

    $conn->close();
}
