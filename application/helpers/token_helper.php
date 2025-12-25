<?php 

if (!function_exists('generate_token')) {
    function generate_token() {
        return md5(uniqid(rand(), true));
    }
}

if (!function_exists('generate_signature')) {
    function generate_signature($token) {
        $secret = "POSYANDU_SECRET_KEY";
        return md5($token . $secret);
    }
}

if (!function_exists('generate_expired')) {
    function generate_expired() {
        return time() + 3600;
    }
}

if (!function_exists('validate_signature')) {
    function validate_signature($token, $signature) {
        $secret = "POSYANDU_SECRET_KEY";
        return md5($token . $secret) === $signature;
    }
}

if (!function_exists('validate_expired')) {
    function validate_expired($exp) {
        return time() < $exp;
    }
}
