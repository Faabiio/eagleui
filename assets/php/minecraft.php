<?php

function formatUuid($uuid) {
    return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20);
}

/**
 * Returns the player name for a given UUID.
 *
 * @param string $uuid The UUID of the player.
 * @return string|null The player name or null if not found.
 */
function getPlayerName($uuid) {
    $api_url = "https://api.minetools.eu/uuid/$uuid";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);

    if (isset($data['name'])) {
        return $data['name'];
    } else {
        return null;
    }
}

/**
 * Returns the player uuid for a given name.
 *
 * @param string $name The player name.
 * @return string|null The UUID of the player or null if not found.
 */
function getPlayerUuid($name){
    $api_url = "https://api.minetools.eu/uuid/$name";
    $response = file_get_contents($api_url);
    $data = json_decode($response, true);

    if (isset($data['id'])) {
        return formatUuid($data['id']);
    } else {
        return null;
    }
}

/**
 * Returns the URL of an image for a given UUID.
 *
 * @param string $uuid The UUID of the player.
 * @return string The URL of the player's avatar image.
 */
function getPlayerImageUrl($uuid) {
    return "https://mc-heads.net/avatar/$uuid/100";
}
?>