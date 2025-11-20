<?php

namespace App\MessageHandler;

use App\Entity\Default\ThirdCheckFile;
use App\Message\ProcessThirdCheckFile;
use App\Repository\Default\ThirdCheckFileRepository;
use App\Services\ErreziboakApiService;
use App\Services\ErroldaApiService;
use App\Services\GestionaApiService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ProcessThirdCheckFileHandler
{
   public function __construct(
      private string $thirdFileUploadDirectory,
      private EntityManagerInterface $em,
      private ThirdCheckFileRepository $repo,
      private readonly ErreziboakApiService $erreziboakApiService,
      private readonly ErroldaApiService $errolda,
      private readonly GestionaApiService $gestiona,
   )
   {
      
   }

    public function __invoke(ProcessThirdCheckFile $message)
    {
      try {
         $thirdCheckFileId = $message->getThirdCheckFileId();
         $thirdCheckFile = $this->repo->find($thirdCheckFileId);
         $results = $this->processThirdCheckFile($thirdCheckFile);
         // For debugging purposes
         // dump($results);
         $thirdCheckFile->setProcessedDate(new \DateTime());
         $thirdCheckFile->setStatus(ThirdCheckFile::STATUS_PROCESSED);
         $this->em->persist($thirdCheckFile);
         $this->em->flush();
      }
      catch (Exception $e) {

      }
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

    private function processThirdCheckFile(ThirdCheckFile $thirdCheckFile): array
    {
        $file = $this->thirdFileUploadDirectory . '/' . $thirdCheckFile->getFileName();
        $csv = Reader::from($file);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        $content = [];
        foreach ($records as $offset => $record) {
            $dni = $record['Dni'];
            $languagePreferenceResult = $this->checkLanguagePreference($dni);
            $erroldaResult = $this->checkErrolda($dni);
            $debtsResult = $this->checkDebts($dni);
            $result = $this->fillContent($languagePreferenceResult, $erroldaResult, $debtsResult);
            $content[] = [$result];
        }
        $this->writeDebtsCsvFile($this->thirdFileUploadDirectory, $thirdCheckFile, $content);
        $this->zipDebtsFile($this->thirdFileUploadDirectory, $thirdCheckFile);

        return $content;
    }

    private function fillContent(array|null $languagePreferenceResult, array|null $erroldaResult, array|null $debtsResult): array {
        $content = [
            'dni' => null,
            'full_name' => null,
            'language_preference' => null,
            'erroldatua' => null,
            'debts' => null,
        ];
        if ( $languagePreferenceResult !== null ) {
            if (isset($languagePreferenceResult['error'])) {
                $content['language_preference'] = 'Error: ' . $languagePreferenceResult['error'];
            } else if ( count( $languagePreferenceResult['content'] ) == 1 ) {
                    $record = $languagePreferenceResult['content'][0];
                    $content['dni'] = $record['nif'];
                    $content['full_name'] = $record['full_name'];
                    $content['language_preference'] = isset($record['language_preference']) ? $record['language_preference'] : '';
                } else {
                    $content['language_preference'] = isset($record['language_preference']) ? $record['language_preference'] : '';
                }
        }
        if ( $erroldaResult !== null ) {
            if ( count( $erroldaResult['content'] ) == 1 ) {
                $record = $erroldaResult['content'][0];
                if ( !isset($content['dni']) ) {
                    $content['dni'] = $record['nif'];
                } 
                if ( !isset($content['full_name']) ) {
                    $content['full_name'] = $record['full_name'];
                }
                if ( !isset($content['erroldatua']) ) {
                    $content['erroldatua'] = $record['erroldatua'] ? '1': '0';
                }
            } else {
                $content['erroldatua'] = '0';
            }
        }
        if ( $debtsResult !== null ) {
            $record = $debtsResult['content'][0];
            if ( !isset($content['dni']) ) {
                $content['dni'] = $record['nif'];
            } 
            if ( !isset($content['full_name']) ) {
                $content['full_name'] = $record['full_name'];
            }
            if ( !isset($content['debts']) ) {
                $content['debts'] = $record['debts'] ? '1' : '0';
            }
        }

        return $content;
    }

    private function writeDebtsCsvFile(string $path, ThirdCheckFile $thirdCheckFile, array $content)
    {
        $file = $path . '/' . $thirdCheckFile->getFileName() . '-processed.csv';
        $csv = Writer::from(new \SplFileObject($file, 'w+'));
        $csv->setDelimiter(';');
        $csv->setEndOfLine("\r\n");
        $headers = array_keys((array_values($content)[0])[0]);
        $csv->insertOne($headers);
        foreach ($content as $key => $value) {
            $csv->insertOne($value[0]);
        }
        $csv->output();
    }

    private function zipDebtsFile(string $path, ThirdCheckFile $thirdCheckFile)
    {
        $without_extension = pathinfo($thirdCheckFile->getFileName(), PATHINFO_FILENAME);
        $zipFilename = $path . '/' . $without_extension . '.zip';
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilename, \ZipArchive::CREATE)) {
            exit("cannot open <$zipFilename>\n");
        }
        $fullPath = $path . '/' . $thirdCheckFile->getFileName();
        $zip->addFile($fullPath, $without_extension . '.txt');
        $zip->addFile($fullPath . '-processed.csv', $without_extension . '-processed.csv');
        $zip->close();

        return $zipFilename;
    }
}