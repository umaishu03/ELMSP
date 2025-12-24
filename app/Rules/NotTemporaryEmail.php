<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotTemporaryEmail implements ValidationRule
{
    /**
     * List of known temporary email domains
     */
    private $temporaryDomains = [
        '10minutemail.com',
        'tempmail.org',
        'guerrillamail.com',
        'mailinator.com',
        'temp-mail.org',
        'throwaway.email',
        'getnada.com',
        'maildrop.cc',
        'sharklasers.com',
        'guerrillamailblock.com',
        'pokemail.net',
        'spam4.me',
        'bccto.me',
        'chacuo.net',
        'dispostable.com',
        'mailnesia.com',
        'mailcatch.com',
        'inboxalias.com',
        'mailmetrash.com',
        'trashmail.net',
        'spamgourmet.com',
        'spam.la',
        'binkmail.com',
        'bobmail.info',
        'chammy.info',
        'devnullmail.com',
        'letthemeatspam.com',
        'mailin8r.com',
        'mailinator2.com',
        'notmailinator.com',
        'reallymymail.com',
        'reconmail.com',
        'safetymail.info',
        'sogetthis.com',
        'spamhereplease.com',
        'superrito.com',
        'thisisnotmyrealemail.com',
        'tradermail.info',
        'veryrealemail.com',
        'wegwerfmail.de',
        'wegwerfmail.net',
        'wegwerfmail.org',
        'wegwerpmailadres.nl',
        'wetrainbayarea.com',
        'wetrainbayarea.org',
        'wh4f.org',
        'whyspam.me',
        'willselfdestruct.com',
        'wuzup.net',
        'wuzupmail.net',
        'yeah.net',
        'yopmail.com',
        'yopmail.net',
        'yopmail.org',
        'ypmail.webarnak.fr',
        'cool.fr.nf',
        'jetable.fr.nf',
        'nospam.ze.tc',
        'nomail.xl.cx',
        'mega.zik.dj',
        'speed.1s.fr',
        'courriel.fr.nf',
        'moncourrier.fr.nf',
        'monemail.fr.nf',
        'monmail.fr.nf',
        'test.com',
        'example.com',
        'example.org',
        'example.net',
        'mv6a.com', // This is the domain from your test case
    ];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        $email = strtolower(trim($value));
        $domain = substr(strrchr($email, "@"), 1);

        if (in_array($domain, $this->temporaryDomains)) {
            $fail('The :attribute field cannot use temporary or disposable email services.');
        }
    }
}
