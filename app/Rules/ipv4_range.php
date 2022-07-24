<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ipv4_range implements Rule
{
    private $regex;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->regex = "^(((\d{1,2})|(1\d{2})|(2[0-4]\d)|(25[0-5])|\*)\.){3}((\d{1,2})|(1\d{2})|(2[0-4]\d)|(25[0-5])|\*)$^";
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return preg_match($this->regex, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid IP adress.';
    }
}
