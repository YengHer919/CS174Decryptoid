<!DOCTYPE html>
<html>
<head>
<title>Form Validation</title>
<script>
// Function to validate the form before submission
function validate(form) {
    let fail = ""; // Initialize the error message
    fail += validateInput(form.field.value, form.file.value); // Check if input conditions are met
    if (fail === "") { // If no errors
        return true; // Allow form submission
    } else { // If errors exist
        alert(fail); // Show the error message
        return false; // Prevent form submission
    }
}

// Function to check if at least one input (text or file) is provided
function validateInput(field, file) {
    if (field === "" && (!file || file === "")) { // If both text and file are empty
        return "Must have either a text input or a file uploaded.\n"; // Return error message
    }
    return ""; // Return no error
}
</script>
</head>

<?php
// Aung Paing Soe and Yeng Her
// 12.8.24
// CS174 Final Decryption

// Miscellaneous setup
require_once 'init.php';

// Function to destroy the session and its data
function destroy_session_and_data(){
    $_SESSION = []; // Clear session data
    setcookie(session_name(), "", time()-MONTH, '/'); // Expire the session cookie
    session_destroy(); // End the session
}

// Open connection to the database
try {
    $conn = new mysqli($hn, $un, $pw, $db); // Establish connection using credentials
} catch (Exception $e) {
    die(ERROR_MESSAGE); // Handle connection errors
}

// Ensure unauthorized users cannot access the page directly
if (!isset($_SESSION["auth"])){   
    destroy_session_and_data(); // Clear session data
    session_regenerate_id(); // Generate a new session ID for security
    header("Location: registration.php"); // Redirect to the registration page
    die(); // Stop further script execution
}

// Display welcome message and instructions
echo "Welcome! Please submit a '.txt' file or type in the text box. <br>Please only do one at a time, if you choose to do both, the file will have priority";

// HTML form for input submission and encryption/decryption
echo <<<_END
<form method="post" action="home.php" enctype="multipart/form-data" onsubmit="return validate(this)">
    <pre>
        Insert Text: <input type="text" name="field">
        Insert File: <input type="file" name="file" size="10">
        Cipher: 
        <select name="cipher" required>
            <option value="simple_substitution">Simple Substitution</option>
            <option value="double_transposition">Double Transposition</option>
            <option value="rc4">RC4</option>
        </select>
        Insert Key: <input type="text" name="key" required>
        <input type="submit" name="action" value="Encrypt">
        <input type="submit" name="action" value="Decrypt">
    </pre>
</form>
_END;

$action = isset($_POST['action']) ? mysql_entities_fix_string($conn, $_POST['action']) : ''; // Get the action (Encrypt/Decrypt)

// Handle encryption logic
if ($action === "Encrypt") {
    $cipher = mysql_entities_fix_string($conn, $_POST['cipher']); // Get the cipher type
    echo "Encrypting with cipher: $cipher<br>";

    if (isset($_POST['file'])) { // If a file is uploaded
        $content = mysql_entities_fix_string($conn, $_FILES['field']);
        if (mysql_entities_fix_string($conn, $_FILES['filename']['type']) == 'text/plain') { // Check file type
            $fileName = mysql_entities_fix_string($conn, $_FILES['filename']['tmp_name']); // Get temporary file name
        }else{
            die("File must be type .txt!"); // Reject non-txt files
        }

        if (!is_uploaded_file($fileName)) { // Check if file was successfully uploaded
            die("Error uploading the file. Please try again."); // Handle upload errors
        }else{
            $fileContent = mysql_entities_fix_string($conn, file_get_contents($fileName)); // Read file contents
            $content = preg_replace('/\r\n|\r|\n/', '<br>', html_entity_decode($fileContent)); // Clean and format file content
        }
        Encrypt($content, $cipher, $conn); // Encrypt the content
    }

    else if (isset($_POST['field']) && isset($_POST['cipher'])) { // If text is provided
        $content = mysql_entities_fix_string($conn, $_POST['field']); // Get the text content
        Encrypt($content, $cipher, $conn); // Encrypt the text
    }
}

// Handle decryption logic
else if ($action === "Decrypt") {
    echo "Decrypting with cipher: $cipher<br>";
    // Perform decryption logic here
} elseif (isset($_POST['key'])) { // If an invalid action is detected
    echo "Invalid action.<br>";
}

$conn->close(); // Close the database connection

// Log-out button
echo <<<_END
        <form method="post" action="home.php" enctype="multipart/form-data">
                <input type="hidden" name="loginState" value="0"> <!-- Hidden input to maintain state -->
                <input type="submit" name="logOut" value="Log Out">
            </form>
        _END;

// Handle log-out logic
if (isset($_POST['logOut'])) {
    destroy_session_and_data(); // Clear session data
    session_regenerate_id(); // Generate a new session ID
    header("Location: registration.php"); // Redirect to the registration page
    exit(); // Stop further script execution
}

// Function to perform encryption and log to the database
Function Encrypt($content, $cipher, $conn){
    $time = date('Y-m-d H:i:s'); // Current timestamp

    if ($cipher == "Simple Substitution"){ // Handle Simple Substitution encryption
        simpleSubstitution();
    } else if ($cipher == "Double Transposition"){ // Handle Double Transposition encryption
        doubleTransposition();
    } else if ($cipher == "RC4"){ // Handle RC4 encryption
        RC4();
    }else{
        die(ERROR_MESSAGE); // Handle invalid cipher
    }

    try {
        // Save the encryption details to the database
        $stmt = $conn->prepare("INSERT INTO cipher_logs (time, input, cipher) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $time, $content, $cipher);
        $stmt->execute();
        echo "Data saved successfully!<br>";
    } catch (Exception $e) {
        die(ERROR_MESSAGE); // Handle database errors
    }
}

// Placeholder functions for encryption/decryption methods
Function simpleSubstitution(){
    // Logic for Simple Substitution
}
Function doubleTransposition(){
    // Logic for Double Transposition
}
Function RC4(){
    // Logic for RC4
}
?>
