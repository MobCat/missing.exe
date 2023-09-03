
<?php
// define maximum file size in bytes
$maxFileSize = 1509949; // 1.44 MB

function encode_string($data) {
  //BAD!BAD!BAD!BAD!BAD!BAD!BAD!BAD!BAD!BAD!BAD!
  //Dont out this here. move it somewhere.
  //TODO: Move this! and make it auto genrate per install!
  $key = 'eAJ1oq1PZ^9%xwM=7U@JB*xj0<S~^<zhw';
  //$key = file_get_contents('C:\path\to\key\not\in\web\dir\key.bin');
  $key_len = strlen($key); //This is kinda pointless, just static set it but ill leave it for now
  // Encode our url
  $encoded = '';
  for ($i = 0; $i < strlen($data); $i++) {
    $encoded .= chr(ord($data[$i]) ^ ord($key[$i % $key_len]));
  }
  $encoded = base64_encode($encoded);
  $encoded = strtr($encoded, '+/', '-_');
  $encoded = rtrim($encoded, '=');

  unset($key);
  //unset($randomData);
  //sodium_memzero($key) // This would be nice but need to install shit
  return $encoded;
}

// functions go buuuuurr.
// Gives us a human readable file size
function formatSizeUnits($bytes){
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unitIndex = 0;
    while ($bytes >= 1024 && $unitIndex < count($units) - 1){
        $bytes /= 1024;
        $unitIndex++;
    }
    return round($bytes, 2) . ' ' . $units[$unitIndex];
}

