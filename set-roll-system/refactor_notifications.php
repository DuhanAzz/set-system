<?php
$dir = new RecursiveDirectoryIterator("src/");
$ite = new RecursiveIteratorIterator($dir);

foreach($ite as $file) {
    if ($file->getExtension() === "php") {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;
        
        $content = preg_replace('/\$msg\s*=\s*[\'"][\'"];\s*/', '', $content);
        
        $content = preg_replace_callback('/\$msg\s*=\s*["\']<div[^>]*bg-green[^>]*>(.*?)<\/div>["\'];/is', function($matches) {
            $text = trim($matches[1]);
            $text = str_replace("'", "\\'", $text);
            return "\$_SESSION['flash_message'] = '$text';\n                \$_SESSION['flash_type'] = 'success';";
        }, $content);
        
        $content = preg_replace_callback('/\$msg\s*=\s*["\']<div[^>]*bg-red[^>]*>(.*?)<\/div>["\'];/is', function($matches) {
            $text = trim($matches[1]);
            $text = str_replace('"', '\\"', $text);
            return "\$_SESSION['flash_message'] = \"$text\";\n                \$_SESSION['flash_type'] = 'error';";
        }, $content);
        
        $content = preg_replace_callback('/\$msg\s*=\s*["\']<div[^>]*bg-orange[^>]*>(.*?)<\/div>["\'];/is', function($matches) {
            $text = trim($matches[1]);
            $text = str_replace("'", "\\'", $text);
            return "\$_SESSION['flash_message'] = '$text';\n                \$_SESSION['flash_type'] = 'warning';";
        }, $content);
        
        // Use \x3F for question mark to avoid parser issues
        $content = preg_replace('/<\x3F=\s*\$msg\s*\x3F>\s*/', '', $content);
        
        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Refactored $path\n";
        }
    }
}
