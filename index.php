<?php

class emailer{
    private $sender;
    private $recipients;
    private $subject;
    private $body;

    function __construct($sender)
    {
        $this -> sender = $sender;
        $this -> recipients = [];
    }

    public function addRecipients($recipient){
        array_push($this->recipients, $recipient);
    }

    public function setSubject($subject){
        $this->subject = $subject;
    }

    public function setBody($body){
        $this->body = $body;
    }

    public function sendEmail(){
        foreach ($this->recipients as $recipient){
            $result = mail(
                $recipient,
                $this->subject,
                $this->body,
                "From : {$this->sender}" );
            if($result)
                echo "邮件成功发送到：{$recipient}";
        }
    }
}

function __autoload( $className )
{
    echo $className;
}
// ------------------------ client code ---------------------------

$emailer = new emailer("1162097842@qq.com");

var_dump($emailer);

//$obj = new MyClass();

exit();
// --------------------------- ThinkPHP ----------------------------------

define('APP_DEBUG', true); // debug mode
define('APP_PATH', './Application/'); // apps dir, only one
define('RUNTIME_PATH', '/tmp/tp-runtime/'); // runtime dir, need writable
require './ThinkPHP/ThinkPHP.php';