// check if a file was uploaded
if (isset($_FILES["fileToUpload"])) {
  // check for errors
  if ($_FILES["fileToUpload"]["error"] > 0) {
    echo "Error: " . $_FILES["fileToUpload"]["error"] . "<br>";
  } else {
    // get file contents
    $fileContent = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

    // get file info
    $filename = $_FILES["fileToUpload"]["name"];
    $filesize = formatSizeUnits($_FILES["fileToUpload"]["size"]);
    $checksum = md5_file($_FILES["fileToUpload"]["tmp_name"]);
    // Dont wan't upload date, want og dates of files.
    // also need to scrub any xfif data or wgaterver it is.
    //$filedate = date("Y-m-d H:i:s", filemtime($_FILES["fileToUpload"]["tmp_name"]));

    // check file size
    if ($_FILES["fileToUpload"]["size"] > $maxFileSize) {
      echo "Error: File size is too large. Max file size is " . $maxFileSize . " bytes.";
    } else {
      // generate random key
      // This is turbo trash. 
      //$key = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+'), 0, 21);
      //$key = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+'), 0, 12);
      //$key = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()_+', 3)), 0, 21);
      $key = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@#$%^&*()', 3)), 0, 16);
      //$key = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz@#$%^&*()', 3)), 0, 21);

      // Genrate ivs for encrpting and decrpting
      $iv = random_bytes(16);
      $iv2 = random_bytes(16);

      // Ok something major is fucked upand idk whats going on.
      // we have 2 openssl_encrypt and one openssl_decrypt
      // ok its working now. not sure if its working right or not
      // No idea if memory is beeing wiper, what that openssl_decrypt
      // or why file.php was echoing stuff into the file.
      // But it looks like its working

      // encrpy the uploaded file?
      $key2 = $iv.$key;
      $encrypted_file = openssl_encrypt($fileContent, "AES-256-CBC", $key2, OPENSSL_RAW_DATA, $iv2);
      //$encrypted_file = openssl_encrypt($fileContent, "AES-256-GCM", $key, OPENSSL_RAW_DATA, $iv); // GCM has built-in authentication??

      // generate random file name
      $random_file_name = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 12);
      $random_metadata_name = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 12);

      // save metadata to random file
      $containerSize = rand(800000, 1200000); //800kb to 1.20mb
      // Create a padded container string
      $containerData = str_pad($filename.'|'.$filesize.'|'.$random_file_name.'|'.$iv2."|".$checksum, $containerSize, ' ');
      // Encrypt the container
      $encryptedContainerData = openssl_encrypt($containerData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
      // Save the encrypted container to disk
      file_put_contents('files/' . $random_metadata_name, $encryptedContainerData);
      // Clean up
      unset($encryptedContainerData);
      //fclose($containerData); // idk what or if I have to close anything?
      unset($containerData);
      unset($containerSize);
      // Distroy when the file was uploaded.
      $timestamp = strtotime('1970-01-01 00:00:00');
      touch('files/' . $random_metadata_name, $timestamp);


      // save encrypted file user uploaded with random name
      // TODO:
      // $key needs to be changed to $iv.$key.$iv2
      // Or something that contains data from both metadata and the link that doesnt compromzies the encrption on user file.
      $temp_file = tmpfile();
      fwrite($temp_file, $encrypted_file);
      fseek($temp_file, 0);
      $encrypted_file_in_memory = fread($temp_file, strlen($encrypted_file));
      $file_size_in_memory = strlen($encrypted_file_in_memory);
      $decrypted_file_in_memory = openssl_decrypt($encrypted_file_in_memory, "AES-256-CBC", $key2, OPENSSL_RAW_DATA, $iv2);

      // remove unencrypted file from memory
      $encrypted_file = $encrypted_file_in_memory;
      file_put_contents("files/" . $random_file_name, $encrypted_file);
      unset($decrypted_file_in_memory);
      fclose($temp_file);
      // Distroy when the file was uploaded.
      touch('files/' . $random_file_name, $timestamp);
      unset($temp_file, $key2, $encrypted_file);

      // generate filename and key string for download URL
      $filename_key_string = $key.$random_metadata_name.$iv;

      //Clean up metadata
      //unset($key);
      //unset($random_file_name);
      unset($key, $random_file_name, $filename, $filesize, $filedate);

      // generate download link
      // server IP is automatly grabbed, but no conigs yet for
      // setting if it runs out of a folder or not yet.
      $download_link = "http://".$_SERVER['SERVER_NAME']."/missing/file?v=" . encode_string($filename_key_string);
      unset($filename_key_string);

      // send back download link for debug
      // Need to comment out the unset ram flush thing
      /*
      echo strlen($filename_key_string). " ".$filename_key_string."<br>";
      echo strlen($random_file_name). " ".$random_file_name."<br>";
      echo strlen($key). " ".$key."<br><br>";
      */

      // 
      echo "<head><link rel=\"icon\" href=\"icon.png\" type=\"image/x-icon\"></head><div class=\"center\">
      <img src=\"locked.png\" alt=\"locked\">
      <h1>Your file has been successfully uploaded</h1><br> Your download link is: 
      <br><a href='" . $download_link . "'>" . $download_link . "</a>
      <button onclick=\"copyToClipboard()\">Copy to Clipboard</button><br><br>
      !PLEASE NOTE! If you lose this link, the file CAN NOT be recovered! Ever!<br>
      <a href=\"/missing\">Home</a></div>
      <script>
        function copyToClipboard() {
          const downloadLink = document.querySelector(\"a[href='" . $download_link . "']\");
          const textToCopy = downloadLink.textContent;
          const tempInput = document.createElement(\"input\");
          tempInput.value = textToCopy;
          document.body.appendChild(tempInput);
          tempInput.select();
          document.execCommand(\"copy\");
          document.body.removeChild(tempInput);
          alert(\"Link copyed to clipboard:\\n \" + textToCopy);
        }
      </script>";

      // Nuke the link and force garbage collection
      unset($download_link);
      //gc_collect_cycles(); // Oops, I think this kills the server?
    }
  }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Missing.exe</title>
    <link rel="icon" href="icon.png" type="image/x-icon">
    <style>
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }

      .center {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        height: 100%;
      }
    </style>
  </head>
  <body>
    <div class="center">
    <?php if (!isset($_FILES["fileToUpload"])): ?>
    <img src="icon.png" alt="icon">
    <h1>Missing.exe</h1>
    <form action="" method="post" enctype="multipart/form-data">
      Select file to upload (Max file size: 1.44 MB):<br>
      <input type="file" name="fileToUpload" id="fileToUpload" required>
      <input type="submit" value="Upload File" name="submit">
    </form>
    <br><br><a href="info.html">About this site</a>

    <?php endif; ?>
    </div>
    <script>
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
      window.addEventListener('beforeunload', function() {
        document.getElementById("fileToUpload").value = "";
      });
    </script>
  </body>
</html>
