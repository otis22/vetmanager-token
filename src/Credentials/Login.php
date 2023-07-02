<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use ElegantBro\Interfaces\Stringify;

use function preg_match;

final class Login implements Stringify
{
    /**
     * @var string
     */
    protected $login;

    /**
     * Login constructor.
     *
     * @param string $login
     */
    public function __construct(string $login)
    {
        $this->login = $login;
    }

    private function isNotValid(): bool
    {
        return !preg_match("/^[-_A-Za-z0-9]{4,}$/i", $this->login);
    }

    /**
     * @inheritDoc
     */
    public function asString(): string
    {
        if ($this->isNotValid()) {
            throw new \Exception("Login is not valid. "
                . "Login must contains only latin letters and can't be less than 4 symbols");
        }
        return $this->login;
        /* TODOL

        валидатор пароля
        валидатор логина  login: /^[-_A-Za-z0-9]{4,}$/i
       passwordSingle: function(v) {
        var me = this,
            reg = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/g;
        if (v && !reg.test(v)) {
            return _t('Пароль введен неверно. Пароль должен быть не короче 8 символов и содержать ' +
                'цифры и латинские символы (0-9, a-Z)');
        } else {
            return true;
        }
    },

*/
    }
}
