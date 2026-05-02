<?php
// SubnetCalculator.php

class SubnetCalculator {
    public static function calculate($baseIP, $basePrefix, $hostRequests) {
        // Sort descending for VLSM
        usort($hostRequests, fn($a, $b) => $b['count'] <=> $a['count']);
        
        $currentAddr = ip2long($baseIP);
        $baseNetworkLong = $currentAddr;
        $maxAddr = $currentAddr + pow(2, (32 - $basePrefix)) - 1;
        
        $results = [];
        $steps = [];
        $masksUsed = [];

        foreach ($hostRequests as $req) {
            $needed = $req['count'];
            $name = $req['name'];

            // Logic: 2^n >= (hosts + 2)
            $bitsNeeded = ceil(log($needed + 2, 2));
            $newPrefix = 32 - $bitsNeeded;
            $blockSize = pow(2, $bitsNeeded);
            $masksUsed[] = $newPrefix;
            
            $networkAddr = $currentAddr;
            $broadcastAddr = $currentAddr + $blockSize - 1;

            if ($broadcastAddr > $maxAddr) {
                return ["status" => "error", "suggestion" => self::getSuggestion($hostRequests)];
            }

            $results[] = [
                "name" => $name,
                "prefix" => "/" . $newPrefix,
                "network_address" => long2ip($networkAddr),
                "first_usable" => long2ip($networkAddr + 1),
                "last_usable" => long2ip($broadcastAddr - 1),
                "broadcast" => long2ip($broadcastAddr),
                "subnet_mask" => long2ip(-1 << (32 - $newPrefix))
            ];

            // Record the step for the UI
            $steps[] = "<strong>$name</strong>: Needed $needed hosts. Smallest block is $blockSize (/$newPrefix). Range: " . long2ip($networkAddr) . " to " . long2ip($broadcastAddr);

            $currentAddr = $broadcastAddr + 1;
        }

        // Determine Mode
        $mode = (count(array_unique($masksUsed)) === 1) ? "FLSM (Fixed Length)" : "VLSM (Variable Length)";

        return [
            "status" => "success", 
            "subnets" => $results, 
            "steps" => $steps,
            "mode" => $mode
        ];
    }

    private static function getSuggestion($hostRequests) {
        $total = 0;
        foreach ($hostRequests as $h) { $total += pow(2, ceil(log($h['count'] + 2, 2))); }
        return 32 - ceil(log($total, 2));
    }
}