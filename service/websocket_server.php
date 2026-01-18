<?php
// ملف websocket_server.php

$host = '0.0.0.0'; // استقبل من أي IP
$port = 8080; // البورت اللي تفتحه

$server = stream_socket_server("tcp://$host:$port", $errno, $errstr);

if (!$server) {
    echo "خطأ في تشغيل السيرفر: $errstr ($errno)\n";
    exit(1);
}

$clients = [];

echo "✅ WebSocket Server يعمل على $host:$port...\n";

while (true) {
    $read = $clients;
    $read[] = $server;
    $write = $except = null;

    if (stream_select($read, $write, $except, null) > 0) {
        if (in_array($server, $read)) {
            $client = stream_socket_accept($server);
            if ($client) {
                $clients[] = $client;
                echo "🟢 عميل جديد اتصل\n";
                $handshake = fread($client, 1024);
                if (perform_handshake($client, $handshake)) {
                    echo "🤝 تم المصافحة مع العميل\n";
                }
            }
            unset($read[array_search($server, $read)]);
        }

        foreach ($read as $client) {
            $data = @fread($client, 1024);
            if (!$data) {
                fclose($client);
                unset($clients[array_search($client, $clients)]);
                echo "🔴 عميل قطع الاتصال\n";
                continue;
            }

            $decoded = decode($data);

            echo "📥 استقبلت: " . $decoded . "\n";

            // مثال: لما العميل يرسل رسالة، السيرفر يرد على الجميع
            $response = json_encode([
                "type" => "new_notification",
                "user_id" => "123", // ID المستخدم، المفروض ترسل حسب المستخدم اللي يخصه الاشعار
                "service_name" => "خدمة جديدة",
                "topic" => "تم إضافة خدمة لك 🎉",
            ]);

            send_to_all($clients, $response);
        }
    }
}

function perform_handshake($client, $handshake)
{
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $handshake, $matches)) {
        $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $headers = "HTTP/1.1 101 Switching Protocols\r\n"
                 . "Upgrade: websocket\r\n"
                 . "Connection: Upgrade\r\n"
                 . "Sec-WebSocket-Accept: $key\r\n\r\n";
        fwrite($client, $headers);
        return true;
    }
    return false;
}

function send_to_all($clients, $message)
{
    $encoded = encode($message);
    foreach ($clients as $client) {
        fwrite($client, $encoded);
    }
}

function encode($payload, $type = 'text', $masked = false)
{
    $frameHead = [];
    $payloadLength = strlen($payload);

    switch ($type) {
        case 'text':
            $frameHead[0] = 129;
            break;
        default:
            return false;
    }

    if ($payloadLength <= 125) {
        $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
    } elseif ($payloadLength >= 126 && $payloadLength <= 65535) {
        $frameHead[1] = ($masked === true) ? 126 + 128 : 126;
        $frameHead[2] = ($payloadLength >> 8) & 255;
        $frameHead[3] = $payloadLength & 255;
    } else {
        $frameHead[1] = ($masked === true) ? 127 + 128 : 127;
        $frameHead[2] = ($payloadLength >> 56) & 255;
        $frameHead[3] = ($payloadLength >> 48) & 255;
        $frameHead[4] = ($payloadLength >> 40) & 255;
        $frameHead[5] = ($payloadLength >> 32) & 255;
        $frameHead[6] = ($payloadLength >> 24) & 255;
        $frameHead[7] = ($payloadLength >> 16) & 255;
        $frameHead[8] = ($payloadLength >> 8) & 255;
        $frameHead[9] = $payloadLength & 255;
    }

    foreach (array_keys($frameHead) as $i) {
        $frameHead[$i] = chr($frameHead[$i]);
    }

    $frame = implode('', $frameHead) . $payload;

    return $frame;
}

function decode($data)
{
    $bytes = unpack('C*', $data);
    $dataLength = $bytes[2] & 127;

    if ($dataLength === 126) {
        $masks = array_slice($bytes, 5, 4);
        $data = array_slice($bytes, 9);
    } elseif ($dataLength === 127) {
        $masks = array_slice($bytes, 11, 4);
        $data = array_slice($bytes, 15);
    } else {
        $masks = array_slice($bytes, 3, 4);
        $data = array_slice($bytes, 7);
    }

    $decoded = '';
    foreach (array_keys($data) as $i) {
        $decoded .= chr($data[$i] ^ $masks[($i - 1) % 4]);
    }

    return $decoded;
}
?>
