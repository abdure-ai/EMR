<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Code => native display name, in the order shown in language switchers.
     */
    public const SUPPORTED = [
        'en' => 'English',
        'am' => 'አማርኛ',
        'ar' => 'العربية',
        'om' => 'Afaan Oromoo',
    ];

    public const RTL = ['ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', 'en');

        if (! array_key_exists($locale, self::SUPPORTED)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
