<?php
namespace App\Components\SignIn;

use App\Components\CommonComponent;
use App\Components\Forms\BaseForm;
use App\Model\User;
use App\Services\MailService;
use App\Repositories\UsersRepository;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Utils\Random;

class SignInComponent extends CommonComponent {
    /** @var UsersRepository  */
    private $usersRepository;

    /**
     * @persistent
     * @var string
     */
    public $view = 'login';

    /** @var  string */
    private $backlink;
    /** @var  MailService */
    private $mailService;
    private $token;
    private $token2;

    /**
     * SignInComponent constructor.
     * @param UsersRepository $usersRepository
     * @param MailService $mailService
     */
    public function __construct(UsersRepository $usersRepository, MailService $mailService) {
        parent::__construct();
        $this->usersRepository = $usersRepository;
        $this->mailService = $mailService;
    }

    public function render() {
        $template = parent::render();
        $template->view = $this->view;
        $template->setFile(dirname(__FILE__) . '/SignInComponent.latte');
        $template->render();
    }

    public function createForm() {
        $form = parent::createForm();
        //FIXME - recaptcha not working on localhost
        //$form->addInvisibleReCaptcha('recaptcha', $required = TRUE, $message = 'Are you a bot?');
        return $form;
    }

    /**
     * @param $token
     * @param $token2
     */
    public function setTokens($token, $token2) {
        $this->token = $token;
        $this->token2 = $token2;
    }

    /**
     * @param string $view
     */
    public function setView($view = 'login') {
        $this->view = $view;
    }

    /**
     * @param string $view
     */
    public function handleSetView($view) {
        $this->setView($view);
        $this->redrawControl();
    }

    /**
     * @param string $backlink
     */
    public function setBacklink($backlink) {
        $this->backlink = $backlink;
    }


    /**
     * @return Form
     */
    protected function createComponentLoginForm() {
        $form = $this->createForm();
        $form->addText('email', 'E-mail');
        $form->addPassword('password', 'Password')->setRequired();
        $form->addCheckbox('remember_me', 'Remember me?')
            ->getControl()->setAttribute("class", "i-checks");
        $form->addSubmit('submit', "Log in")
            ->setHtmlAttribute("class", "btn btn-block btn-success loginbtn");
        $form->onValidate[] = [$this, 'loginValidate'];
        $form->onSuccess[] = function(Form $form) {
            $this->presenter->flashMessage("Logged in successfully", 'success');
            if ($this->backlink) {
                $this->presenter->restoreRequest($this->backlink);
            } else {
                $this->redirect('this');
            }
        };
        return $form;
    }

    /**
     * @return BaseForm
     */
    protected function createComponentRegisterForm() {
        $form = $this->createForm();
        $form->addEmail('email', 'E-mail');
        $this->addPasswordInputs($form);
        $form->addCheckbox('terms', 'I agree with the terms and conditions')
            ->setRequired(true)
            ->getControl()->setAttribute("class", "i-checks");

        $form->addSubmit('submit', "Sign up")
            ->setHtmlAttribute("class", "btn btn-block btn-success");;
        $form->onValidate[] = function(Form $form) {
            $user = $this->usersRepository->getByUsername($form->values->email);
            if ($user) {
                $form->addError("User with this username already exists");
            }
        };
        $form->onSuccess[] = function(Form $form) {
            $this->usersRepository->registerUser(
                $form->values->email,
                $form->values->password
            );
            $this->presenter->flashMessage("You are now registered.", 'success');
            if ($this->backlink) {
                $this->presenter->restoreRequest($this->backlink);
            } else {
                $this->redirect('this');
            }
            $this->redirect('this');
            //TODO: Send e-mail confirmation
        };
        return $form;
    }

