<?php

use Honeybee\Core\Service\DocumentService;
use Honeybee\Core\Security\Auth\TokenGenerator;
use Honeybee\Core\Mail\MailService;
use Honeybee\Agavi\Logging;

use Honeybee\Domain\User\UserDocument;

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 * @package User
 */
class UserService extends DocumentService
{
    public function sendPasswordLostEmail(UserDocument $user)
    {
        $tm = \AgaviContext::getInstance()->getTranslationManager();
        $lm = \AgaviContext::getInstance()->getLoggerManager();

        $user->setAuthToken(TokenGenerator::generateToken());
        $expireDate = new DateTime();
        $expireDate->add(new DateInterval('PT20M'));
        $user->setTokenExpireDate($expireDate->format(DATE_ISO8601));
        $this->save($user);

        $current_language = $tm->getCurrentLocaleIdentifier();
        $user_language = $user->getLanguage();
        if (empty($user_language)) {
            $user_language = $current_language;
        }

        // TODO use locale of user instead of language of the user document?
        $tm->setLocale($user_language);

        $user_password_link = \AgaviContext::getInstance()->getRouting()->gen('user.password', array('token' => $user->getAuthToken()));

        $mail_service = $user->getModule()->getService('mail');
        $project_contact = \AgaviConfig::get('core.project_contact');

        $message = $mail_service->createMessageFromTemplate(
            'ResetPassword/ResetPassword',
            array(
                'user_password_link' => $user_password_link,
                'user' => $user,
                'project_contact' => $project_contact
            )
        );

        $message->setSender(array('familienportal@plus-b.net' => $tm->_('brand-name', 'modules.labels')));
        $message->setFrom(array($project_contact['email'] => $tm->_('brand-name', 'modules.labels')));
        $message->setTo(array($user->getEmail() => $user->getFirstname() . ' ' . $user->getLastname()));
        $message->setReplyTo(array($project_contact['email'] => $project_contact['name']));

        try {
            $info = $mail_service->send($message);
        } catch (\Exception $e) {
            $lm->logTo(
                'mail',
                Logging\Logger::ERROR,
                __METHOD__,
                array("Unable to send ResetPassword email for", $user, "- exception was:", $e, $message)
            );

            $tm->setLocale($current_language);

            throw new \Exception(
                $tm->_("Unable to send email. Please try again later or contact the responsible staff.", "user.errors")
            );
        }

        if (count($info[MailService::FAILED_RECIPIENTS]) !== 0) {
            $lm->logTo(
                'mail',
                Logging\Logger::ERROR,
                __METHOD__,
                array("Failed to send ResetPassword email for", $user, "- return value was:", $info)
            );

            $tm->setLocale($current_language);

            throw new \Exception(
                $tm->_("Unable to deliver email correctly. Please try again later or contact the responsible staff.", "user.errors")
            );
        }

        $tm->setLocale($current_language);
    }
}
