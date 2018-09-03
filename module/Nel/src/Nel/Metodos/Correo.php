<?php
namespace Nel\Metodos;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;


class Correo
{
   
    public function enviarCorreo($destinatario1,$emisor1,$contrasena,$asunto,$cuerpo,$nombre,$host,$puerto)
    {
        $validar = FALSE;
        $message = new \Zend\Mail\Message();
        $html = new MimePart($cuerpo);
        $html->type = "text/html";
        
        
        $message->setEncoding("UTF-8");        
        $body = new MimeMessage();
        $body->setParts(array($html));
        $message->setBody($body);
        $message->setFrom($emisor1);
        $message->addTo($destinatario1);
        $message->setSubject($asunto);

        $smtpOptions = new \Zend\Mail\Transport\SmtpOptions();  
        $smtpOptions->setHost($host)
            ->setPort($puerto)
            ->setConnectionClass('login')
            ->setName($host)
            ->setConnectionConfig(array(
                'username' => $emisor1,
                'password' => $contrasena,
                'ssl' => 'tls',
            ));

        $transport = new \Zend\Mail\Transport\Smtp($smtpOptions);
        $transport->send($message);
        
        return $validar;
    }
}
?>