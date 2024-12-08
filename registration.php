<!DOCTYPE html>
<html>
<head>
<title>Form Validation</title>
<script>
    // Function to validate the form inputs on submission
    function validate(form) {
        let fail = "";
        fail += validateUsername(form.user.value); // Validate the username field
        fail += validateID(form.id.value); // Validate the ID field
        fail += validateEmail(form.email.value); // Validate the email field
        fail += validatePassword(form.passwd.value); // Validate the password field
        if (fail == "") return true; // Proceed if no validation errors
        else { alert(fail); return false; } // Alert the errors and prevent form submission
    }

    // Shortcut function to get an element by its ID
    function $(id) {
        return document.getElementById(id);
    }

    // Validate the username for specific character constraints
    function validateUsername(field){
        if (field == "") return "No Username was entered.\n";
        else if (/[^a-zA-Z0-9_-]/.test(field)) // Allow only alphanumeric characters, hyphens, and underscores
            return "Only a-z, A-Z, 0-9, - and _ allowed in Usernames.\n";
        return "";
    }

    // Validate the ID for numeric input and length
    function validateID(field){
        if (field == "") return "No ID was entered.\n";
        else if (field.length != 9) // Require exactly 9 characters
            return "ID's must be 9 characters.\n";
        else if (/[^0-9]/.test(field)) // Allow only numeric characters
            return "0-9 allowed in ID's.\n";
        return "";
    }

    // Validate the email for proper formatting
    function validateEmail(field) {
        if (field == "") 
            return "No Email was entered.\n";
        else if (!field.endsWith(".edu") && !field.endsWith(".com") && !field.endsWith(".org") && !field.endsWith(".gov") || !field.includes("@")) 
            return "Email must be properly formatted.\n"; // Ensure correct domain and @ symbol
        else if (/[^a-zA-Z0-9._@-]/.test(field)) // Allow only valid characters
            return "Invalid characters in Email.\n";
        else if (field.length < 10) 
            return "Email can't be empty.\n"; // Prevent submitting domain-only emails
        return "";
    }

    // Validate the password for complexity
    function validatePassword(field) {
        if (field == "") return "No Password was entered.\n";
        else if (field.length < 6) // Ensure a minimum length of 6
            return "Passwords must be at least 6 characters.\n";
        else if (!/[a-z]/.test(field) || !/[A-Z]/.test(field) || !/[0-9]/.test(field)) 
            return "Passwords require at least one lowercase and uppercase letter and at least one number.\n"; // Enforce complexity requirements
        return "";
    }
</script>
</head>

<?php
// Aung Paing Soe and Yeng Her
// 12.8.24
// Final Decryptiod - PHP file for user login and registration

// Include initialization settings
require_once 'init.php';

// HTML form for user login
echo "Log in";
echo <<<_END
        <form method="post" action="registration.php" enctype="multipart/form-data"><pre>
            Enter Name: <input type="text" name="username" required>
            Enter Password: <input type="text" name="password" required>
            <input type="submit" value="Log in">
        </pre></form>
        _END;

// Process login if username and password are submitted
if (isset($_POST['username']) && isset($_POST['password'])) {
    try {
        $conn = new mysqli($hn, $un, $pw, $db); // Open database connection
    } catch (Exception $e) {
        die(ERROR_MESSAGE); // Handle connection error
    }

    // Sanitize inputs for security
    $un_temp = mysql_entities_fix_string($conn, $_POST['username']);
    $pw_temp = mysql_entities_fix_string($conn, $_POST['password']);

    try {
        // Query database for user credentials
        $stmt = $conn->prepare("SELECT * FROM credentials WHERE name = ?");
        $stmt->bind_param("s", $un_temp);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (Exception $e) {
        die(ERROR_MESSAGE); // Handle query error
    }
    
    if ($result->num_rows) {
        $row = $result->fetch_array(MYSQLI_NUM);
        $salt1 = $row[4]; $salt2 = $row[5]; // Retrieve salts
        $token = hash('ripemd128', "$salt1$pw_temp$salt2"); // Generate hashed password
        if ($token == $row[3]) { // Match password
            $_SESSION['auth'] = 1; // Set session authentication
            header("Location: home.php"); // Redirect to home
            exit();
        } else { 
            echo "Invalid username/password combination.<br>";
        }
    } else {
        echo "User does not exist.<br>";
    }
    $result->close();
    $stmt->close();
    $conn->close();
} else {
    echo "Please enter your user name and password.<br>";
}

// Form for user registration
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

// Check if salts are unique
function saltVerified($conn, $s1, $s2){
    try {
        $stmt = $conn->prepare("SELECT * FROM credentials WHERE salt1 = ?");
        $stmt->bind_param("s", $s1);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }

    if ($result->num_rows) return false; // Salt1 already exists
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
    
    if ($result->num_rows) return false; // Salt2 already exists
    $result->close();
    $stmt->close(); 
    
    return true; // Both salts are unique
}

// Handle user registration
if (isset($_POST['user']) && isset($_POST['id']) && isset($_POST['email']) && isset($_POST['passwd'])) {
    try {
        $conn = new mysqli($hn, $un, $pw, $db); // Open database connection
    } catch (Exception $e) {
        die(ERROR_MESSAGE); // Handle connection error
    }
    
    // Get sanitized user input
    $userName = mysql_entities_fix_string($conn, $_POST['user']);
    $id = mysql_entities_fix_string($conn, $_POST['id']);
    $email = mysql_entities_fix_string($conn, $_POST['email']);
    $password =  mysql_entities_fix_string($conn, $_POST['passwd']);

    // Generate random salts
    $salt1 = bin2hex(random_bytes(random_int(1, 8))); 
    $salt2 = bin2hex(random_bytes(random_int(1, 8)));
    $attempts = 0;

    // Ensure salts are unique
    while(!saltVerified($conn, $salt1, $salt2)){
        $salt1 = bin2hex(random_bytes(random_int(1, 8))); 
        $salt2 = bin2hex(random_bytes(random_int(1, 8)));
        $attempts++;
        if ($attempts >= 10) die("Failed to generate unique salts.");
    }

    $hashPass = hash('ripemd128', "$salt1$password$salt2"); // Hash password with salts

    // Insert new user into the database
    try {
        $stmt = $conn->prepare("INSERT INTO credentials (name, id, email, password, salt1, salt2) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissss", $userName, $id, $email, $hashPass, $salt1, $salt2);
        $stmt->execute();
        echo "User registered successfully! Please log in.<br>";
    } catch (Exception $e) {
        die(ERROR_MESSAGE); // Handle insertion error
    }
    $stmt->close(); 
    $conn->close();
} else {
    echo "Please enter all required fields.<br>";
}

?>
