<?php

namespace App\Services;

use App\Entity\Errolda\Habitante;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[IsGranted('ROLE_HIRUGARRENAK')]
final class ErreziboakApiService extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $erreziboakApiUrl,
        private string $erreziboakApiUser,
        private string $erreziboakApiPassword,
    ) {}

    public function getHasDebts(string $nif): ?array
    {
        $response = $this->httpClient->request('GET', "$this->erreziboakApiUrl/person/$nif/has-debts", [
        'headers' => [
            'Authorization' => 'Basic '. base64_encode($this->erreziboakApiUser . ':' . $this->erreziboakApiPassword),
        ],
        ]);
        $statusCode = $response->getStatusCode();
        if($statusCode  == 200) {
            $contentArray = $response->toArray();
        }
        return $contentArray["data"];
    }

}
