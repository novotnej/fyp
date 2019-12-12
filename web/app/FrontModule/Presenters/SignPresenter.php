<?php

namespace App\FrontModule\Presenters;

use App\Components\SignIn\ISignInComponentFactory;

class SignPresenter extends BasePresenter {
    /**
     * @var string
     * @persistent
     */
    public $backlink;

    public function renderIn() {
        $this['signInComponent']->setView('login');
        $this->template->title = 'Sign in';
        $this->setView('default');
    }

    public function actionOut() {
        if ($this->user->isLoggedIn()) {
            $this['signInComponent']->logout();
        } else {
            $this->redirect('in');
        }
    }

    public function actionDefault() {
        $this->redirect('in');
    }

    public function renderUp() {
        $this['signInComponent']->setView('register');
        $this->template->title = 'Register';
        $this->setView('default');
    }

    public function renderPasswordReset($token, $token2) {
        $this['signInComponent']->setView('passwordReset');
        $this['signInComponent']->setTokens($token, $token2);
        $this->template->title = "Password reset";
        $this->setView('default');
    }

    public function renderForgottenPassword() {
        $this['signInComponent']->setView('forgottenPassword');
        $this->template->title = "Forgotten password";
        $this->setView('default');
    }

    protected function createComponentSignInComponent() {
        $signInComponent = $this->signInComponentFactory->create();
        $signInComponent->setBackLink($this->backlink);
        return $signInComponent;
    }

    /** @var ISignInComponentFactory @inject */
    public $signInComponentFactory;
}
