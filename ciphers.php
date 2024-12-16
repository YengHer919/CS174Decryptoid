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
        $final = "";
        foreach($content as $line){
            $dec_rep = array();
            for ($j = 0; $j < strlen($line); $j++){
                $dec_rep[$j] = hexdec(bin2hex($line[$j]));
            }
            $keystream = generate_key($dec_rep, $key);
            for ($i = 0; $i < count($dec_rep); $i++){
                $final .= $keystream[$i] xor $line[$i];
            }
            $final .= "<br>";
        }
        return $final;
    }

    function generate_key($line, $key){
        $key_arr = explode(" ", $key);
        $dec_key = array();
        $keystream = array();
        for ($h = 0; $h < count($key_arr); $h++){
            $dec_key[$h] = hexdec($key_arr[$h]);
        }
        $s = array();
        $k = array();
        $i = 0;
        for ($i = 0; $i < 256; $i++){
            $s[$i] = $i;
            $k[$i] = $i % count($dec_key);
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
            $key = ($s[$i] + $s[$j]) % 256;
            $keystream[$b] = dechex($s[$key]);
        }
        return $keystream;
    }

    function swap(){
        
    }
    Function doubleTranspositionDecrypt($key){
        
    }
    Function RC4Decrypt($key){
        
    }
?>