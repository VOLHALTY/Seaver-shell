<?php
function SEND_METHOD ($method , $params){
    if(!$params){
        $params = array();
    }
    $params["method"] = $method;
    define("API_TOKEN","Api Token Bot");
    define("API_TELEGRAM","https://api.telegram.org/bot" . API_TOKEN . "/");
    $Resalte = curl_init(API_TELEGRAM);
    curl_setopt($Resalte , CURLOPT_RETURNTRANSFER , true);
    curl_setopt($Resalte , CURLOPT_CONNECTTIMEOUT , 60);
    curl_setopt($Resalte , CURLOPT_TIMEOUT , 10);
    curl_setopt($Resalte , CURLOPT_POSTFIELDS , json_encode($params));
    curl_setopt($Resalte , CURLOPT_HTTPHEADER , array("Content-Type:application/json"));
    $END = curl_exec($Resalte);
    return $END;
}
$Conect = file_get_contents("php://input");
$Conect_json = json_decode($Conect , true);
$chat_id = $Conect_json["message"]["chat"]["id"];
$user_input = $Conect_json["message"]["text"];
$message_id = $Conect_json["message"]["message_id"];
$detabes_File_name = "How.db";
$testchat = new SQLite3($detabes_File_name);
$testchat->exec("
    CREATE TABLE IF NOT EXISTS resatle (
    shell TEXT NOT NULL UNIQUE 
)");
if ($user_input == "/start"){
    $keword = array(
        "resize_keyboard"=>true,
        "keyboard"=>array(
            array("Save Shell 💀"),
            array("🎖 View List","⚙️ shell testing")
        )
    );
    $text = "<b>🤖 Welcome to the robot</b>\n\n<blockquote>👨🏻‍💻 Contact the following keyboards to continue</blockquote>";
    SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" => $text,"parse_mode"=>"HTML","reply_markup"=>$keword,"reply_to_message_id"=>$message_id));
}
elseif($user_input == "Save Shell 💀"){
    $text = "<b>🍄 For the saving of the URL please send the address:</b>\n\n<blockquote>⚠️ The address should be in the form of:\n https://www.amazon.com</blockquote>";
    SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" => $text,"parse_mode"=>"HTML","disable_web_page_preview"=>true,"reply_to_message_id"=>$message_id));
}
elseif (filter_var($user_input, FILTER_VALIDATE_URL)){
    $check = $testchat->querySingle("SELECT COUNT(*) as count FROM resatle WHERE shell = '$user_input'");
    if ($check > 0){
        $text = "<b>⚠️ This URL already exists in the database!</b>";
        SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" => $text,"parse_mode"=>"HTML"));
    }else {
        $testchat->exec("INSERT OR IGNORE INTO resatle (shell) VALUES ('$user_input')");
        $text = "<b>🟢 Was successfully stored in the database ✅</b>";
        SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" => $text, "parse_mode" => "HTML"));
    }
}elseif ($user_input == "🎖 View List"){
//    $check = $testchat->querySingle("SELECT COUNT(*) as count FROM resatle WHERE shell = '$user_input'");
    $btn1 = array(
        "resize_keyboard"=>true,
        "inline_keyboard"=>array(
            array(
                array("text"=>"🗑 Delet List", "callback_data"=>"deletList"),
            )
        )
    );
    $results = $testchat->query("SELECT * FROM resatle");
    $deta = [];
    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $deta[] = $row;
    }
    $jsone = json_encode($deta, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE);
    $str = str_replace(['[', ']', '{', '}', '"', '  ', 'shell:', ','], '', $jsone);
    $str2 = str_replace(['\/'] , "/", $str);
    $prints = print_r($str2, true);
    $text = "📂 List of stored laughs:\n<blockquote> $prints</blockquote>";
    SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" => $text, "parse_mode" => "HTML","disable_web_page_preview"=>true,"reply_markup"=>$btn1));

}elseif ($Conect_json["callback_query"]["data"] == "deletList"){
    $chat_idcallback_query = $Conect_json["callback_query"]["message"]["chat"]["id"];
    $message_idcallback_query = $Conect_json["callback_query"]["message"]["message_id"];

    unlink("How.db");
    $text = "<i>he current list was successfully erased ✅</i>";
    SEND_METHOD("editMessageText",array("chat_id"=>$chat_idcallback_query,"text"=>$text,"message_id"=>$message_idcallback_query,"parse_mode" => "HTML"));
}elseif ($user_input == "⚙️ shell testing"){
    $results = $testchat->query("SELECT * FROM resatle");
    $deta = [];

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        $deta[] = $row;
    }
    $shmaro_list = count($deta);
    $btn2 = array(
        "resize_keyboard"=>true,
        "inline_keyboard"=>array(
            array(
                array("text"=>"The number of shells ->","callback_data"=>"numberOfShells"),
                array("text"=>"$shmaro_list","callback_data"=>"numberOfShells1"),
            ),
            array(
                array("text"=>"🔥 shell test start","callback_data"=>"testStart"),
            )
        )
    );
    $text = "<b>🔥 Welcome to the shell testing section</b>\n\n<blockquote>This section sends to all the stored shells of Ricost and declares the accuracy of the shell🧨</blockquote>\n\n<b>By clicking <u>🔥 Shell Test Start</u> can start the test</b>";
    SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" => $text, "parse_mode" => "HTML","reply_markup"=>$btn2));
}elseif ($Conect_json["callback_query"]["data"] == "testStart"){
    $chat_idcallback_query = $Conect_json["callback_query"]["message"]["chat"]["id"];
    $message_idcallback_query = $Conect_json["callback_query"]["message"]["message_id"];
    SEND_METHOD("deleteMessage", array("chat_id" => $chat_idcallback_query, "message_id" => $message_idcallback_query));
    $Select_url = $testchat->query("SELECT shell FROM resatle");
    function getStatusCode($url){
        $ch = curl_init($url);
        curl_setopt($ch , CURLOPT_NOBODY , true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $status_code;
    }

    $deta = [];
    $conters = 0;
    while ($rows = $Select_url->fetchArray(SQLITE3_ASSOC)) {
        $conters++;
       $url = $rows['shell'];
       $status_code = getStatusCode($url);
       $deta[] = "-$conters- | $status_code]]";
    }
    $jsone1 = json_encode($deta,JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE);
    $str1 = str_replace(['[',']',',',' ','"'], '',$jsone1);
    $printes = print_r($str1, true);
    $text = "<b>⚙️Tust successfully performed</b>\n⚜️Result:\n\n<blockquote>$printes</blockquote>\n<i>⚠️ The list of status code is in order of the shell list !!</i>";
    SEND_METHOD("sendMessage", array("chat_id" => $chat_idcallback_query, "text" =>$text, "parse_mode" => "HTML"));
}
//SEND_METHOD("sendMessage", array("chat_id" => $chat_id, "text" =>$Conect_json));
