
<?php

function decode_string($data) {
    //!!!FUCK YOU, REMOVE THIS!!!
    $key = 'eAJ1oq1PZ^9%xwM=7U@JB*xj0<S~^<zhw';
    $data = strtr($data, '-_', '+/');
    $data = base64_decode($data);
    if ($data === false) {
        return false;
    }
    $key_len = strlen($key);
    $decoded = '';
    for ($i = 0; $i < strlen($data); $i++) {
        $decoded .= chr(ord($data[$i]) ^ ord($key[$i % $key_len]));
    }
    unset($key);
    return $decoded;
}

function getFileIcon($fileName) {
  $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

  $fileCategories = array(
    'jpg' => array('jpg'),
    'png' => array('png', 'gif'),
    'bmp' => array('bmp'),
    'pdf' => array('pdf'),
    'txt' => array('txt'), // Might be a bunch for text file formats, also find an special icon for NFO
    'reg' => array('reg'),
    'doc' => array('doc', 'docx'),
    'xls' => array('xls', 'xlsx', 'csv', 'tsv'),
    'ppt' => array('ppt', 'pptx'),
    'zip' => array('zip', 'tar', 'gz'),
    '7z' => array('7z'),
    'obj' => array('obj', 'fx', '3ds', 'fxb', 'fbx'), // I dont remember which one it is.
    'rar' => array('rar'),
    'exe' => array('exe'),
    'bat' => array('bat', 'sh'),
    'wav' => array('mp3', 'wav', 'flac'),
    'midi' => array('midi', 'mid', 'mod', 'xm'),
    'avi' => array('mkv', 'avi'),
    'mpeg' => array('mp4', 'mpeg', 'mpg'),
    'mov' => array('mov'),
    'dll' => array('dll'),
    'html' => array('htm', 'html', 'php'),
    'ini' => array('ini', 'config'),
    'mob' => array('mob'), // Easter egg.
    'ai' => array('ai', 'svg')
  );

  foreach ($fileCategories as $category => $extensions) {
    if (in_array($fileExtension, $extensions)) {
      return 'icons/'.$category.'-icon.jpg';
    }
  }

  return 'icons/unknown.jpg';
}

function downloadFile($filename, $random_file_name, $key2, $iv2) {
    //echo "Download file...<br>";
    global $status;
    $status = "Download file...<br>";
    // This was echoing into my file??
    //echo "name: ".$filename."<br>file: ".$random_file_name."<br>key: ".$key2."<br>iv: ".$iv2."<br><br>";
    // Decrypt file?
    $encrypted_file_path = "files/" . $random_file_name;
    $encrypted_file_contents = file_get_contents($encrypted_file_path);
    $decrypted_file_contents = openssl_decrypt($encrypted_file_contents, "AES-256-CBC", $key2, OPENSSL_RAW_DATA, $iv2);


    // Check if the decryption was successful
    if ($decrypted_file_contents === false) {
        // Set an error message and exit
        $status = "Invalid key<br>";
        exit($status);
    }

    // Send the decrypted file to the user as a download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($decrypted_file_contents));
    echo $decrypted_file_contents;
    
    // Destroy sensitive data in memory
    unsed($key2, $iv2);
    unset($decrypted_file_contents);
    unset($encrypted_file_contents);

    $status = "File downloaded successfully.<br>";
    

}

