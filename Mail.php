<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_Mail extends Zend_Mail
{
	public static function enviar($asunto, $mensaje, $to, $bcc = '', $fromEmail = '', $fromName = '', $html = false)
	{

		$mail = new Zend_Mail();
		$mail->setSubject(ZendR_String::parseString($asunto)->toISO()->__toString());
		if ($html) {
			$mail->setBodyHtml(ZendR_String::parseString($mensaje)->toISO()->__toString());
		} else {
			$mail->setBodyText(ZendR_String::parseString($mensaje)->toISO()->__toString());
		}

		$emails = explode(',', $to);
		foreach ($emails as $email) {
			$mail->addTo(trim($email));
		}

		$emails = explode(',', $bcc);
		foreach ($emails as $email) {
			$mail->addBcc(trim($email));
		}
        if ($fromEmail != '' && $fromName != '') {
            $mail->setFrom($fromEmail, ZendR_String::parseString($fromName)->toISO()->__toString());
        }
        $mail->send();
	}

	public static function enviarPdf($asunto, $mensaje, $to, $pathPdf, $nombrePdf, $bcc='', $fromEmail = '', $fromName = '',	$html = false)
	{
		$mail = new Zend_Mail();
		$mail->setSubject(ZendR_String::parseString($asunto)->toISO()->__toString());
		if ($html) {
			$mail->setBodyHtml(ZendR_String::parseString($mensaje)->toISO()->__toString());
		} else {
			$mail->setBodyText(ZendR_String::parseString($mensaje)->toISO()->__toString());
		}

		$emails = explode(',', $to);
		foreach ($emails as $email) {
			$mail->addTo(trim($email));
		}

		$emails = explode(',', $bcc);
		foreach ($emails as $email) {
			$mail->addBcc(trim($email));
		}

		if ($fromEmail != '' && $fromName != '') {
            $mail->setFrom($fromEmail, ZendR_String::parseString($fromName)->toISO()->__toString());
        }

		$miPdf = $pathPdf . '/' . $nombrePdf;

		$fileContents = file_get_contents($miPdf);
		$file = $mail->createAttachment($fileContents);
		$file->filename = $nombrePdf;
		try{
			$mail->send();
		} catch (Zend_Mail_Exception $e){
			throw new ZendR_Exception(ZendR_Message_Error::SEND_MAIL);
		}
	}
}
