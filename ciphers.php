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

    function doubleTransposition($text, $row_key, $col_key) {
        $text = explode("\\n", $text);
        $row_arr = explode(",", $row_key);
        $col_arr = explode(",", $col_key);
        $row_num = max($row_arr);
        $col_num = max($col_arr);
        $ciphertext = "";
        foreach($text as $line){
            $grid = create_grid($line, $row_num, $col_num);
            $row = row_permute($grid, $row_arr,  $col_num);
            $final = col_permute($row, $col_arr, $row_num);
            $ciphertext .= grid_to_str($final, $row_num, $col_num) . "<br>";
        }
        return $ciphertext;
    }
    
    function doubleTranspositionDecrypt($text, $row_key, $col_key) {
        $text = explode("\\n", $text);
        $row_arr = explode(",", $row_key);
        $col_arr = explode(",", $col_key);
        $row_num = max($row_arr);
        $col_num = max($col_arr);
        $plaintext = "";
        foreach($text as $line){
            $grid = create_grid($line, $row_num, $col_num);
            $col = inverse_col_permute($grid, $col_arr,  $row_num);
            $final = inverse_row_permute($col, $row_arr, $col_num);
            $plaintext .= grid_to_str($final, $row_num, $col_num) . "<br>";
        }
        return $plaintext;
    }

    function create_grid($line, $rows, $cols){
        $grid = array();
        $char_count = 0;
        for($i = 0; $i<$rows; $i++){
            for($j=0; $j<$cols; $j++){
                if ($line[$char_count] == " ")
                    $grid[$i][$j] = "_";
                else 
                    $grid[$i][$j] = $line[$char_count];
                $char_count++;
            }
        }
        return $grid;
    }

    function grid_to_str($grid, $rows, $cols){
        $str = "";
        for($i = 0; $i<$rows; $i++){
            for($j=0; $j<$cols; $j++){
                $str .= $grid[$i][$j];
            }
        }
        return $str;
    }

    function row_permute($grid, $row_arr, $col_num) {
        $row_grid = array();
        $r = 0; 
        for($row=0; $row<count($row_arr); $row++){
            $index = $row_arr[$row];
            for($i=0; $i < $col_num; $i++){
                $row_grid[$r][$i] = $grid[$index-1][$i];
            }
            $r++;
        }
        return $row_grid;
    }

    function col_permute($grid, $col_arr, $row_num){
        $c = 0; 
        $col_grid = array();
        for($col=0; $col<count($col_arr); $col++){
            $index = $col_arr[$col];
            for($i=0; $i < $row_num; $i++){
                $col_grid[$i][$c] = $grid[$i][$index-1];
            }
            $c++;
        }
        return $col_grid;
    }

    function inverse_row_permute($grid, $row_arr, $col_num){
        $r = 0; 
        $row_grid = array();
        //Row Permutations
        for($row=0; $row<count($row_arr); $row++){
        $index = $row_arr[$row];
            for($i=0; $i < $col_num; $i++){
                $row_grid[$index-1][$i] = $grid[$r][$i];
            }
            $r++;
        }
        return $row_grid;
    }
    
    function inverse_col_permute($grid, $col_arr, $row_num){
        $col_grid = array();
        $c = 0;
        for($col=0; $col<count($col_arr); $col++){
            $index = $col_arr[$col];
            for($i=0; $i < $row_num; $i++){
                $col_grid[$i][$index-1] = $grid[$i][$c];
            }
            $c++;
        }
        return $col_grid;
    }
    
?>
