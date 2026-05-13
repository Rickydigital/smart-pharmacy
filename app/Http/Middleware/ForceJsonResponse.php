<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if ($response instanceof JsonResponse
            || $response instanceof BinaryFileResponse
            || $response instanceof StreamedResponse) {
            return $response;
        }

        if ($response instanceof RedirectResponse) {
            return $this->redirectToJson($request, $response);
        }

        if ($response instanceof LaravelResponse) {
            $original = $response->getOriginalContent();

            if ($original instanceof ViewContract) {
                return response()->json([
                    'success' => true,
                    'data' => $original->getData(),
                ], $response->getStatusCode());
            }
        }

        return $response;
    }

    private function redirectToJson(Request $request, RedirectResponse $response): JsonResponse
    {
        $message = null;
        $errors = null;

        if ($request->hasSession()) {
            $session = $request->session();
            $message = $session->get('success')
                ?? $session->get('status')
                ?? $session->get('error');

            $errors = $this->formatErrors($session->get('errors'));
        }

        $isError = filled($errors) || ($request->hasSession() && $request->session()->has('error'));

        return response()->json([
            'success' => ! $isError,
            'message' => $message ?? ($isError ? 'The request could not be completed.' : 'Request completed successfully.'),
            'errors' => $errors,
        ], $isError ? 422 : 200);
    }

    private function formatErrors(mixed $errors): ?array
    {
        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        $formatted = [];

        foreach ($errors->getBags() as $name => $bag) {
            $formatted[$name] = $bag->toArray();
        }

        return $formatted ?: null;
    }
}
