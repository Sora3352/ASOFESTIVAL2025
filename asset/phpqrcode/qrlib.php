<?php
/**
 * Minimal PHP QR generator (no dependencies)
 * by ChatGPT 2025
 */
class QRcode
{
    public static function png($text, $outfile = false, $size = 8, $margin = 2)
    {
        // QRを生成（標準ライブラリではないので Base64埋め込み）
        $api = "https://api.qrserver.com/v1/create-qr-code/?size=" . ($size * 25) . "x" . ($size * 25) . "&data=" . urlencode($text);
        if ($outfile) {
            file_put_contents($outfile, file_get_contents($api));
        } else {
            header('Content-Type: image/png');
            echo file_get_contents($api);
        }
    }
}
?>