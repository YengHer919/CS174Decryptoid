<?php
    /* Aung Paing Soe and Yeng Her
       12/19/2024, CS 174-03
       Final Project - Decryptiod
    */

    // Miscellaneous setup
    require_once 'init.php';
    require_once 'ciphers.php';

    session_regenerate_id();

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
        header("Location: ./registration.php");
    }

    echo <<<_END
        <h2>Welcome!</h2>
        Please submit a '.txt' file or type in the text box. <br>
        Please only do one at a time, if you choose to do both, the file will have priority. <br>
        <h3><u>Key Instructions:</u></h3>
        <b>Simple Substitution</b> - String of length 26 with no repeating alphabets. <br>
        <b>Double Transposition</b> - Comma-separated (No spaces in between commas) numbered list of column/row permutations. Please make sure the permutations work for all the lines in the file. <br>
        <b>RC4</b> - String of alphanumeric characters only, which will be converted to hexadecimal numbers. <br> <br>
    _END;

    $cipher = isset($_POST['cipher']) ? sanitization($conn, $_POST['cipher']) : 'simple_substitution';

    // Form to read input and en/decrypt
    if ($cipher == "double_transposition"){
        echo <<<_END
        <form method="post" action="home.php" enctype="multipart/form-data" onsubmit="return validate(this)">
            <pre>
            Insert Text: <input type="text" name="field" id="field"> <br>
            Insert File: <input type="file" name="file" id="file" size="10"> <br>
            Cipher: $cipher<br>
                <input type="submit" name="cipher" value="simple_substitution"> <br>
                <input type="submit" name="cipher" value="double_transposition"> <br>
                <input type="submit" name="cipher" value="rc4"><br>
                Insert Key or Row Permutation for Double Transposition Here: <input type="text" name="key" id="key"> <br>
                Insert Column Permutation for Double Transposition Here: <input type="text" name="col_key" id="col_key"> <br>
                <input type="submit" name="action" value="Encrypt"> <br>
                <input type="submit" name="action" value="Decrypt">
            </pre>
        </form>
        _END;
    }else{
        echo <<<_END
        <form method="post" action="home.php" enctype="multipart/form-data" onsubmit="return validate(this)">
            <pre>
            Insert Text: <input type="text" name="field" id="field"> <br>
            Insert File: <input type="file" name="file" id="file" size="10"> <br>
            Cipher: $cipher<br>
                <input type="submit" name="cipher" value="simple_substitution"> <br>
                <input type="submit" name="cipher" value="double_transposition"> <br>
                <input type="submit" name="cipher" value="rc4"><br>
                Insert Key Here: <input type="text" name="key" id="key"> <br>
                <input type="submit" name="action" value="Encrypt"> <br>
                <input type="submit" name="action" value="Decrypt">
            </pre>
        </form>
        _END;
    }

    
    $action = isset($_POST['action']) ? sanitization($conn, $_POST['action']) : '';

    if ($action === "Encrypt") {
        $key = sanitization($conn, $_POST['key']);
        $col_key = isset($_POST['col_key']) ? sanitization($conn, $_POST['col_key']) : '';
        
        echo "Encrypting with cipher: $cipher<br>";
         // Check if file is set and if it isn't empty 
         if (isset($_FILES['file']) && sanitization($conn, $_FILES['file']['tmp_name']) != "") {
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
            Encrypt($content, $cipher, $conn, $key, $col_key);
        }

        else if (isset($_POST['field'])) {
            $content = sanitization($conn, $_POST['field']);
            Encrypt($content, $cipher, $conn, $key, $col_key);
        }
    }

    if ($action === "Decrypt") {
        $key = sanitization($conn, $_POST['key']);
        $col_key = sanitization($conn, $_POST['col_key']);

        echo "Decrypting with cipher: $cipher<br>";
          // Check if file is set and if it isn't empty 
         if (isset($_FILES['file']) && sanitization($conn, $_FILES['file']['tmp_name']) != "") {
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
            Decrypt($content, $cipher, $conn, $key, $col_key);
        }
        else if (isset($_POST['field'])) {
            $content = sanitization($conn, $_POST['field']);
            Decrypt($content, $cipher, $conn, $key, $col_key);
        }
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

    function Encrypt($content, $cipher, $conn, $key, $col_key){
        $time = date('Y-m-d H:i:s'); // Current timestamp

        if ($cipher == "simple_substitution"){
            $encrypted = simpleSubstitution($content, $key);
        } else if ($cipher == "double_transposition"){
            $encrypted = doubleTransposition($content, $key, $col_key);
        } else if ($cipher == "rc4"){
            $encrypted = RC4($content, $key);
        }else{
            die(ERROR_MESSAGE);
        }

        try {
            // Insert the time, input, and cipher into the database
            $key_str = $key . ";" . $col_key;
            $stmt = $conn->prepare("INSERT INTO cipher_logs (time, input, cipher, cipher_key) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $time, $content, $cipher, $key_str);
            $stmt->execute();
            echo "Data saved successfully!<br>";
        } catch (Exception $e) {
            die(ERROR_MESSAGE);
        }
        echo <<<_END
            <h2> Result: </h2>
            $encrypted
            <br>
        _END;
    }

    function Decrypt($content, $cipher, $conn, $key, $col_key){
        $time = date('Y-m-d H:i:s'); // Current timestamp

        if ($cipher == "simple_substitution"){
            $decrypted = simpleSubstitution($content, $key);
        } else if ($cipher == "double_transposition"){
            $decrypted = doubleTranspositionDecrypt($content, $key, $col_key);
        } else if ($cipher == "rc4"){
            $decrypted = RC4Decrypt($content, $key);
        }else{
            die(ERROR_MESSAGE);
        }

        try {
            // Insert the time, input, and cipher into the database
            $key_str = $key . ";" . $col_key;
            $stmt = $conn->prepare("INSERT INTO cipher_logs (time, input, cipher, cipher_key) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $time, $content, $cipher, $key_str);
            $stmt->execute();
            echo "Data saved successfully! <br>";
        } catch (Exception $e) {
            die(ERROR_MESSAGE);
        }
        echo <<<_END
            <h2> Result: </h2>
            $decrypted
            <br>
        _END;
    }
?>