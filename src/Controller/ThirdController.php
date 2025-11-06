<?php

namespace App\Controller;

use App\Form\ThirdSearchType;
use App\Form\ThirdType;
use App\Services\ErreziboakApiService;
use App\Services\ErroldaApiService;
use App\Services\GestionaApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_HIRUGARRENAK')]
#[Route('/{_locale}')]
final class ThirdController extends BaseController
{

    public function __construct(
        private readonly ErreziboakApiService $erreziboakApiService,
        private readonly ErroldaApiService $errolda,
        private readonly GestionaApiService $gestiona,
    ) {}

    #[Route('/third', name: 'third_index')]
    public function index(Request $request): Response
    {
        $thirds = [];
        $operation = null;
        $form = $this->createForm(ThirdSearchType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ( ( isset($data['id']) === false && isset($data['nif']) === false ) || (empty($data['id']) && empty($data['nif'])) ) {
                $this->addFlash('error', 'message.fillAtLeastOneField');

                return $this->render('third/index.html.twig', [
                    'thirds' => $thirds,
                    'form' => $form,
                ]);
            }
            switch ($data['check']) {
                case ThirdSearchType::CHECK_CHOICES['check.language']:
                    if (isset($data['nif'])) {
                        $operation = ThirdSearchType::CHECK_CHOICES['check.language'];
                        $thirds = $this->checkLanguagePreference($data['nif']);
                    } else {
                        $this->addFlash('error', 'message.nifRequired');
                    }
                    break;
                case ThirdSearchType::CHECK_CHOICES['check.errolda']:
                    if (isset($data['nif'])) {
                        $operation = ThirdSearchType::CHECK_CHOICES['check.errolda'];
                        $thirds = $this->checkErrolda($data['nif']);
                    } else {
                        $this->addFlash('error', 'message.nifRequired');
                    }
                    break;
                case ThirdSearchType::CHECK_CHOICES['check.debts']:
                    $operation = ThirdSearchType::CHECK_CHOICES['check.debts'];
                    if (isset($data['nif'])) {
                        $thirds = $this->checkDebts($data['nif']);
                    } else {
                        $this->addFlash('error', 'message.nifRequired');
                    }
                    break;
            }
        }

        return $this->render('third/index.html.twig', [
            'thirds' => $thirds,
            'operation' => $operation,
            'form' => $form,
        ]);
    }

    private function checkLanguagePreference(string $nif): array
    {
        $thirds = $this->gestiona->getThirdsByNif($nif);
        return $thirds;
    }

    private function checkErrolda(string $nif): array
    {
        $thirds = [
            'page' => 1,
            'content' => [],
            'links' => []
        ];
        $habitante = $this->errolda->getActiveCitizenByNif($nif);
        if ($habitante != null) {
            $content = $thirds['content'];
            $habitante = 
            [
                'nif' => $habitante->getDni(),
                'full_name' => $habitante->getFullName(),
                'erroldatua' => true,
            ];
            $content = array_merge($content, $habitante);
            $thirds['content'][] = $habitante;
        }
        return $thirds;
    }

    private function checkDebts(string $nif): array
    {
        $thirds = [
            'page' => 1,
            'content' => [],
            'links' => []
        ];
        $debts = $this->erreziboakApiService->getHasDebts($nif);
        if ($debts !== null) {
            $content = $thirds['content'];
            $content = array_merge($content, $debts);
            $thirds['content'][] = $content;
        }
        return $thirds;
    }
  

    #[Route('/third/export/{maxResults}', name: 'third_index_export', defaults: ['maxResults' => 100])]
    public function indexexport(Request $request, int $maxResults): Response
    {
        $form = $this->createForm(ThirdSearchType::class);
        $form->handleRequest($request);
        $jsonFilter = json_encode([
            'result' => [
                'max_results' => $maxResults,
                'expand' => []
            ]
        ]);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (isset($data['id'])) {
                $id = $data['id'];
                $thirds = $this->gestiona->getThird($id);
            } elseif (isset($data['nif'])) {
                $nif = $data['nif'];
                $thirds = $this->gestiona->getThirdsByNif($nif);
            } else {
                $thirds = $this->gestiona->getAllThirdsExports($jsonFilter);
            }

            return $this->render('third/index.html.twig', [
                'thirds' => $thirds,
                'form' => $form,
            ]);
        }
        

        $thirds = $this->gestiona->getAllThirdsExports($jsonFilter);
        return $this->render('third/index.html.twig', [
            'thirds' => $thirds,
            'form' => $form,
        ]);
    }

    #[Route(path: '/third/{id}/edit', name: 'third_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, $id): Response
    {
        $this->loadQueryParameters($request);
        $third = $this->gestiona->getThird($id, false);
        if (isset($third['default_address'])) {
            $third = array_merge($third, $third['default_address']);
        }
        $form = $this->createForm(ThirdType::class, $third, [
            'readonly' => false,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // TODO update the third throw the API
            $this->addFlash('success', 'message.computer.saved');
            return $this->redirectToRoute('computer_index');
        }

        $template = $request->isXmlHttpRequest() ? '_form.html.twig' : 'edit.html.twig';

        return $this->render('third/' . $template, [
            'form' => $form,
            'third' => $third,
            'readonly' => false
        ]);
    }

    #[Route(path: '/third/{id}', name: 'third_show', methods: ['GET', 'POST'])]
    public function show(Request $request, $id): Response
    {
        $this->loadQueryParameters($request);
        $third = $this->gestiona->getThird($id, false);
        
        if (isset($third['default_address'])) {
            $third = array_merge($third, $third['default_address']);
        }

        $form = $this->createForm(ThirdType::class, $third, [
            'readonly' => true,
        ]);

        $template = $request->isXmlHttpRequest() ? '_form.html.twig' : 'edit.html.twig';
        return $this->render('third/' . $template, [
            'third' => $third,
            'form' => $form,
            'readonly' => true
        ]);
    }
}
