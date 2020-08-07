<!doctype html>
<html lang="en">
<head>
    <title>Document</title>
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>

<h1 class="text-center">Algoritma Blowfish</h1>

<hr>
<div class="container">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" multipart="" enctype="multipart/form-data">
        
        <div class="form-group col-lg-12">
            <div class="col-lg-12"><font color="brown" style="font-weight:bold;font-size:16px;">Masukkan File</font></div>
            <div class="col-lg-12">
                <input class="form-control" type='file' name='file' class="form-group" ></input>
            </div>
        </div>

        <div class="form-group col-lg-12">
            <div class="col-lg-12"><font color="brown" style="font-weight:bold;font-size:16px;">Masukkan Kunci</font></div>
            <div class="col-lg-12"><input name="key" type="password" class="form-control" placeholder="Masukkan Kunci " maxlength="20"></div>
        </div>

        <div class="form-group col-md-12">
            <button type="submit" class="btn btn-info" name="submit" > Submit</button>
        </div>
    </form>
</div>

<hr><br><br>

    

<?php 

include('blowfish.php');

if(isset($_POST['submit'])){
    
    function toBin($str){
		$str = (string)$str;
		$l = strlen($str);
		$result = '';
		while($l--){
			$result = str_pad(decbin(ord($str[$l])),8,"0",STR_PAD_LEFT).$result;
		}
		return $result;
    }
    
    function entropizer($str) {
        $classes = array(
            array("regex" => "/[a-z]/", "size" => 26),
            array("regex" => "/[A-Z]/", "size" => 26),
            array("regex" => "/[0-9]/", "size" => 10),
            " ,.?!\"£$%^&*()-_=+[]{};:\'@#~<>/\\|`¬¦"
        );
        $size = 0;
        $str = trim($str);
        foreach($classes as $case) {
            if(is_array($case)) {
                if(preg_match($case["regex"], $str)) {
                    $size += $case["size"];
                }
            } else {
                foreach(str_split($case, 1) as $char) {
                    if(strpos($str, $char) !== false) {
                        $size += strlen($case);
                        break;
                    }
                }
            }
        }
        return floor(log($size, 2) * strlen($str));
    }

    function format_numb($num)
    {
        return number_format((float)$num, 4, '.', '');
    }

    function entropy($string) {
        $h=0;
        $size = strlen($string);
        foreach (count_chars($string, 1) as $v) {
           $p = $v/$size;
           $h -= $p*log($p)/log(2);
        }
        return $h;
     }



    $key = $_POST['key']; //key untuk aes
    $file = $_FILES['file'];
    // $file = $_FILES['file']['name'];
    $plain = file_get_contents($file['tmp_name']);
    // echo $plain;
    
    $file_name = preg_replace("/\s+/", "_", $file['name']);
    move_uploaded_file($file['tmp_name'],'upload/'.$file_name);
    
    echo '<pre>';
    // $plain = "Universitas Dian Nuswantoro Imam Bonjol Polke";
    // $key = "asdqwe213";
    $startCBC = microtime(true);
    $encryptCBC = Blowfish::encrypt($plain,$key,Blowfish::BLOWFISH_MODE_CBC,Blowfish::BLOWFISH_PADDING_RFC,PHP_EOL);
    $endCBC = microtime(true) - $startCBC;
    $decryptCBC = Blowfish::decrypt($encryptCBC,$key,Blowfish::BLOWFISH_MODE_CBC,Blowfish::BLOWFISH_PADDING_RFC,PHP_EOL);
        
    $startECB = microtime(true);
    $encryptECB = Blowfish::encrypt($plain,$key,Blowfish::BLOWFISH_MODE_EBC,Blowfish::BLOWFISH_PADDING_RFC,PHP_EOL);
    $endECB = microtime(true) - $startECB;
    $decryptEBC = Blowfish::decrypt($encryptECB,$key,Blowfish::BLOWFISH_MODE_EBC,Blowfish::BLOWFISH_PADDING_RFC,PHP_EOL);


    $data = [
        'Plaintext' => $plain,
        'cipherCBC' => $encryptCBC,
        'decryptCBC' => $decryptCBC,
        'cipherEBC' => $encryptECB,
        'decryptEBC' => $decryptEBC,
        // PHP_EOL,
    ];

    // file_put_contents('filename.txt', print_r($data, true));
    file_put_contents('hasil/Enkrip_'.$file_name, print_r($data, true));
    // print_r($data);

    // echo base64_encode($ciphertxt);
    // echo $decrypt;

    // $open = fopen("hasil/Enkrip_".$file_name,"w");
    // fwrite($open, print_r($data, true));
    // fclose($open);
    // $nama_file = "Enkrip_".$file_name;

    $toBinCBC = toBin($encryptCBC);
    $toBinECB = toBin($encryptECB);
    
    $entropCBC = entropy($encryptCBC);
    $entropeCBC = entropizer($encryptCBC);
    $percentaseCBC = ($entropCBC*100)/strlen($toBinCBC);
    $percenCBC = ($entropeCBC*100)/strlen($toBinCBC);
    
    $entropECB = entropy($encryptECB);
    $entropeECB = entropizer($encryptECB);
    $percenECB = ($entropeECB*100)/strlen($toBinECB);
    
    echo 'Name = '.$file['name'].'<br>';
    echo 'Size = '.round($file['size']/1024).' Kb <br>';
    echo 'CBC Entropy = '.$entropCBC.' - Percentase = '.format_numb($percentaseCBC).'<br>';
    echo 'CBC Entropizer = '.$entropeCBC.' - Percentase = '.format_numb($percenCBC).'<br>';
    echo 'ECB Entropy = '.$entropeECB.' - Percentase = '.format_numb($percenECB).'<br>';
    echo 'CBC Time = '.format_numb($endCBC).'<br>';
    echo 'ECB Time = '.format_numb($endECB).'<br>';
    // echo round($percenCBC,4);

    // echo round(9.5, 0, PHP_ROUND_HALF_UP);   // 10
    // echo round(9.5, 0, PHP_ROUND_HALF_DOWN); // 9
    // echo round(9.5, 0, PHP_ROUND_HALF_EVEN); // 10
    // echo round(9.5, 0, PHP_ROUND_HALF_ODD);  // 9

    // echo round(8.5, 0, PHP_ROUND_HALF_UP);   // 9
    // echo round(8.5, 0, PHP_ROUND_HALF_DOWN); // 8
    // echo round(8.5, 0, PHP_ROUND_HALF_EVEN); // 8
    // echo round(8.5, 0, PHP_ROUND_HALF_ODD);  // 9
    // echo round(3.4);         // 3
    // echo round(3.5);         // 4
    // echo round(3.6);         // 4
    // echo round(3.6, 0);      // 4
    // echo round(1.95583, 2);  // 1.96
    // echo round(1241757, -3); // 1242000
    // echo round(5.045, 2);    // 5.05
    // echo round(5.055, 2);    // 5.06
    

    echo '</pre>';

?>

    <div class="container">
        <div class="form-group">
            <label>Encrypt using key Cipher Block Chaining</label><br>
            <small id="helpId" class="text-muted">Encrypt : <?php echo $encryptCBC?></small><br>
            <small id="helpId" class="text-muted">Encode 64 : <?php echo base64_encode($encryptCBC)?></small><br>
            <small id="helpId" class="text-muted">Decode 64 : <?php echo base64_decode($decryptCBC)?></small><br>
            <small id="helpId" class="text-muted">Decrypt : <?php echo $decryptCBC?></small><br>
            </div>
            </div>
            <div class="container">
            <div class="form-group">
            <label>Encrypt using key Electronic Code Book</label><br>
            <small id="helpId" class="text-muted">Encrypt : <?php echo $encryptECB?></small><br>
            <small id="helpId" class="text-muted">Encode 64 : <?php echo base64_encode($encryptECB)?></small><br>
            <small id="helpId" class="text-muted">Decode 64 : <?php echo base64_decode($decryptEBC)?></small><br>
            <small id="helpId" class="text-muted">Decrypt : <?php echo $decryptEBC?></small><br>
        </div>
    </div>
<?php 
}
?>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous"></script>
</body>
</html>