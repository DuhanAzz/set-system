<?php
$file = 'app/Roll/Controllers/User/RollTokenCheckoutController.php';
$content = file_get_contents($file);

$tokenCheck = "
        \$active_token = \$_SESSION['active_token_' . \$event_id] ?? null;
        \$active_invoice = \$_SESSION['active_manual_invoice_' . \$event_id] ?? null;
        if (!\$active_token || !\$active_invoice) {
            \$_SESSION['flash_message'] = \"Sesi Token Anda tidak valid atau telah berakhir.\";
            \$_SESSION['flash_type'] = \"error\";
            header(\"Location: \" . getenv('APP_URL') . \"/roll/user/explore\");
            exit;
        }
";

// Add to pay()
$content = preg_replace('/(public function pay\(\$event_id = null\) \{.*?\$db = Database::getInstance\(\)->getConnection\(\);)/s', "$1\n" . $tokenCheck, $content);

file_put_contents($file, $content);
echo "Fixed pay().\n";
