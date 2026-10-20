<?php

namespace App\Http\Middleware;

use App\Services\Seo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoResponse
{
    public function __construct(private Seo $seo) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $publicPage = $request->routeIs('home', 'workshop.show', 'policies');
        $discovery = $request->routeIs('sitemap', 'robots');

        if ($this->seo->indexingEnabled() && config('seo.canonical_redirects') && $request->isMethodSafe()
            && ($publicPage || $discovery) && $response->isSuccessful()) {
            $origin = rtrim(config('app.url'), '/');
            $path = '/'.ltrim(rtrim($request->getPathInfo(), '/'), '/');
            if ($request->getSchemeAndHttpHost() !== $origin || $request->getPathInfo() !== $path) {
                $query = $request->getQueryString();

                return redirect()->to($origin.$path.($query ? '?'.$query : ''), 301);
            }
        }

        if (! $this->seo->indexingEnabled() || (! $publicPage && ! $request->routeIs('portfolio.image')) || $request->routeIs('policies') || $response->getStatusCode() >= 400) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }
        if ($request->is('admin', 'admin/*', 'workshop-admin', 'workshop-admin/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
