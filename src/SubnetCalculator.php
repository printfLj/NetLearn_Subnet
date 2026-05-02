<?php
// SubnetCalculator.php

class SubnetCalculator {
    public static function calculateVLSM($baseIP, $basePrefix, $hostRequests) {
        // Sort requirements descending (CCNA Rule)
        arsort($hostRequests);
        $hostRequests = array_values($hostRequests); // Re-index so names are A, B, C...
        
        $currentAddr = ip2long($baseIP);
        $baseNetworkLong = $currentAddr;
        $maxAddr = $currentAddr + pow(2, (32 - $basePrefix)) - 1;
        
        $results = [];
        foreach ($hostRequests as $name => $needed) {
            // Find smallest n such that 2^n - 2 >= needed (i.e. 2^n >= needed + 2)
            // Use integer loop to avoid floating-point precision issues with log()
            $bitsNeeded = 1;
            while ((pow(2, $bitsNeeded) - 2) < $needed) {
                $bitsNeeded++;
            }
            $newPrefix = 32 - $bitsNeeded;
            $blockSize = pow(2, $bitsNeeded);
            
            $networkAddr = $currentAddr;
            $broadcastAddr = $currentAddr + $blockSize - 1;

            // Check for Overflow
            if ($broadcastAddr > $maxAddr) {
                return [
                    "status" => "error",
                    "needed_at_failure" => $needed,
                    "suggestion" => self::getSuggestion($hostRequests)
                ];
            }

            $results[] = [
                "name"            => "Subnet " . chr(65 + $name),
                "hosts_required"  => $needed,
                "prefix"          => "/" . $newPrefix,
                "network_address" => long2ip($networkAddr),
                "first_usable"    => long2ip($networkAddr + 1),
                "last_usable"     => long2ip($broadcastAddr - 1),
                "broadcast"       => long2ip($broadcastAddr),
                "subnet_mask"     => long2ip(-1 << (32 - $newPrefix)),
                "total_hosts"     => (int)($blockSize - 2)
            ];

            $currentAddr = $broadcastAddr + 1;
        }

        return ["status" => "success", "subnets" => $results];
    }

    private static function getSuggestion($hostRequests) {
        $totalNeededSpace = 0;
        foreach ($hostRequests as $req) {
            $totalNeededSpace += pow(2, ceil(log($req + 2, 2)));
        }
        $suggestedPrefix = 32 - ceil(log($totalNeededSpace, 2));
        return $suggestedPrefix;
    }
}