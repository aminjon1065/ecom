<?php

declare(strict_types=1);

namespace Pest\Browser\Support;

function socket_create_listen(int $port): mixed
{
    $server = @stream_socket_server("tcp://127.0.0.1:{$port}", $errorCode, $errorMessage);

    return $server === false ? false : $server;
}

function socket_getsockname(mixed $socket, mixed &$address, mixed &$port = null): bool
{
    if (! is_resource($socket)) {
        return false;
    }

    $name = stream_socket_get_name($socket, false);

    if ($name === false) {
        return false;
    }

    $separatorPosition = strrpos($name, ':');

    if ($separatorPosition === false) {
        return false;
    }

    $address = substr($name, 0, $separatorPosition);
    $port = (int) substr($name, $separatorPosition + 1);

    return true;
}

function socket_close(mixed $socket): void
{
    if (is_resource($socket)) {
        fclose($socket);
    }
}