    /**
     * @param Form $form
     */
    public function loginValidate(Form $form) {
        try {
            // we try to log the user in
            $user = $this->presenter->getUser();
            $user->login($form->values->email, $form->values->password);
            if ($form->values->remember_me) {
                $this->presenter->user->setExpiration('14 days', FALSE);
            }
        } catch (AuthenticationException $e) {
            $form->addError(('login.error'));
        }
    }

    /**
     * @return BaseForm
     */
    protected function createComponentChangePasswordForm() {
        $form = $this->createForm();
        $form->addPassword('old_password', "Old password");
        $this->addPasswordInputs($form);
        $form->addSubmit('submit', "Change password");
        $form->onValidate[] = function(Form $form) {
            /** @var User $user */
            $user = $this->usersRepository->getById($this->presenter->user->id);
            if (!$user) {
                throw new \Exception('login.changePassword.userNotFound');
            } else {
                if (!Passwords::verify($form->values->old_password, $user->password)) {
                    $form->addError("Wrong password");
                }
            }
        };
        $form->onSuccess[] = function(Form $form) {
            $user = $this->usersRepository->getById($this->presenter->user->id);
            /** @var User $user */
            $user->password = Passwords::hash($form->values->password);
            $this->usersRepository->persistAndFlush($user);
            $this->presenter->flashMessage("Your password was successfully changed", 'success');

            $this->redirect('this');
        };
        return $form;
    }

    /**
     * @return BaseForm
     */
    protected function createComponentForgottenPasswordForm() {
        $form = $this->createForm();
        $form->addEmail('email', "E-mail");
        $form->addSubmit('submit', "Request new password");
        $form->onValidate[] = function(Form $form) {
            $user = $this->usersRepository->getByUsername($form->values->email);
            if (!$user) {
                $form->addError("User with this e-mail not found.");
            }
        };
        $form->onSuccess[] = function(Form $form) {
            //TODO: Send e-mail, assign tokens
            /** @var User $user */
            $user = $this->usersRepository->getByUsername($form->values->email);
            $user->passwordResetToken = Random::generate(25);
            $user->passwordResetToken2 = Random::generate(25);
            $user->passwordResetRequested = new \DateTime();
            $this->usersRepository->persistAndFlush($user);
            $link = $this->presenter->link('//:Front:Sign:passwordReset', [
                'token' => $user->passwordResetToken,
                'token2' => $user->passwordResetToken2
            ]);
            $this->mailService->sendPasswordResetLink($user, $link);
            $this->presenter->flashMessage($this->t->translate('login.forgottenPassword.linkSent'), 'success');
            $this->presenter->redirect(':Front:Sign:in');
        };
        return $form;
    }

    /**
     * @return BaseForm
     * @throws \Exception
     */
    protected function createComponentPasswordResetForm() {
        $form = $this->createForm();
        /** @var User $user */
        $user = $this->usersRepository->getByTokens($this->token, $this->token2);
        if (!$user) {
            $form->addError("Security tokens do not match");
            return $form;
        }
        $this->addPasswordInputs($form);
        $form->addSubmit('submit', "Reset password");
        $form->onSuccess[] = function(Form $form) {
            $user = $this->usersRepository->getByTokens($this->token, $this->token2);
            $user->setNewPassword($form->values->password);
            $this->usersRepository->persistAndFlush($user);
            $this->presenter->flashMessage("Password re-set successfully", 'success');
            $this->presenter->redirect(':Front:Sign:in');
        };
        return $form;
    }

    public function handleSignOut() {
        $this->logout();
    }

    public function logout() {
        $this->presenter->user->logout(true);
        $this->presenter->flashMessage("You are now logged out", 'success');
        $this->redirect('this');
    }

    private function addPasswordInputs(BaseForm &$form) {
        $form->addPassword('password', "Password")
            ->setRequired();
        $form->addPassword('confirm_password', "Password again")
            ->setRequired()
            ->addRule(Form::EQUAL, "Passwords do not match", $form['password']);
    }
}

interface ISignInComponentFactory {
    /**
     * @return SignInComponent
     */
    public function create();
}
