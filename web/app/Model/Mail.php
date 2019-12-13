<?php
namespace App\Model;
use Nextras\Dbal\Utils\DateTimeImmutable;

/**
 * Class Mail
 * @package App\Model
 * @property string $subject
 * @property string $code {enum static::CODE_*}
 * @property string $body
 * @property string $email
 * @property User|null $user {m:1 User, oneSided=true} {default null}
 * @property DateTimeImmutable|null $scheduled {default null}
 * @property DateTimeImmutable|null $sent {default null}
 */
class Mail extends CommonModel {
    const CODE_PASSWORD_RESET_LINK = 'passwordResetLink';
}
