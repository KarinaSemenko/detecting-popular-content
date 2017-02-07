<?php
$clientId = ;
$clientSecret = ;
$redirectUri = 'http://localhost';

$calls = 0;

function getAuthInfo($code)
{
    global $clientId, $clientSecret, $redirectUri;

    return json_decode(
        file_get_contents(
            "https://oauth.vk.com/access_token?client_id=$clientId&client_secret=$clientSecret&redirect_uri=$redirectUri&code=$code&v=5.62"
        ), true
    );
}

function execute($code, $token)
{
    return method('execute',
        array(
            'code' => $code
        ), $token
    );
}

function getMembers($groupId, $count, $filter, $token)
{
    return method('groups.getMembers',
        array(
            'group_id' => $groupId,
            'count' => $count,
            'filter' => $filter
        ), $token
    );
}

function getGroups($userId, $token)
{
    return method('groups.get',
        array(
            'user_id' => $userId
        ), $token
    );
}

function getComments($ownerId, $postId, $count, $token)
{
    return method('wall.getComments',
        array(
            'owner_id' => $ownerId,
            'post_id' => $postId,
            'count' => $count
        ), $token
    );
}

function getLikesList($type, $ownerId, $itemId, $skipOwn, $token)
{
    return method('likes.getList',
        array(
            'type' => $type,
            'owner_id' => $ownerId,
            'item_id' => $itemId,
            'skip_own' => $skipOwn
        ), $token
    );
}

function getPosts($ownerId, $count, $token)
{
    return method('wall.get',
        array(
            'owner_id' => $ownerId,
            'count' => $count
        ), $token
    );
}

function getFriends($token)
{
    return method('friends.get',
        array(
        ), $token
    );
}

function method($name, $params, $token)
{
    global $calls;

    if ($calls == 3) {
        sleep(1);
        $calls = 1;
    } else $calls++;

    return json_decode(file_get_contents(
        "https://api.vk.com/method/$name?"
        . http_build_query($params)
        . "&access_token=$token&v=5.62"
    ), true)['response'];
}
