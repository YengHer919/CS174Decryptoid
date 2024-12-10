<!DOCTYPE html>
<html>
<head>
<title>Form Validation</title>
<script>
function validate(form) {
    let fail = "";
    fail += validateUsername(form.user.value);
    fail += validateID(form.id.value);
    fail += validateEmail(form.email.value);
    fail += validatePassword(form.passwd.value);
    if (fail == "") return true;
    else { alert(fail); return false; }
}
function $(id) {
    return document.getElementById(id);
}
function validateUsername(field){
    if (field == "") return "No Username was entered.\n"
    else if (/[^a-zA-Z0-9_-]/.test(field))
        return "Only a-z, A-Z, 0-9, - and _ allowed in Usernames.\n"
    return ""
}
function validateID(field){
    if (field == "") return "No ID was entered.\n"
    else if (field.length != 9) // no ID's shorter than 9
        return "ID's must be 9 characters.\n"
    else if (/[^0-9]/.test(field))
        return "0-9 allowed in ID's.\n"
    return ""
}
function validateEmail(field) {
    if (field == "") 
        return "No Email was entered.\n";
    else if (!field.endsWith(".edu") || !field.endsWith(".com") || !field.endsWith(".org") || !field.endsWith(".gov") || !field.includes("@")) // Check for the correct domain
        return "Email must be properly formatted.\n";
    else if (/[^a-zA-Z0-9._@-]/.test(field))
        return "Invalid characters in Email.\n";
     else if (field.length < 10) // ensure user can't submit "@sjsu.edu" by itself
        return "Email can't be empty.\n"
    return "";
}
function validatePassword(field)
{
    if (field == "") 
        return "No Password was entered.\n"
    else if (field.length < 6) // no passwords shorter than 6
        return "Passwords must be at least 6 characters.\n"
    else if (!/[a-z]/.test(field) || ! /[A-Z]/.test(field) ||!/[0-9]/.test(field))
        return "Passwords require at least one lowercase and uppercase letter and at least one number.\n"
    return ""
}
</script>
</head>

<?php
// Aung Paing Soe and Yeng Her
// 12.8.24
// Final Decryptiod

// Miscellaneous setup
require_once 'init.php';

// HTML to log in
echo "Log in";
echo <<<_END
        <form method="post" action="registration.php" enctype="multipart/form-data"><pre>
            Enter Name: <input type="text" name="username" required>
            Enter Password: <input type="text" name="password" required>
            <input type="submit" value="Log in">
        </pre></form>
        _END;

// If the username and password are input
if (isset($_POST['username']) && isset($_POST['password'])) {
    // Open connection to database
    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }

    // Set variables to sanitized inputs
    $un_temp = mysql_entities_fix_string($conn, $_POST['username']);
    $pw_temp = mysql_entities_fix_string($conn, $_POST['password']);

   // Check to find user in database 
    try {
        $stmt = $conn->prepare("SELECT * FROM credentials WHERE name = ?");
        $stmt->bind_param("s", $un_temp);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }
    
    // If user exsists
    if ($result->num_rows) {
        $row = $result->fetch_array(MYSQLI_NUM);

        // Verify Password
        $token = password_verify($pw_temp, $row[3]);
        if ($token) { // If passwords match, store all data into session
            $_SESSION['auth'] = 1;
            echo "successful log in";
            //session_regenerate_id();
            header("Location: home.php");
            exit();
        // If user exsists and password is wrong, let the user know
        }else{ echo("Invalid username/password combination <br>"); echo "__________________________________________________________<br><br>";}
    // If the user doesn't exsist, let the user know
    }else{
        echo ("User does not exsist");
        echo "__________________________________________________________<br><br>";
    }
    $result->close();
    $stmt->close();
    $conn -> close();
// If the fields aren't full, page will ask user to do so
}else{
    echo "Please enter your user name and password <br>";
    echo "__________________________________________________________<br><br>";
}


// Form to register
echo "Sign up";
echo <<<_END
        <form method="post" action="registration.php" enctype="multipart/form-data" onsubmit="return validate(this)"><pre>
            Enter Name: <input type="text" name="user" required>
            Enter ID: <input type="text" name="id" required>
            Enter Email: <input type="text" name="email" required>
            Enter Password: <input type="text" name="passwd" required>
            <input type="submit" value="Sign in">
        </pre></form>
        _END;

// Check to make sure salts are unique
function saltVerified($conn, $s1, $s2){
    try {
        $stmt = $conn->prepare("SELECT * FROM credentials WHERE salt1 = ?");
        $stmt->bind_param("s", $s1);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }

    if ($result->num_rows) {
        return false;    
    }
    $result->close();
    $stmt->close(); 

    try {
        $stmt = $conn->prepare("SELECT * FROM credentials WHERE salt2 = ?");
        $stmt->bind_param("s", $s2);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }
    
    if ($result->num_rows) {
        return false;    
    }
    $result->close();
    $stmt->close(); 
    
    return true;
}

// Check if all fields have been entered
if (isset($_POST['user']) && isset($_POST['id']) && isset($_POST['email']) && isset($_POST['passwd'])) {
    // Open connection to database
    try {
        $conn = new mysqli($hn, $un, $pw, $db);
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }
    
    // Get info from fields
    $userName = mysql_entities_fix_string($conn, $_POST['user']);
    $id = mysql_entities_fix_string($conn, $_POST['id']);
    $email = mysql_entities_fix_string($conn, $_POST['email']);
    $password =  mysql_entities_fix_string($conn, $_POST['passwd']);
    $hashPass = password_hash($password, PASSWORD_BCRYPT);
    // Prepare to insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO credentials (name, id, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sissss", $userName, $id, $email, $hashPass);

    // Execute the statement and check for success
    try{
        $stmt->execute();
        echo "User registered successfully! Please log in <br>";
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }    
    $stmt->close(); 
    $conn -> close(); 
}else{
    echo "Please enter your user name and password";
}

?>

