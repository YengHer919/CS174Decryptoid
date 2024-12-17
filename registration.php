<?php
    // Aung Paing Soe and Yeng Her
    // 12.8.24
    // Final Decryptiod

    // Miscellaneous setup
    require_once 'init.php';
    session_regenerate_id();

    // HTML to log in
    echo <<<_END
        <html> <head> <title> Decryptoid Signup and Login Page </title> 
        <script src="registration_validation.js"> </script> 
        <h1> Decryptoid </h1> </head>
        Log in
        <form method="post" action="registration.php" enctype="multipart/form-data"><pre>
        Enter Username: <input type="text" name="username" required>
        Enter Password: <input type="password" name="password" required>
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
        $un_temp = sanitization($conn, $_POST['username']);
        $pw_temp = sanitization($conn, $_POST['password']);

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
            $token = password_verify($pw_temp, $row[2]);
            if ($token) { // If passwords match, store all data into session
                $_SESSION['auth'] = 1;
                echo "successful log in";
                $result->close();
                $stmt->close();
                $conn -> close();
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
                Enter Username: <input type="text" name="user" id="user">
                Enter Email: <input type="text" name="email" id="email">
                Enter Password: <input type="password" name="passwd" id="passwd">
                <input type="submit" value="Sign up">
            </pre></form>
            _END;

    // Check to make sure salts are unique
    // function saltVerified($conn, $s1, $s2){
    //     try {
    //         $stmt = $conn->prepare("SELECT * FROM credentials WHERE salt1 = ?");
    //         $stmt->bind_param("s", $s1);
    //         $stmt->execute();
    //         $result = $stmt->get_result();
    //     } catch (Exception $e) {
    //         die(ERROR_MESSAGE);
    //     }

    //     if ($result->num_rows) {
    //         return false;    
    //     }
    //     $result->close();
    //     $stmt->close(); 

    //     try {
    //         $stmt = $conn->prepare("SELECT * FROM credentials WHERE salt2 = ?");
    //         $stmt->bind_param("s", $s2);
    //         $stmt->execute();
    //         $result = $stmt->get_result();
    //     } catch (Exception $e) {
    //         die(ERROR_MESSAGE);
    //     }
        
    //     if ($result->num_rows) {
    //         return false;    
    //     }
    //     $result->close();
    //     $stmt->close(); 
        
    //     return true;
    // }

    // Check if all fields have been entered
    if (isset($_POST['user']) && isset($_POST['email']) && isset($_POST['passwd'])) {
        // Open connection to database
        try {
            $conn = new mysqli($hn, $un, $pw, $db);
        } catch (Exception $e) {
            die(ERROR_MESSAGE);
        }
        
        // Get info from fields
        $userName = sanitization($conn, $_POST['user']);
        $email = sanitization($conn, $_POST['email']);
        $password =  sanitization($conn, $_POST['passwd']);
        $hashPass = password_hash($password, PASSWORD_BCRYPT);
        // Prepare to insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO credentials (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userName, $email, $hashPass);

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

