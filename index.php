<?php

namespace IvOv;


ini_set('display_errors', TRUE);
error_reporting(-1);

function dl($val){
    ob_start();
    var_dump($val);
    $val_dump = ob_get_contents();
    ob_end_clean();

    ?><pre style="background: beige; color: #000"><?
            echo htmlspecialchars($val_dump);
    ?></pre><?
}

interface FaceRecognizerInterface {
    public function verify($photo1, $photo2);
}


/**
 * @author Iv Ov
 */
class FindFace implements FaceRecognizerInterface
{
    /**
     * @var string
     * @todo: generalize
     */
    private $api_url = 'https://api.findface.pro/v0/';

    /**
     *
     * @var type
     * @todo: Active trial till 14-Jun-2017 !!!
     */
    private $auth_token = '';


    public function __construct($token) {
        $this->auth_token = $token;
    }


    public function verify($photo1, $photo2) {
        $result = $this->callApi(
            'verify',
            [
                'photo1' => $photo1,
                'photo2' => $photo2,
                'threshold' => 'strict',
            ]
        );
        return $result->verified;
    }


    /**
     * @param array $request_data
     * @return mixed
     */
    private function callApi($url_path, $request_data)
    {
        $context_options = array(
            'http' => array(
                'method' => 'POST',
                'header' => [
                    'Authorization: Token ' . $this->auth_token,
                    'Content-Type: application/json',
                ],
                'content' => json_encode($request_data),
                //'ignore_errors' => true,
            )
        );
        $context = stream_context_create($context_options);

        $result_json = file_get_contents($this->api_url . trim($url_path, '/') . '/', false, $context);
        if (!$result_json) {
            return false;
        }
        $result = json_decode($result_json);

        return $result;
    }

}



class Auth
{
    private $faceRecognizer;


    public function __construct(FaceRecognizerInterface $faceRecognizer) {
        $this->faceRecognizer = $faceRecognizer;
    }


    public function loginByPhoto($username, $photo) {
        $user_photo = $this->getUserPhoto($username);
        if (!$user_photo){
            return null;
        }
        return $this->faceRecognizer->verify($user_photo, $photo);
    }


    private function getUserPhoto($username) {
        /** @todo: hardcoded! */
        $photosByUser = [
            'user1' => 'http://iv-ov.ru/telemed_help/img/user1_photo_trusted.jpeg',
        ];

        if (isset($photosByUser[$username])){
            return $photosByUser[$username];
        }
        return null;
    }
}



$photo_to_check = filter_input(INPUT_POST, 'photo');
$username = filter_input(INPUT_POST, 'username');

if ($photo_to_check){

    /**
     * @todo: Enter your token from FindFace.Pro here
     */
    $findFace_token = file_get_contents('./findfacepro_token.txt');

    $findFace = new FindFace($findFace_token);

    $auth = new Auth($findFace);

    $auth_result = $auth->loginByPhoto($username, $photo_to_check);
    dl($auth_result);
}


?>
<form action="" method="post">
    <p>
        <label>
            Логин:
            <input name="username" value="user1" />
            <i>(в системе есть только один логин &mdash; user1)</i>
        </label>
    </p>
    <p>
        <label>
            Ваше фото (url):
            <input name="photo" value="http://iv-ov.ru/telemed_help/img/user1_photo_to_check.jpeg" type="url" size="100" style="max-width: 100%" />
        </label>
    </p>
    <p>
        <button type="submit">Войти</button>
    </p>
    <p class="note" style="color: #777">
        Active trial till 14-Jun-2017
    </p>
</form>
