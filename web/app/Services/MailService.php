<?php
namespace App\Services;

use App\Model\Mail;
use App\Model\User;
use Latte\Engine;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Nette\Mail\SmtpMailer;

class MailService extends CommonService
{
    /** @var SmtpMailer */
    private $mailer;
    /** @var  string */
    private $systemEmail;

    /**
     * MailService constructor.
     */
    public function __construct() {
        $this->mailer = new SmtpMailer;
    }

    /**
     * @param string $systemEmail
     */
    public function setSystemEmail($systemEmail) {
        $this->systemEmail = $systemEmail;
    }

    /**
     * @param string $email
     * @param string $code
     * @param string|null $subject
     * @param string|null $body
     * @param User|null $user
     * @param \DateTime|null $scheduled
     * @return Mail
     */
    private function createMail($email, $code, $subject, $body = null, User $user = null, \DateTime $scheduled = null) {
        $mail = new Mail([
            'email' => $email,
            'code' => $code,
            'subject' => $subject,
            'body' => $body,
            'user' => $user,
            'scheduled' => $scheduled
        ]);

        return $mail;
    }

    /**
     * @param Mail $mail
     */
    private function sendNow(Mail $mail) {
        $mail->scheduled = null;
        //$this->mailsRepository->persistAndFlush($mail);
    }


    /**
     * @param User $user
     * @param $link
     * @throws \Throwable
     */
    public function sendPasswordResetLink(User $user, $link) {
        $mail = $this->createMail($user->email, Mail::CODE_PASSWORD_RESET_LINK, null, null, $user);
        $mail->body = $this->generateTemplate($mail, [
            'link' => $link,
            'user' => $user
        ]);
        $this->sendNow($mail);
    }

    /**
     * @param Mail $mail
     * @param array $parameters
     * @return string
     * @throws \Throwable
     */
    protected function generateTemplate(Mail $mail, $parameters = []) {
        $latte = new Engine();
        UIMacros::install($latte->getCompiler());
        $template = new Template($latte);
        $template->setFile(__DIR__.'/../templates/Mail/'.$mail->code.'.latte');
        $template->setParameters($parameters);
        return $template->__toString();
    }
}
