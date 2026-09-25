<?php
$file = 'app/Roll/Controllers/User/RollTokenCheckoutController.php';
$content = file_get_contents($file);

// 1. Rename class
$content = str_replace('class RollCheckoutController', 'class RollTokenCheckoutController', $content);

// 2. Auth check for token in detail() and confirmPayment()
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
$content = preg_replace('/(public function detail\(\$event_id = 0\) \{.*?\$db = Database::getInstance\(\)->getConnection\(\);)/s', "$1\n" . $tokenCheck, $content);
$content = preg_replace('/(public function confirmPayment\(\) \{.*?\n.*?\$event_id =.*?;\n.*?\$db = Database::getInstance\(\)->getConnection\(\);)/s', "$1\n" . $tokenCheck, $content);

// 3. Update SQL to filter by manual_invoice_code
$content = str_replace('AND e.manual_invoice_code IS NULL', "AND e.manual_invoice_code = '\$active_invoice'", $content);

// 4. Update view path and URLs
$content = str_replace("view('roll/user/checkout/detail'", "view('roll/user/token_checkout/detail'", $content);
$content = str_replace('/roll/user/checkout/detail/', '/roll/user/token_checkout/detail/', $content);
$content = str_replace('/roll/user/registration/index/', '/roll/user/token_registration/index/', $content);
$content = str_replace('/roll/user/checkout', '/roll/user/token_checkout', $content);

// 5. When confirming payment (inserting roll_payments), we need to insert the manual_invoice_code!
// And we also need to UPDATE roll_event_tokens is_used = 1.
$insertPayment = "
            // Save to roll_payments but use manual_invoice_code
            \$stmtPay = \$db->prepare(\"
                INSERT INTO roll_payments (club_id, event_id, invoice_code, total_amount, payment_method, payment_proof, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())
            \");
            \$stmtPay->execute([\$club_id, \$event_id, \$active_invoice, \$final_total, 'Transfer', \$uploadResult['filename']]);

            // Expire the token
            \$stmtTok = \$db->prepare(\"UPDATE roll_event_tokens SET is_used = 1, used_at = NOW() WHERE event_id = ? AND club_id = ? AND token_code = ?\");
            \$stmtTok->execute([\$event_id, \$club_id, \$active_token]);
            
            // Clear session
            unset(\$_SESSION['active_token_' . \$event_id]);
            unset(\$_SESSION['active_manual_invoice_' . \$event_id]);
";
$content = preg_replace('/(\$stmtPay = \$db->prepare\(".*?INSERT INTO roll_payments.*?}\s*\n)/s', $insertPayment . "\n", $content);

file_put_contents($file, $content);
echo "TokenCheckoutController modified.\n";
