<?php
    Function simpleSubstitution($text, $key){
        if (strlen($key) != 26){
            return "Invalid Key: Not possible to decrypt/encrypt!";
        }
        $content = explode("\\n", strtolower($text));
        $final_text = "";
        $divided_key = str_split(strtoupper($key));
        $key_index = 0;
        $key_table = array();
        foreach(range('a','z') as $letter){
            $key_table[$letter] = $divided_key[$key_index];
            $key_index++; 
        }
        for ($i = 0; $i < count($content); $i++){
            $line = $content[$i];
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

    Function RC4($text, $key){
        $content = explode("\\n", $text);
        $final = "";
        foreach($content as $line){
            $dec_rep = array();
            for ($j = 0; $j < strlen($line); $j++){
                $dec_rep[$j] = ord($line[$j]);
            }
            $keystream = generate_key($dec_rep, $key);
            for ($i = 0; $i < count($dec_rep); $i++){
                $xor = dechex($keystream[$i] ^ $dec_rep[$i]);
                if (strlen($xor) == 1){
                    $final .= "0" . $xor . " ";
                }
                else {
                    $final .= $xor . " ";
                }
            }
            $final .= "<br>";
        }
        return $final;
    }


    Function RC4Decrypt($content, $key){
        $content = explode("\\n", $content);
        $final = "";
        foreach($content as $line){
            $dec_rep = array();
            $line_chars = explode(" ", $line);
            for ($j = 0; $j < count($line_chars); $j++){
                $dec_rep[$j] = hexdec($line_chars[$j]);
            }
            $keystream = generate_key($dec_rep, $key);
            for ($i = 0; $i < count($dec_rep); $i++){
                $xor = chr($keystream[$i] ^ $dec_rep[$i]);
                $final .= $xor . " ";
            }
            $final .= "<br>";
        }
        return $final;
    }

    function generate_key($line, $key){
        $keystream = array();
        $dec_key = array();
        for ($h = 0; $h < strlen($key); $h++){
            $dec_key[$h] = ord($key[$h]);
            // echo "$key[$h] --> $dec_key[$h]<br>";
        }
        $s = array();
        $k = array();
        $i = 0;
        while ($i < 256){
            $s[$i] = $i;
            $k[$i] = $dec_key[$i % count($dec_key)];
            $i++;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++){
            $j = ($j + $s[$i] + $k[$i]) % 256;
            $temp = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $temp;
        }
        $i = $j = 0;
        for ($b = 0; $b < count($line); $b++){
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            $temp = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $temp;
            $t = ($s[$i] + $s[$j]) % 256;
            $keystream[$b] = $s[$t];
            // echo "$keystream[$b] <br>";
        }
        // $key_count = count($keystream);
        // echo "<br> $key_count <br>";
        return $keystream;
    }

    function doubleTransposition($key, $message) {
        // Normalize the key to ensure only alphanumeric characters are used
        $key = preg_replace('/[^a-zA-Z0-9]/', '', $key);
        
        $keyArray = str_split($key);
        // Bind indices to characters before sorting
        $indexedKey = array_combine(range(0, count($keyArray) - 1), $keyArray);

        // Sort the array by value (alphabetically)
        asort($indexedKey); // Keeps the original indices intact

        // Extract the sorted indices to create the numeric key
        $numericKey = array_keys($indexedKey);
        $numCol = count($numericKey);
        $numRows = ceil(strlen($message)/$numCol); // Round up number of rows
    
        //Build the grid
        $grid = [];
         for ($i = 0; $i < $numRows; $i++) {
             $start = $i * $numCol;
             $grid[] = str_split(substr($message, $start, $numCol));
         }
    
        // Permute rows
        $grid = permute($grid, $numericKey);
    
        // Permute columns
        $grid = transpose($grid);
        $grid = permute($grid, $numericKey);
        $grid = transpose($grid);
    
        // Return grid as string
        return gridToString($grid);
    }
    
    function doubleTranspositionDecrypt($key, $message) {
        // Normalize the key to ensure only alphanumeric characters are used
        $key = preg_replace('/[^a-zA-Z0-9]/', '', $key);
        
        $keyArray = str_split($key);
        // Bind indices to characters before sorting
        $indexedKey = array_combine(range(0, count($keyArray) - 1), $keyArray);

        // Sort the array by value (alphabetically)
        asort($indexedKey); // Keeps the original indices intact

        // Extract the sorted indices to create the numeric key
        $numericKey = array_keys($indexedKey);
    
        $numCol = count($numericKey);
        $numRows = ceil(strlen($message)/$numCol); // Round up number of rows
    
        //Build the grid
        $grid = [];
         for ($i = 0; $i < $numRows; $i++) {
             $start = $i * $numCol;
             $grid[] = str_split(substr($message, $start, $numCol));
         }
    
        // Inverse operations in reverse order
        $grid = transpose($grid);
        $grid = inversePermute($grid, $numericKey);
        $grid = transpose($grid);
        
        $grid = inversePermute($grid, $numericKey);
    
        // Return grid as string
        return rtrim(gridToString($grid));
    }
    
    function inversePermute($grid, $key) {
        $permutedGrid = [];
        
        foreach ($grid as $row) {
            $newRow = array_fill(0, count($key), ' ');  // Initialize with spaces
            foreach ($key as $newPos => $oldPos) {
                if (isset($row[$newPos])) {  // If we have a value at this position
                    $newRow[$oldPos] = $row[$newPos];
                }
            }
            $permutedGrid[] = $newRow;
        }
        return $permutedGrid;
    }
     
    function permute($grid, $key) {
        $permutedGrid = [];
        foreach ($grid as $row) {
            $newRow = [];
            foreach ($key as $index) {
                $newRow[] = $row[$index] ?? ' ';
            }
            $permutedGrid[] = $newRow;
        }
        return $permutedGrid;
    }
    
    function transpose($grid) {
        $transposed = [];
        for ($i = 0; $i < count($grid[0]); $i++) {
            $transposed[$i] = array_column($grid, $i);
        }
        return $transposed;
    }
   
    function gridToString($grid) {
        return implode('', array_map('implode', $grid));
    }
?>
