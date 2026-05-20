<?php
// Ipv6Helper.php

class Ipv6Helper {
    // Generates a list of /64 subnets from a /48 prefix
    public static function getSubnets($base48, $count = 5) {
        $subnets = [];
        // Example: 2001:db8:acad::/48
        $cleanBase = rtrim($base48, ":/0"); 

        for ($i = 0; $i < $count; $i++) {
            $sla_id = str_pad(dechex($i), 4, "0", STR_PAD_LEFT);
            $subnets[] = [
                "subnet_id" => $i,
                "full_address" => $cleanBase . ":" . $sla_id . "::/64",
                "explanation" => "SLA ID $sla_id is incremented in the 4th quartet."
            ];
        }
        return $subnets;
    }
}