
<!DOCTYPE html>
<html>
<head>
<title>Form Validation</title>
<script>
function validate(form) {
    let fail = "";
    fail += validateInput(form.field.value, form.file.value);
    if (fail === "") {
        return true;
    } else {
        alert(fail);
        return false;
    }
}

function validateInput(field, file) {
    if (field === "" && (!file || file === "")) {
        return "Must have either a text input or a file uploaded.\n";
    }
    return "";
}
</script>
</head>


<?php
// Aung Paing Soe and Yeng Her
// 12.8.24
// CS174 Final Decryptiod

// Miscellaneous setup
require_once 'init.php';

// Funct to destroy session
function destroy_session_and_data(){
    $_SESSION = []; 
    setcookie(session_name(), "", time()-MONTH, '/');
    session_destroy();
}

// Open connection to database
try {
    $conn = new mysqli($hn, $un, $pw, $db);
} catch (Exception $e) {
    die(ERROR_MESSAGE);
}

// Makes sure someone can't skip to home page
if (!isset($_SESSION["auth"])){   
    // Redirect to the registration page
    destroy_session_and_data();
    session_regenerate_id();
    header("Location: registration.php");
    die();
}

echo "Welcome! Please submit a '.txt' file or type in the text box. <br>Please only do one at a time, if you choose to do both, the file will have priority";

// Form to read input and en/decrypt
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

$action = isset($_POST['action']) ? mysql_entities_fix_string($conn, $_POST['action']) : '';

if ($action === "Encrypt") {
    $cipher = mysql_entities_fix_string($conn, $_POST['cipher']);
    echo "Encrypting with cipher: $cipher<br>";

    if (isset($_POST['file'])) {
        $content = mysql_entities_fix_string($conn, $_FILES['field']);
        if (mysql_entities_fix_string($conn, $_FILES['filename']['type']) == 'text/plain') {
            $fileName = mysql_entities_fix_string($conn, $_FILES['filename']['tmp_name']);
        }else{
            die ("File must be type .txt!");
        }

        if (!is_uploaded_file($fileName)) {
            die("Error uploading the file. Please try again.");
        }else{
            $fileContent = mysql_entities_fix_string($conn, file_get_contents($fileName));
            $content = preg_replace('/\r\n|\r|\n/', '<br>', html_entity_decode($fileContent));    
        }
        Encrypt($content, $cipher, $conn);
    }

    else if (isset($_POST['field']) && isset($_POST['cipher'])) {
        $content = mysql_entities_fix_string($conn, $_POST['field']);
        Encrypt($content, $cipher, $conn);
    }
}

else if ($action === "Decrypt") {
    echo "Decrypting with cipher: $cipher<br>";
    // Perform decryption logic here
} elseif (isset($_POST['key'])) {
    echo "Invalid action.<br>";
}

$conn->close();

// Button to log out
echo <<<_END
        <form method="post" action="home.php" enctype="multipart/form-data">
                <input type="hidden" name="loginState" value="0"> <!-- Hidden input to maintain state -->
                <input type="submit" name="logOut" value="Log Out">
            </form>
        _END;

// Check if the log out button was clicked
if (isset($_POST['logOut'])) {
    // Redirect to the registration page
    destroy_session_and_data();
    session_regenerate_id();
    header("Location: registration.php");
    exit();
}

Function Encrypt($content, $cipher, $conn){
    $time = date('Y-m-d H:i:s'); // Current timestamp

    if ($cipher == "Simple Substitution"){
        simpleSubstitution();
    } else if ($cipher == "Double Transposition"){
        doubleTransposition();
    } else if ($cipher == "RC4"){
        RC4();
    }else{
        die(ERROR_MESSAGE);
    }

    try {
        // Insert the time, input, and cipher into the database
        $stmt = $conn->prepare("INSERT INTO cipher_logs (time, input, cipher) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $time, $content, $cipher);
        $stmt->execute();
        echo "Data saved successfully!<br>";
    } catch (Exception $e) {
        die(ERROR_MESSAGE);
    }
}

//Functions for en/decryption:
Function simpleSubstitution(){

}
Function doubleTransposition(){
    
}
Function RC4(){
    
}
?>
