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

    function swap(){
        
    }
    Function doubleTranspositionDecrypt($key){
        
    }
    Function RC4Decrypt($key){
        
    }
?>