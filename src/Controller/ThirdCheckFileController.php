<?php

namespace App\Controller;

use App\Entity\Default\ThirdCheckFile;
use App\Form\ThirdUploadType;
use App\Message\ProcessThirdCheckFile;
use App\Repository\Default\ThirdCheckFileRepository;
use App\Services\FileUploader;
use App\Services\ErreziboakApiService;
use App\Services\ErroldaApiService;
use App\Services\GestionaApiService;
use App\Services\ThirdFileValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_HIRUGARRENAK')]
#[Route('/{_locale}')]
final class ThirdCheckFileController extends BaseController
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ErreziboakApiService $erreziboakApiService,
        private readonly ErroldaApiService $errolda,
        private readonly GestionaApiService $gestiona,
        private readonly ThirdFileValidatorService $validator,
        private readonly string $thirdFileUploadDirectory,
        private readonly ThirdCheckFileRepository $repo,
        private readonly MessageBusInterface $bus,
    ) {}

    #[Route('/third-check-file', name: 'third_check_file_index')]
    public function index(Request $request): Response
    {
        $thirdCheckFiles = $this->repo->findBy([],['receptionDate' => 'DESC'], 50);

        $form = $this->createForm(ThirdUploadType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $file = $form['file']->getData();
            if (null === $file) {
                $this->addFlash('error', 'message.fileNotSelected');
                return $this->redirectToRoute('third_check_file_index');
            }
            $this->validator->setRequiredFields(['Dni']);
            $validationResult = $this->validator->validate($file);
            if ($validationResult['status'] !== $this->validator::VALID) {
                $this->addFlash('error', $validationResult['message']);
                return $this->render('third_check_file/index.html.twig', [
                    'thirdCheckFiles' => $thirdCheckFiles,
                    'form' => $form,
                ]);
            }
            try {
                $fileUploader = new FileUploader($this->thirdFileUploadDirectory);
                $thirdFileName = $fileUploader->upload($file);
                $data['thirdFileName'] = $thirdFileName;
                $thirdCheckFile = ThirdCheckFile::createThirdCheckFile($data);
                $this->em->persist($thirdCheckFile);
                $this->em->flush();
                $this->bus->dispatch(new ProcessThirdCheckFile($thirdCheckFile->getId()));
                $this->addFlash('success', 'message.successfullySended');

                $thirdCheckFiles = $this->repo->findBy([],['receptionDate' => 'DESC'], 50);

                return $this->render('third_check_file/index.html.twig', [
                    'thirdCheckFiles' => $thirdCheckFiles,
                    'form' => $form,
                ]);

            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }            
        }

        return $this->render('third_check_file/index.html.twig', [
            'thirdCheckFiles' => $thirdCheckFiles,
            'form' => $form,
        ]);
    }

    #[Route(path: '/third-check-file/{thirdCheckFile}/download', name: 'third_check_file_download')]
    public function download(ThirdCheckFile $thirdCheckFile)
    {
        $without_extension = pathinfo($thirdCheckFile->getFileName(), PATHINFO_FILENAME);
        $fileName = $this->thirdFileUploadDirectory . '/' . $without_extension . '.zip';
        $response = new BinaryFileResponse($fileName);
        $response->headers->set('Content-Type', 'application/zip');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $without_extension . '.zip'
        );

        return $response;
    }
}
