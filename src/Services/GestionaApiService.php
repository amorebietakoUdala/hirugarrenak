<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use League\Csv\Writer;

#[IsGranted('ROLE_HIRUGARRENAK')]
final class GestionaApiService extends AbstractController
{
    // Example filter: {"nif":"00000000T","result":{"expand":["default-address"]}}
    private string $defaultFilter = '{"result":{"expand":["default-address"]}}';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $gestionaApiUrl,
        private readonly string $gestionaApiAccessToken,

    ) {}

    private function addFilterFromArray(array $filter): string
    {
        $filter = array_merge($filter, json_decode($this->defaultFilter, true));
        $jsonFilter = json_encode($filter, JSON_THROW_ON_ERROR);
        return $jsonFilter;
    }

    public function getThirds(?string $jsonFilter = null): array
    {
        $uri = '/thirds';
        if ($jsonFilter !== null) {
            $base64Filter = base64_encode($jsonFilter);
            $uri .= '?filter-view=' . $base64Filter;
        }

        $result = $this->httpClient->request('GET', $this->gestionaApiUrl . $uri, [
            'headers' => [
                'X-Gestiona-Access-Token' => $this->gestionaApiAccessToken,
            ]
        ]);
        
        if ($result->getStatusCode() >= 300) {
            throw new \Exception('Error fetching data from Gestiona API: ' . $result->getStatusCode());
        }

        $statusCode = $result->getStatusCode();

        if ($statusCode === 204) {
            $content = $this->createEmptyResult();
            return $content;
        }

        $response = $result->toArray();

        return $response;
    }

    public function getAllThirdsExports(?string $jsonFilter = null): array
    {
        $uri = '/thirds';
        if ($jsonFilter !== null) {
            $base64Filter = base64_encode($jsonFilter);
            $uri .= '?filter-view=' . $base64Filter;
        }
        $allThirds = [];
        do {
            $result = $this->httpClient->request('GET', $this->gestionaApiUrl . $uri, [
                'headers' => [
                    'X-Gestiona-Access-Token' => $this->gestionaApiAccessToken,
                ]
            ]);
            $response = $result->toArray(false);
            if ($result->getStatusCode() >= 300) {
                throw new \Exception('Error fetching data from Gestiona API: ' . $result->getStatusCode());
            }
            if (isset($response['content'])) {
                $allThirds = array_merge($allThirds, $response['content']);
            }
            $nextLink = null;
            if (isset($response['links'])) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] === 'next') {
                        $nextLink = $link['href'];
                        break;
                    }
                }
            }
            if ($nextLink !== null) {
                $uri = strpos($nextLink, 'http') === 0
                    ? str_replace($this->gestionaApiUrl, '', $nextLink)
                    : $nextLink;
            }
        } while ($nextLink !== null);
        $this->exportThirdstocsv(['content' => $allThirds]);
        return ['content' => $allThirds];
    }

    public function exportThirdstocsv(array $thirds): void
    {
        // Ruta completa donde quieres guardar el archivo
        $filePath = '/var/www/SF7/hirugarrenak/public/list/thirds.csv';

        // Asegurarte de que el directorio existe
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            throw new \Exception("El directorio no existe: " . $directory);
        }

        // Crear el escritor de CSV en modo escritura
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->setEscape('');

        // Insertar cabeceras y datos
        if (!empty($thirds['content'])) {
            $headers = array_keys($thirds['content'][0]);
            $csv->insertOne($headers);

            foreach ($thirds['content'] as $row) {
                $csv->insertOne(array_values($row));
            }
        }
    }


    public function getThird(string $id, $asSearchResult = true): array
    {
        $response = [];
        $result = $this->httpClient->request('GET', $this->gestionaApiUrl . '/thirds/' . $id, [
            'headers' => [
                'X-Gestiona-Access-Token' => $this->gestionaApiAccessToken,
            ]
        ]);
        if (!$asSearchResult) {
            $response['content'][0] = $result->toArray();
        } else {
            $response = $this->createEmptyResult();
            $response['content'][0] = $result->toArray();
        }

        if ($result->getStatusCode() >= 300) {
            throw new \Exception('Error fetching data from Gestiona API: ' . $result->getStatusCode());
        }

        return $response;
    }

    public function getThirdsByNif(string $nif): array
    {
        $filter = ['nif' => $nif];
        $jsonFilter = $this->addFilterFromArray($filter);
        return $this->getThirds($jsonFilter);
    }

    private function createEmptyResult(): array
    {
        return [
            'page' => 1,
            'content' => [],
            'links' => []
        ];
    }
}
