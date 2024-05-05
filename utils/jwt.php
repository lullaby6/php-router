<?php

function generate_jwt($payload = [], $secret_key = "secret_key", $exp = 3600) {
    $header = [
        'alg' => 'HS256',
        'typ' => 'JWT'
    ];

    $defaultPayload = [
        'exp' => time() + $exp
    ];
    $payload = array_merge($defaultPayload, $payload);

    $base64Header = base64_encode(json_encode($header));
    $base64Payload = base64_encode(json_encode($payload));

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret_key);

    $jwt = "$base64Header.$base64Payload.$signature";

    return $jwt;
}

function verify_jwt($jwt, $secret_key = "secret_key"){
    $tokenParts = explode('.', $jwt);

    if (count($tokenParts) !== 3) return [
        'error' => 'Invalid token format'
    ];

    $base64Header = $tokenParts[0];
    $base64Payload = $tokenParts[1];
    $signature = $tokenParts[2];

    $header = json_decode(base64_decode($base64Header), true);
    $payload = json_decode(base64_decode($base64Payload), true);

    if (!$header || !$payload) return [
        'error' => 'Invalid token format'
    ];

    if (empty($header['alg']) || $header['alg'] !== 'HS256') return [
        'error' => 'Invalid token algorithm'
    ];

    $expectedSignature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret_key);

    if (!hash_equals($signature, $expectedSignature)) return [
        'error' => 'Invalid token signature',
    ];

    if (isset($payload['exp']) && $payload['exp'] < time()) return [
        'error' => 'Token expired'
    ];

    return $payload;
}