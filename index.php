<html>
<head>
    <meta charset="utf8">
    <title>Виявлення популярного контенту</title>
    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
</head>
<body>
<div class="jumbotron">
    <div class="container">
        <h1 style="text-align: center">Виявлення популярних записів у VK</h1>
    </div>
</div>
<div class="container">
    <?php
    require_once 'vkapi.php';

    set_time_limit(300);
    //error_reporting(0);

    if (isset($_REQUEST['tags'])) {
        $code = $_REQUEST['code'];
        $tags = explode(',', $_REQUEST['tags']);

        $info = getAuthInfo($code);
        $token = $info['access_token'];
        $userId = $info['user_id'];

        $userIdToScore = array();

        $posts = getPosts($userId, 20, $token)['items'];
        foreach ($posts as $post) {
            $likes = getLikesList('post', $userId, $post['id'], 1, $token)['items'];
            foreach ($likes as $user) {
                if (isset($userIdToScore[$user])) {
                    $userIdToScore[$user]++;
                } else $userIdToScore[$user] = 1;
            }
        }

        $groups = getGroups($userId, $token)['items'];
        foreach ($groups as $group) {
            $members = getMembers($group, 20, 'friends', $token)['items'];
            foreach ($members as $member) {
                if (isset($userIdToScore[$member])) {
                    $userIdToScore[$member]++;
                } else $userIdToScore[$member] = 1;
            }
        }

        if (count($tags) != 0 && $tags[0] !== '') {
            $friends = getFriends($token)['items'];
            foreach ($friends as $friend) {
                $posts2 = getPosts($friend, 20, $token)['items'];
                foreach ($posts2 as $post) {
                    $text = $post['text'];
                    foreach ($tags as $tag) {
                        if (strpos($text, $tag) !== false) {
                            if (isset($userIdToScore[$friend])) {
                                $userIdToScore[$friend]++;
                            } else $userIdToScore[$friend] = 1;
                        }
                    }

                    if (isset($post['copy_history'])) {
                        foreach ($post['copy_history'] as $history) {
                            $text2 = $history['text'];
                            foreach ($tags as $tag) {
                                if (strpos($text2, $tag) !== false) {
                                    if (isset($userIdToScore[$friend])) {
                                        $userIdToScore[$friend]++;
                                    } else $userIdToScore[$friend] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        arsort($userIdToScore);

        $maxCount = count($userIdToScore) / 8;

        $potentialCount = 0;
        foreach ($userIdToScore as $user => $score) {
            if ($score >= 5) {
                $potentialCount++;
            }
        }

        $potentialCount /= 5;

        print_r($userIdToScore);
        echo "<h2>From $potentialCount to $maxCount</h2>";
    } else if (isset($_REQUEST['code'])) { ?>

        <form style="width: 30%; margin: auto" method="get" action="/">
            <label for="tags">Введіть теги вашого запису через кому</label>
            <input type="text" name="tags" class="form-control" placeholder="Tags separated by commas: tag1,tag2,..."/>
            <input type="hidden" name="code" value="<?php echo $_REQUEST['code']; ?>"/>
            <input type="submit" onclick="progress()" class="form-control btn btn-success" style="margin-top: 20px"/>
            <div class="progress" id="my-progress" style="margin-top: 30px; display: none">
                <div class="progress-bar progress-bar-striped active" role="progressbar" style="width: 100%">
                    Processing...
                </div>
            </div>
        </form>
    <?php } else { ?>
        <h4>Будь ласка, увійдіть до вашого аккаунту VK перед тим, як продовжити</h4>
        <a href="<?php echo "https://oauth.vk.com/authorize?client_id=$clientId&redirect_uri=$redirectUri&scope=friends,groups"; ?>">
            <h1>Вконтакті</h1></a>
    <?php } ?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>
    function progress() {
        $('#my-progress').fadeIn("slow");
    }
</script>
</body>
</html>