// Check if the "file" parameter exists in the URL
if (isset($_GET['d'])) {
    // Get the value of the "file" parameter from the URL
    $encoded_string = $_GET['d'];

    $status = "Raw dogg downloading your shit now...";

    // Decode the base64 string using the character set
    $decoded = decode_string($encoded_string);

    // Split the decoded string into a filename and key variable
    $key = substr($decoded, 0, 16);
    $filename = substr($decoded, 16, 12);
    $iv = substr($decoded, 28, 16);
    unset($decoded);

    // Decode metadata
    // Check isset($_GET['v']) for more info on this
    $file_path = './files/' . $filename;
    if (file_exists($file_path)) {
        $encryptedContainerData = file_get_contents('files/' . $filename);
        $decryptedContainerData = openssl_decrypt($encryptedContainerData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $containerParts = explode('|', trim($decryptedContainerData));

        $filename = $containerParts[0];
        //$filesize = $containerParts[1];
        $random_file_name = $containerParts[2];
        $iv2 = $containerParts[3];
        //$checksum = $containerParts[4];

        $status = 'Link decoded, file found.<br>';
        $encryptedContainerData = str_repeat('F', strlen($encryptedContainerData));
        $decryptedContainerData = str_repeat(' ', strlen($decryptedContainerData));
        $containerParts = array_fill(0, count($containerParts), '');
        unset($encryptedContainerData, $containerParts);
        // Download file
        $key2 = $iv.$key;
        downloadFile($filename, $random_file_name, $key2, $iv2);
        unset($key2, $iv, $key, $filename, $random_file_name);

    }
     else {
        $status = 'Invalid link<br>';
    }

    
    

    
} 
elseif (isset($_GET['v'])) {
    // Use custom base64 fucntion to decode and set ver
    $encoded_string = $_GET['v'];
    //echo $encoded_string."<br>";
    $decoded = decode_string($encoded_string);

    // Split the decoded string into a filename and key variable
    $key = substr($decoded, 0, 16);
    $filename = substr($decoded, 16, 12);
    $iv = substr($decoded, 28, 16);
    unset($decoded);

    // weard hack for fav icon
    //echo "<link rel=\"icon\" href=\"icon.png\" type=\"image/x-icon\">";
    /*
    echo "DEBUG: Link decode<br>";
    echo $decoded."<br>";
    echo "Filename: $filename<br>";
    echo "Key: $key<br>";
    echo "IV: $iv<br><br>";
    */

    // decode metadata and read it here.
    // Check if file exists
    $file_path = './files/' . $filename;
    if (file_exists($file_path)) {
        // Decrypt metadata
        // Read the encrypted container from disk
        $encryptedContainerData = file_get_contents('files/' . $filename);
        // Decrypt the container
        $decryptedContainerData = openssl_decrypt($encryptedContainerData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        // Trim the padded container string and split it into parts
        $containerParts = explode('|', trim($decryptedContainerData));

        // Extract the metadata values
        $filename = $containerParts[0];
        $filesize = $containerParts[1];
        $random_file_name = $containerParts[2];
        $iv2 = $containerParts[3];
        $checksum = $containerParts[4];

        $status = 'Link decoded, file found.<br>';
        //$sendhelp = true;


        // Clean up, Dont think this is right?
        // Dont we need to unload the container?
        $encryptedContainerData = str_repeat('F', strlen($encryptedContainerData));
        $decryptedContainerData = str_repeat(' ', strlen($decryptedContainerData));
        $containerParts = array_fill(0, count($containerParts), '');
        unset($encryptedContainerData, $decryptedContainerData, $containerParts);
        //unset($key2, $iv, $key, $filename, $random_file_name);
        


    }
    else {
        // File not found
        $status = 'Invalid link<br>';
        $icon = "<img src='icon/error.jpg'>";
    }

    // Display info and button
    
    $icon = "<img src='".getFileIcon($filename)."'>";
    //echo $filename." ";
    //echo "(".$filesize.")<br>";
    //echo "MD5: ".strtoupper($checksum)."<br>";
    //echo "<button onclick=\"downloadFile()\">Download</button>";
    //echo "<button type=\"button\" onclick=\"downloadFile()\">Download</button>";
    

    //Info for download button
    //$random_file_name; // File to decrpty
    //$iv2; // IV for decrption
    $key2 = $iv.$key; // Key for decrption
    //unlink($iv, $key); // Might be to late for this here.
    //echo $key2;

    if(isset($_POST['btnDownload'])) {
    // call download function on button press
     downloadFile($filename, $random_file_name, $key2, $iv2);

    }

    
}
else {
  // Output an error message if the "file" parameter is not present
  echo "Error: invalid link.";
  $icon = "<img src='icon/error.jpg'>";
}
?>
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
        <div>
    Status: <?php echo $status; ?>
    <!-- TODO: If v is set, then If status is ok -->
    <?php echo $icon; ?>
    <?php echo $filename." "."(".$filesize.")<br>"; ?>
    <?php echo "MD5: ".strtoupper($checksum)."<br>"; ?>
        </div> 
    <form method="post">
        <button type="submit" name="btnDownload">Download</button>
    </form>
    <a href=/missing>Home</a></div>
    </div>
</body>
</html>