<?php
// IpUtils.php

class IpUtils {
    public static function generateRandomIP($class = 'C') {
        switch ($class) {
            case 'A': return rand(1, 126) . ".0.0.0";
            case 'B': return rand(128, 191) . "." . rand(0, 255) . ".0.0";
            case 'C': 
            default:  return rand(192, 223) . "." . rand(0, 255) . "." . rand(0, 255) . ".0";
        }
    }
}