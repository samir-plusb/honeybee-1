<?php

use Honeybee\Core\Service\DocumentService;
use Honeybee\Core\Security\Auth\TokenGenerator;

use Honeybee\Domain\User\UserDocument;

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @package User
 */
class UserService extends DocumentService
{
    public function sendPasswordLostEmail(UserDocument $user)
    {
        $user->setAuthToken(TokenGenerator::generateToken());
        $expireDate = new DateTime();
        $expireDate->add(new DateInterval('PT20M'));
        $user->setTokenExpireDate($expireDate->format(DATE_ISO8601));
        $this->save($user);

        $fullname = $user->getFirstname() . ' ' . $user->getLastname();
        $projectName = \AgaviConfig::get('core.app_name');

        $message = \Swift_Message::newInstance()
            ->setSubject($projectName . ' - Passwort zurücksetzen')
            ->setFrom(array('no-reply@honeybee.de' => $projectName . ' CMS'))
            ->setTo(array($user->getEmail() => $fullname))
            ->setBody($this->getPasswordLostEmailBody($user));

        $transport = \Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
        $mailer = \Swift_Mailer::newInstance($transport);
        
        if (! $mailer->send($message))
        {
            throw new \Exception(
                "Unable to deliver mail. Please try again later or contact the reponseable staff."
            );
        }
    }

    protected function getPasswordLostEmailBody(UserDocument $user)
    {
        $expireDate = new DateTime($user->getTokenExpireDate());

        return sprintf(
            "Bitte folgen sie dem Link anbei um ein neues Passwort zu vergeben und den Vorgang abzuschließen.\nPasswort setzen: %s\n" .
            "Der Link ist für 20 Minuten gültig und invalidiert somit ab dem Zeitpunkt: %s.\n" .
            "Sollte die Gültigkeitsdauer bereits überschritten sein, kann ein neuer Link angefordert werden.\n" . 
            "Neuen Link anfordern: %s",
            \AgaviContext::getInstance()->getRouting()->gen(
                'user.password', 
                array('token' => $user->getAuthToken())
            ),
            $expireDate->format('d.m.Y H:i:s'),
            \AgaviContext::getInstance()->getRouting()->gen('user.reset'),
            $user->getTokenExpireDate()
        );
    }
}
