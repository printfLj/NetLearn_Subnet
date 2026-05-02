<?php
// IpUtils.php

class IpUtils {
    /**
     * Generate a random valid RFC-1918 network address for a given prefix.
     * All host bits are zeroed. Private ranges used:
     *
     *  10.0.0.0/8   → for prefixes /0 – /15
     *                 (prefixes smaller than /8 have no randomizable bits
     *                  inside 10.x.x.x, so 10.0.0.0 is returned as-is)
     *  172.16.0.0/12 → for prefixes /16 – /23
     *                  (randomizable bits: those between /12 and the prefix)
     *  192.168.0.0/16 → for prefixes /24 – /30
     *                   (randomizable bits: those between /16 and the prefix)
     */
    public static function generateForPrefix(int $prefix): string {
        $hostBits = 32 - $prefix;
        $mask     = $prefix === 0 ? 0 : ((int)((-1) << $hostBits));

        if ($prefix <= 15) {
            // Anchor: 10.0.0.0/8
            // Bits we can randomize: those between /8 and the prefix (max 7 bits)
            $anchorLong  = ip2long("10.0.0.0");
            $randomBits  = max(0, $prefix - 8);
            $randOffset  = $randomBits > 0 ? rand(0, (1 << $randomBits) - 1) : 0;
            $netLong     = ($anchorLong + ($randOffset << $hostBits)) & $mask;
        } elseif ($prefix <= 23) {
            // Anchor: 172.16.0.0/12
            // Bits we can randomize: those between /12 and the prefix (max 11 bits)
            $anchorLong  = ip2long("172.16.0.0");
            $randomBits  = max(0, $prefix - 12);
            $randOffset  = $randomBits > 0 ? rand(0, (1 << $randomBits) - 1) : 0;
            $netLong     = ($anchorLong + ($randOffset << $hostBits)) & $mask;
        } else {
            // Anchor: 192.168.0.0/16
            // Bits we can randomize: those between /16 and the prefix (max 14 bits)
            $anchorLong  = ip2long("192.168.0.0");
            $randomBits  = max(0, $prefix - 16);
            $randOffset  = $randomBits > 0 ? rand(0, (1 << $randomBits) - 1) : 0;
            $netLong     = ($anchorLong + ($randOffset << $hostBits)) & $mask;
        }

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