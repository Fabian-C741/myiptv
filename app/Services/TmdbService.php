<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.themoviedb.org/3';
    protected $language;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.key');
        $this->language = config('services.tmdb.language', 'es-ES');
    }

    /**
     * Buscar película o serie por nombre
     */
    public function search($name, $type = 'movie')
    {
        if (!$this->apiKey) return null;

        $endpoint = ($type === 'movie') ? '/search/movie' : '/search/tv';

        try {
            $response = Http::get($this->baseUrl . $endpoint, [
                'api_key' => $this->apiKey,
                'query' => $name,
                'language' => $this->language
            ]);

            if ($response->successful()) {
                return $response->json()['results'][0] ?? null;
            }
        } catch (\Exception $e) {
            Log::error("TMDB Search Error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Obtener detalles extendidos
     */
    public function getDetails($id, $type = 'movie')
    {
        if (!$this->apiKey) return null;

        $endpoint = ($type === 'movie') ? "/movie/{$id}" : "/tv/{$id}";

        try {
            $response = Http::get($this->baseUrl . $endpoint, [
                'api_key' => $this->apiKey,
                'language' => $this->language,
                'append_to_response' => 'images,videos'
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error("TMDB Details Error: " . $e->getMessage());
        }

        return null;
    }
}
