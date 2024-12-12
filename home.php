<?php
    // Aung Paing Soe and Yeng Her
    // 12.8.24
    // CS174 Final Decryptiod

    // Miscellaneous setup
    require_once 'init.php';

    echo <<<_END
        <html> <head> <title> Decryptoid Signup and Login Page </title> 
        <script src="home_validation.js"> </script> 
        <h1> Decryptoid </h1> </head>
    _END;

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

    $action = isset($_POST['action']) ? sanitization($conn, $_POST['action']) : '';

    if ($action === "Encrypt") {
        $cipher = sanitization($conn, $_POST['cipher']);
        $key = sanitization($conn, $_POST['key']);

        echo "Encrypting with cipher: $cipher<br>";

        if (isset($_FILES['file'])) {
            $content = "";
            if (sanitization($conn, $_FILES['file']['type']) == 'text/plain') {
                $fileName = sanitization($conn, $_FILES['file']['tmp_name']);
            }else{
                die ("File must be type .txt!");
            }

            if (!is_uploaded_file($fileName)) {
                die("Error uploading the file. Please try again.");
            }else{
                $fileContent = preg_replace('/\r\n|\r|\n/', '\\n', file_get_contents($fileName));
                $content = sanitization($conn, $fileContent);
            }
            Encrypt($content, $cipher, $conn, $key);
        }

        else if (isset($_POST['field']) && isset($_POST['cipher'])) {
            $content = sanitization($conn, $_POST['field']);
            Encrypt($content, $cipher, $conn, $key);
        }
    }

    if ($action === "Decrypt") {
        $cipher = sanitization($conn, $_POST['cipher']);
        $key = sanitization($conn, $_POST['key']);

        echo "Decrypting with cipher: $cipher<br>";

        if (isset($_FILES['file'])) {
            $content = "";
            if (sanitization($conn, $_FILES['file']['type']) == 'text/plain') {
                $fileName = sanitization($conn, $_FILES['file']['tmp_name']);
            }else{
                die ("File must be type .txt!");
            }

            if (!is_uploaded_file($fileName)) {
                die("Error uploading the file. Please try again.");
            }else{
                $fileContent = preg_replace('/\r\n|\r|\n/', '\\n', file_get_contents($fileName));
                $content = sanitization($conn, $fileContent);
            }
            Decrypt($content, $cipher, $conn, $key);
        }
        else if (isset($_POST['field']) && isset($_POST['cipher'])) {
            $content = sanitization($conn, $_POST['field']);
            Decrypt($content, $cipher, $conn, $key);
        }

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

    Function Encrypt($content, $cipher, $conn, $key){
        $time = date('Y-m-d H:i:s'); // Current timestamp

        if ($cipher == "simple_substitution"){
            $encrypted = simpleSubstitution($content, $key);
        } else if ($cipher == "double_transposition"){
            doubleTransposition($key);
        } else if ($cipher == "rc4"){
            RC4($key);
        }else{
            die(ERROR_MESSAGE);
        }

        try {
            // Insert the time, input, and cipher into the database
            $stmt = $conn->prepare("INSERT INTO cipher_logs (time, input, cipher, cipher_key) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $time, $content, $cipher, $key);
            $stmt->execute();
            echo "Data saved successfully!<br>";
        } catch (Exception $e) {
            die(ERROR_MESSAGE);
        }
        echo <<<_END
            <h2> Result: </h2>
            $encrypted
        _END;
    }

    Function Decrypt($content, $cipher, $conn, $key){
        $time = date('Y-m-d H:i:s'); // Current timestamp

        if ($cipher == "simple_substitution"){
            $decrypted = simpleSubstitution($content, $key);
        } else if ($cipher == "double_transposition"){
            doubleTranspositionDecrypt($key);
        } else if ($cipher == "rc4"){
            RC4Decrypt($key);
        }else{
            die(ERROR_MESSAGE);
        }

        try {
            // Insert the time, input, and cipher into the database
            $stmt = $conn->prepare("INSERT INTO cipher_logs (time, input, cipher, cipher_key) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $time, $content, $cipher, $key);
            $stmt->execute();
            echo "Data saved successfully! <br>";
        } catch (Exception $e) {
            die(ERROR_MESSAGE);
        }
        echo <<<_END
            <h2> Result: </h2>
            $decrypted
        _END;
    }

    //Functions for en/decryption:
    Function simpleSubstitution($text, $key){
        $plaintext = explode("\\n", strtolower($text));
        $final_text = "";
        $divided_key = str_split(strtoupper($key));
        $key_index = 0;
        $key_table = array();
        foreach(range('a','z') as $letter){
            $key_table[$letter] = $divided_key[$key_index];
            $key_index++; 
        }
        for ($i = 0; $i < count($plaintext); $i++){
            $line = $plaintext[$i];
            $final_line = "";
            for ($j = 0; $j < strlen($line); $j++){
                $char = $line[$j];
                if (ctype_alpha($char)){
                    $final_line .= $key_table[$char];
                } 
                else {
                    $final_line .= $char;
                }
            }
            $final_text .= $final_line . "<br>";
        }
        return $final_text;

    }
    Function doubleTransposition($key){
        
    }
    Function RC4($key){
        
    }

    Function simpleSubstitutionDecrypt($key){

    }
    Function doubleTranspositionDecrypt($key){
        
    }
    Function RC4Decrypt($key){
        
    }

?>
