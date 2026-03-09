<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotReservedSlug implements ValidationRule
{
    /**
     * Reserved slugs that must not be used as product/collection slugs
     * to avoid routing conflicts.
     */
    protected static array $reserved = [
        'admin',
        'api',
        'search',
        'cart',
        'checkout',
        'account',
        'login',
        'logout',
        'register',
        'password',
        'storage',
        'assets',
        'css',
        'js',
        'img',
        'images',
        'uploads',
        'favicon',
        'robots',
        'sitemap',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (in_array(strtolower($value), static::$reserved, true)) {
            $fail("The slug \":input\" is reserved and cannot be used.");
        }
    }
}

