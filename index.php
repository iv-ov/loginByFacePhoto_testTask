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


/**
 * @author Iv Ov
 */
class FindFace
{
//    EXAMPLE:
//    POST /v0/verify/ HTTP/1.1
//    Host: api.findface.pro
//    Authorization: Token yfT8ftheVqnDLS3Q0yCiTH3E8YY_cm4p
//    Content-Type: application/json
//    Content-Length: [length]
//
//    {
//      "photo1": "http://static.findface.pro/smaple-photo1.jpg",
//      "photo2": "http://static.findface.pro/sample-photo2.jpg"
//    }


    /**
     *
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



$photo_to_check = filter_input(INPUT_POST, 'photo');

if ($photo_to_check){

    /**
     * @todo: eliminate hardcode
     */
    $findFace_token = '*** ENTER YOUR TOKEN FROM FINDFACE.PRO HERE ***';
    $photo_trusted = 'http://iv-ov.ru/telemed_help/img/user1_photo_trusted.jpeg';

    $findFace = new FindFace($findFace_token);

    $auth_result = $findFace->verify($photo_trusted, $photo_to_check);
    dl($auth_result);
}


?>
<form action="" method="post">
    <p>
        <label>
            Логин:
            <input name="username" value="user1" readonly="readonly" style="color: #bbb" />
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
