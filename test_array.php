<?php
try {
    $a = array_fill(1, 0, []);
    print_r($a);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
