<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        if (array_key_exists($locale, SetLocale::SUPPORTED)) {
            session(['locale' => $locale]);
        }

        return back();
    }
}
