<?php
// SubnetCalculator.php

class SubnetCalculator {
    /**
     * Main entry point. Accepts host requests as [{name, count}, ...]
     * Sorts descending for VLSM, calculates subnets, returns steps + mode.
     */
    public static function calculate(string $baseIP, int $basePrefix, array $hostRequests): array {
        // Sort descending by count for VLSM (largest subnet first)
        usort($hostRequests, fn($a, $b) => $b['count'] <=> $a['count']);

        $currentAddr     = ip2long($baseIP);
        $maxAddr         = $currentAddr + (int)pow(2, 32 - $basePrefix) - 1;

        $results  = [];
        $steps    = [];
        $masksUsed = [];

        foreach ($hostRequests as $req) {
            $needed = (int)$req['count'];
            $name   = trim($req['name']) !== '' ? $req['name'] : 'Unnamed';

            // Integer loop: find smallest n where 2^n - 2 >= needed
            // Avoids floating-point precision bugs from ceil(log(...))
            $bitsNeeded = 1;
            while ((int)pow(2, $bitsNeeded) - 2 < $needed) {
                $bitsNeeded++;
            }

            $newPrefix    = 32 - $bitsNeeded;
            $blockSize    = (int)pow(2, $bitsNeeded);
            $networkAddr  = $currentAddr;
            $broadcastAddr = $currentAddr + $blockSize - 1;
            $masksUsed[]  = $newPrefix;

            if ($broadcastAddr > $maxAddr) {
                return [
                    "status"     => "error",
                    "suggestion" => self::getSuggestion($hostRequests)
                ];
            }

            $results[] = [
                "name"            => $name,
                "hosts_required"  => $needed,
                "prefix"          => "/" . $newPrefix,
                "network_address" => long2ip($networkAddr),
                "first_usable"    => long2ip($networkAddr + 1),
                "last_usable"     => long2ip($broadcastAddr - 1),
                "broadcast"       => long2ip($broadcastAddr),
                "subnet_mask"     => long2ip(-1 << (32 - $newPrefix)),
                "total_hosts"     => $blockSize - 2
            ];

            $steps[] = "<strong>$name</strong>: Needed $needed hosts → block size $blockSize (/$newPrefix). "
                     . "Network: " . long2ip($networkAddr) . ", Broadcast: " . long2ip($broadcastAddr);

            $currentAddr = $broadcastAddr + 1;
        }

        $mode = (count(array_unique($masksUsed)) === 1) ? "FLSM (Fixed Length)" : "VLSM (Variable Length)";

        return [
            "status"  => "success",
            "subnets" => $results,
            "steps"   => $steps,
            "mode"    => $mode
        ];
    }

    private static function getSuggestion(array $hostRequests): int {
        $total = 0;
        foreach ($hostRequests as $req) {
            $needed = (int)$req['count'];
            $bits   = 1;
            while ((int)pow(2, $bits) - 2 < $needed) {
                $bits++;
            }
            $total += (int)pow(2, $bits);
        }
        return 32 - (int)ceil(log($total, 2));
    }
}