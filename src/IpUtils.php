<?php
// IpUtils.php

class IpUtils {
    /**
     * Generate a random valid network address for a given prefix length.
     * All host bits are zeroed. Uses private RFC-1918 ranges.
     * Prefix 1–15  → Class A-style: 10.x.x.x
     * Prefix 16–23 → Class B-style: 172.16–31.x.x
     * Prefix 24–30 → Class C-style: 192.168.x.x
     */
    public static function generateForPrefix(int $prefix): string {
        if ($prefix == 0) {
            // /0 covers everything; return 0.0.0.0
            return '0.0.0.0';
        } elseif ($prefix <= 15) {
            $rawLong = ip2long("10.0.0.0") + rand(0, (int)pow(2, 24) - 1);
        } elseif ($prefix <= 23) {
            $rawLong = ip2long("172.16.0.0") + rand(0, (int)pow(2, 16) - 1);
        } else {
            $rawLong = ip2long("192.168.0.0") + rand(0, (int)pow(2, 16) - 1);
        }

        // Zero out the host bits to guarantee a valid network address
        $mask    = -1 << (32 - $prefix);
        $netLong = $rawLong & $mask;

        return long2ip($netLong);
    }

    // Keep for backward compatibility
    public static function generateRandomIP($class = 'C') {
        switch ($class) {
            case 'A': return rand(1, 126) . ".0.0.0";
            case 'B': return rand(128, 191) . "." . rand(0, 255) . ".0.0";
            case 'C':
            default:  return rand(192, 223) . "." . rand(0, 255) . "." . rand(0, 255) . ".0";
        }
    }
}