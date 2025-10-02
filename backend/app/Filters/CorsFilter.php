<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CorsFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = service('response');
            $origin = $request->getHeaderLine('Origin');

            // Set CORS headers for preflight
            $this->setCorsHeaders($response, $origin);

            $response->setHeader('Access-Control-Max-Age', '86400');
            $response->setStatusCode(200);
            return $response;
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add CORS headers to all API responses
        $origin = $request->getHeaderLine('Origin');
        $this->setCorsHeaders($response, $origin);

        return $response;
    }

    /**
     * Set CORS headers on the response
     *
     * @param ResponseInterface $response
     * @param string $origin
     */
    private function setCorsHeaders(ResponseInterface $response, string $origin = '')
    {
        // Determine allowed origin
        $allowedOrigin = $this->getAllowedOrigin($origin);

        if ($allowedOrigin) {
            $response->setHeader('Access-Control-Allow-Origin', $allowedOrigin);
        }

        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN, X-Request-ID');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');
        $response->setHeader('Access-Control-Expose-Headers', 'Content-Type, Authorization');
    }

    /**
     * Get allowed origin based on environment and origin header
     *
     * @param string $origin
     * @return string|null
     */
    private function getAllowedOrigin(string $origin = ''): ?string
    {
        // If no origin header, allow for same-origin requests
        if (empty($origin)) {
            return null;
        }

        // Development environment - allow all localhost origins
        if (ENVIRONMENT === 'development' || ENVIRONMENT === 'testing') {
            // Allow any localhost origin with any port
            if (preg_match('/^https?:\/\/localhost(:\d+)?$/', $origin) ||
                preg_match('/^https?:\/\/127\.0\.0\.1(:\d+)?$/', $origin)) {
                return $origin;
            }

            // Allow specific development domains
            $devDomains = [
                'http://localhost:3101',
                'http://localhost:3000',
                'http://localhost:3001',
            ];

            if (in_array($origin, $devDomains)) {
                return $origin;
            }
        }

        // Production environment - specify your allowed domains here
        if (ENVIRONMENT === 'production') {
            $allowedDomains = [
                'https://esgmate.cc-sustain.com',
                'https://www.esgmate.cc-sustain.com',
                'https://your-production-domain.com',
                'https://www.your-production-domain.com'
                // Add your production domains here
            ];

            if (in_array($origin, $allowedDomains)) {
                return $origin;
            }
        }

        // Fallback for development: allow common localhost ports
        if (preg_match('/^https?:\/\/localhost:(3000|3001|3101|8080|8081|9118|9218)$/', $origin)) {
            return $origin;
        }

        return null;
    }
}