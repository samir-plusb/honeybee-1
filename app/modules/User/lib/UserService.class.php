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
        $user->setAuthToken(TokenGenerator::generateToken());
        $expireDate = new DateTime();
        $expireDate->add(new DateInterval('PT20M'));
        $user->setTokenExpireDate($expireDate->format(DATE_ISO8601));
        $this->save($user);

        $current_language = \AgaviContext::getInstance()->getTranslationManager()->getCurrentLocaleIdentifier();
        $user_language = $user->getLanguage();
        if (empty($user_language))
        {
            $user_language = $current_language;
        }

        // TODO use locale of user instead of language of the user document?
        \AgaviContext::getInstance()->getTranslationManager()->setLocale($user_language);

        $user_password_link = \AgaviContext::getInstance()->getRouting()->gen('user.password', array('token' => $user->getAuthToken()));

        $mail_service = $user->getModule()->getService('mail');

        $message = $mail_service->createMessageFromTemplate('ResetPassword/ResetPassword', array(
            'user_password_link' => $user_password_link,
            'user' => $user,
        ));

        $message->setFrom(array('no-reply@honeybee.de' => \AgaviConfig::get('core.app_name') . ' CMS'));
        $message->setTo(array($user->getEmail() => $user->getFirstname() . ' ' . $user->getLastname()));

        $info = $mail_service->send($message);

        if (count($info[MailService::FAILED_RECIPIENTS]) !== 0)
        {
            \AgaviContext::getInstance()->getLoggerManager()->logTo(
                null,
                Logging\Logger::ERROR,
                __METHOD__,
                array("Failed to send ResetPassword email for", $user, "- return value was:", $info)
            );

            throw new \Exception("Unable to deliver mail correctly. Please try again later or contact the responsible staff.");
        }

        \AgaviContext::getInstance()->getTranslationManager()->setLocale($current_language);
    }
}
