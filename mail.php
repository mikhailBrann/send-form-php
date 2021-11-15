<?php

class SendMail {
    public function castomSendMail($to, $from, $replyTo) {
        global $_POST;
        global $_FILES;

        $EOL = "\r\n"; // ограничитель строк, некоторые почтовые сервера требуют \n - подобрать опытным путём
        $boundary     = "--".md5(uniqid(time()));  // любая строка, которой не будет ниже в потоке данных.
        $subject_text = !empty($_POST['форма']) ? $_POST['форма'] : 'сообщение с сайта';
        $subject= '=?utf-8?B?' . base64_encode($subject_text) . '?=';

        #Заполняем письмо
        $message = '';

        //антиспам поле
        if(!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if($key != 'E-mail') {
                    $message .= "{$key} :  {$value}<br>";
                }
            }
        }

        #Закончили заполнять

        $headers    = "MIME-Version: 1.0;" . $EOL . "";
        $headers   .= "Content-Type: multipart/mixed; boundary=\"" . $boundary . "\"" . $EOL . "";
        $headers   .= "From: {$from}\nReply-To: {$replyTo}\n";
        $multipart  = "--" . $boundary . $EOL;
        $multipart .= "Content-Type: text/html; charset=utf-8" . $EOL . "";
        $multipart .= "Content-Transfer-Encoding: base64" . $EOL . "";
        $multipart .= $EOL; // раздел между заголовками и телом html-части
        $multipart .= chunk_split(base64_encode(strip_tags($message, '<br>')));

        #начало вставки файлов
        if(!empty($_FILES['filesArr'])){
            foreach($_FILES["filesArr"]["name"] as $key => $value){
                $filename = $_FILES["filesArr"]["tmp_name"][$key];
                $file = fopen($filename, "rb");
                $data = fread($file,  filesize( $filename ) );
                fclose($file);
                $NameFile = $_FILES["filesArr"]["name"][$key]; // в этой переменной надо сформировать имя файла (без всякого пути);
                $File = $data;
                $multipart .=  "" . $EOL . "--" . $boundary . $EOL . "";
                $multipart .= "Content-Type: application/octet-stream; name=\"" . $NameFile . "\"" . $EOL . "";
                $multipart .= "Content-Transfer-Encoding: base64" . $EOL . "";
                $multipart .= "Content-Disposition: attachment; filename=\"" . $NameFile . "\"" . $EOL . "";
                $multipart .= $EOL; // раздел между заголовками и телом прикрепленного файла
                $multipart .= chunk_split(base64_encode($File));

            }
        }

        #конец вставки файлов
        $multipart .= "" . $EOL . "--" . $boundary . "--" . $EOL . "";

        if(!mail($to, $subject, $multipart, $headers)){
            echo 'Письмо не отправлено';
        } //Отправляем письмо
        else{
            echo 'Письмо отправлено';
        }
    }
}





$mailEven = new SendMail;
$mailEven->castomSendMail('test1.ru', 'webmaster@new-site.souse71.ru', 'webmaster@new-site.souse71.ru');
?>