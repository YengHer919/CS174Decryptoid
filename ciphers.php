<?php
    Function simpleSubstitution($text, $key){
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
    Function doubleTransposition($key){
        
    }
    Function RC4($text, $key){
        $content = explode("\\n", strtolower($text));
        $content_hex = bin2hex($content);
        $content_arr = array();
        $content_index = 0;
        for($b = 0; $b < strlen($content_hex)-2; $b += 2){
            $hex = $content_hex[$b] . $content_hex[$b+1];
            $content_arr[$content_index] = $hex;
            $content_index++;
        }
        $s = array();
        $key_arr = explode(" ", $key);
        $k = array();
        $i = 0;
        for ($i = 0; $i < 256; $i++){
            $s[$i] = $i;
            $k[$i] = $key_arr[$i % count($key_arr)];
            $i++;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++){
            $j = (($j + $s[$i]) + $k[$i]) % 256;
            // Swap(S[i], S[j])
            $temp = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $temp;
        }
        $i = $j = 0;
        $key_stream = array();
        for($c = 0; $c < count($content_arr); $c++){
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            // Swap(S[i], S[j])
            $temp = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $temp;
            $t = ($s[$i] + $s[$j]) % 256;
            $key_stream[$c] = $s[$t];
        }
    }

    Function doubleTranspositionDecrypt($key){
        
    }
    Function RC4Decrypt($key){
        
    }
?>