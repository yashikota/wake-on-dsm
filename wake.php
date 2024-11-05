<?php

function sendMagicPacket($macAddress, $broadcastIp = '255.255.255.255', $port = 9) {
    if (!isValidMacAddress($macAddress)) {
        return "Error: Invalid MAC address format";
    }

    $macBinary = pack('H*', str_replace(':', '', $macAddress));
    $packet = str_repeat(chr(0xFF), 6) . str_repeat($macBinary, 16);

    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if (!$sock) {
        return "Error: Unable to create socket";
    }

    if (!socket_set_option($sock, SOL_SOCKET, SO_BROADCAST, 1)) {
        socket_close($sock);
        return "Error: Unable to set socket options";
    }

    $sent = socket_sendto($sock, $packet, strlen($packet), 0, $broadcastIp, $port);
    socket_close($sock);

    if (!$sent) {
        return "Error: Packet send failed";
    }

    return "Magic packet successfully sent to $macAddress on $broadcastIp:$port";
}

function isValidMacAddress($macAddress) {
    return preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', $macAddress);
}

$macAddress = '';

if (isset($_POST['macSelect']) && $_POST['macSelect'] !== 'other') {
    $macAddress = $_POST['macSelect'];
} elseif (isset($_POST['mac']) && isValidMacAddress($_POST['mac'])) {
    $macAddress = $_POST['mac'];
}

$message = '';

if ($macAddress) {
    $message = sendMagicPacket($macAddress);
} else {
    $message = "Error: No valid MAC address provided";
}

echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
