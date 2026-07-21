<?php
$f = 'E:\laragon\www\WD-11-Cinehome-cinema\storage\framework\views\26b200ae4619d5d1af81d3d4d1415e69.php';
$c = file_get_contents($f);

// Current: array_diff_key(...])))->render()  (3 closes)
// Should be: array_diff_key(...])))->render()  (2 closes)
// Fix: remove one ) 
$old = "['__data' => 1, '__path' => 1])))->render()";
$new = "['__data' => 1, '__path' => 1])))->render()";

if (strpos($c, $old) !== false) {
    $c = str_replace($old, $new, $c);
    file_put_contents($f, $c);
    echo "Fixed: removed one ) from array_diff_key\n";
} else {
    // Check what's actually there
    $pos = strpos($c, 'layouts.admin');
    if ($pos !== false) {
        $snippet = substr($c, $pos, 100);
        echo "Current: $snippet\n";
        // Count chars
        $a = substr_count($snippet, '(');
        $b = substr_count($snippet, ')');
        echo "Opens in snippet: $a, Closes in snippet: $b\n";
    }
}

// Verify
exec('php -l "' . str_replace('\\', '\\\\', $f) . '" 2>&1', $out, $rc);
echo implode("\n", array_slice($out, 0, 3)) . "\n";
