<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1/roll/admin/events/update_profile");
curl_setopt($ch, CURLOPT_POST, 1);
// just send some basic dummy data so it fails gracefully or tells us the db error
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['event_id' => 1]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// since it requires session, we can't easily bypass login, but let's see what it returns
$response = curl_exec($ch);
echo "HTTP Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
echo "Response: " . $response . "\n";
curl_close($ch);
