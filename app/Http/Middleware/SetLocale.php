<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * English or Dhivehi. `?lang=dv` switches and is remembered in the session,
 * so a shared link with the parameter opens in the same language.
 */
class SetLocale
{
    public const LOCALES = ['en', 'dv'];

    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');

        if (in_array($requested, self::LOCALES, true)) {
            $request->session()->put('locale', $requested);
        }

        $locale = $request->session()->get('locale', config('app.locale'));
        $locale = in_array($locale, self::LOCALES, true) ? $locale : 'en';

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
