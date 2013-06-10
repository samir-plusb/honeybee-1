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
        // @todo What shall we take for the 'from' field here?
        // we need some kind of web(cms)master-email setting ...
        $message = \Swift_Message::newInstance()
            ->setSubject($projectName . ' CMS - Kennwort zurücksetzen')
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
        $project_contact = AgaviConfig::get('core.project_contact');
        $project_name = AgaviConfig::get('core.app_name');
        $set_password_link = \AgaviContext::getInstance()->getRouting()->gen(
            'user.password', 
            array('token' => $user->getAuthToken())
        );

        $message = sprintf('
Hallo,

Sie erhalten diese Nachricht, weil im Content Management System des Projekts "%s" ein neues Passwort für Sie angefordert wurde.
Um sich neues Passwort zu erstellen, klicken Sie bitte auf den unten stehenden Link. Auf der Internetseite, die sich hinter diesem Link verbirgt, haben Sie die Möglichkeit, das neue Passwort zu hinterlegen.
            
%s
            
Aus Sicherheitsgründen wir dieser Link nach 20 Minuten inaktiv. Danach können Sie sich diese E-Mail noch einmal zuschicken lassen.
Wichtig für Sie: Auch wenn Ihr Passwort verschlüsselt abgespeichert wird, sollten Sie darauf achten, stets schwer zu erratende Passwörter zu verwenden. 
Benutzen Sie beispielsweise niemals auf zwei Internetseiten dasselbe Passwort. 

Achten Sie außerdem darauf, möglichst eine Kombination aus Groß- und Kleinbuchstaben sowie mindestens eine Zahl zu verwenden.
Wenn Sie der Auffassung sind, dass Ihnen diese E-Mail irrtümlich zugeschickt wurde, wenden Sie sich bitte an %s (%s).

Diese E-Mail wurde automatisch erstellt.',
            $project_name,
            $set_password_link,
            $project_contact['name'],
            $project_contact['email']
        );
    
        return $message;
    }
}
