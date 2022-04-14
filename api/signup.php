<?php
session_start();

mysqli_report((MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX) | MYSQLI_REPORT_STRICT);
try {
    $mysql = new mysqli("localhost", "admin", "r#4adQti94B", "movie_review_database");
} catch (Exception $e) {
    error_log($e);
}

// email, password
$body = json_decode(file_get_contents("php://input"));

// TODO: Sign up the user
// 1. Input validation
//    a. Is the email a valid email address?
//    b. Is the password secure (8+ characters)
// 2. Check if the email is already in use
// 3. Create a user in the database
//      NOTE: Must hash password
// 4. Login

// Is the email invalid? (i.e. does it have no '@' character, etc...)
if (filter_var($body->email, FILTER_VALIDATE_EMAIL) == false) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Email is invalid."]
    );
    exit();
}

// Is the password too weak?
if (strlen($body->password) < 8) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Password must be 8+ characters."]
    );
    exit();
}

$query = "
    SELECT *
    FROM
        users
    WHERE
        email = ?
";
$statement = $mysql->prepare($query);
$statement->execute([$body->email]);
$rows = $statement->get_result()->fetch_all();

// Is the email already in use?
if (count($rows) > 0) {
    http_response_code(400);
    echo json_encode(
        ["message" => "Email already in use."]
    );
    exit();
}

// Insert the user...
$query = "
    INSERT INTO
        users
    VALUES( ?, ? )
";
$statement = $mysql->prepare($query);
$statement->execute($body->email, password_hash($body->password, PASSWORD_DEFAULT));

// Log the user in (check for this later)...
$_SESSION["email"] = $body->email;

http_response_code(200);
echo json_encode(
    ["message" => "Signup successful."]
);
exit();
