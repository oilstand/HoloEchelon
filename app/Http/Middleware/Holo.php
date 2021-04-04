<?php

namespace App\Http\Middleware;

use Closure;
use App\Library\HoloApp;

class Holo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $requestHost = parse_url($request->headers->get('origin'),  PHP_URL_HOST);
        $scheme = parse_url($request->headers->get('origin'), PHP_URL_SCHEME);

        $allowScheme = "https";
        switch($scheme) {
            case "http":
                $allowScheme = "http";
                break;
        }
        $allowHost = "livetimelineshift.appspot.com";
        switch($requestHost) {
            case "3333-cs-491358302187-default.asia-east1.cloudshell.dev":
                $allowHost = "3333-cs-491358302187-default.asia-east1.cloudshell.dev";
                break;
            case "holoshift.appspot.com":
                $allowHost = "holoshift.appspot.com";
                break;
            case "holoechelon.com":
                $allowHost = "holoechelon.com";
                break;
            case "www.holoechelon.com":
                $allowHost = "www.holoechelon.com";
                break;
            default:
                break;
        }
        $request->merge(array('namespace'=>HoloApp::NAMESPACE_HOLO));

        return $next($request)
            ->header('Access-Control-Allow-Origin', $allowScheme."://".$allowHost)
            ->header('Access-Control-Allow-Methods', 'GET');
    }
/*    public function handle($request, Closure $next)
    {
        return $next($request);
    }*/
}
