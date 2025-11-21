<?php

namespace App\Services;

use App\Utils\Validaciones;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use League\Csv\Reader;
use League\Csv\CharsetConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Validaciones.
 *
 * @author ibilbao
 */
class ThirdFileValidatorService
{

   public const VALID = 0;
   public const TOO_MUCH_FIELDS = 1;
   public const INCORRECT_FIELD_NAMES = 2;
   public const MISSING_VALUES_ON_REQUIRED_FIELDS = 3;
   public const IMPORTE_NOT_NUMBERIC = 4;
   public const INVALID_DNI = 7;
   public const TOO_FEW_FIELDS = 8;

   protected $validHeaders = [
      'DNI',
   ];

   protected $requiredFields = [
      'DNI',
   ];


   public function __construct( 
      private TranslatorInterface $translator
   )
   {
      
   }

   public function validate(UploadedFile $file): ?array
    {
        $csv = Reader::from($file->getPath() . DIRECTORY_SEPARATOR . $file->getFilename() . $file->getExtension(), 'r');
        $csv->setHeaderOffset(0); //set the CSV header offset
        $csv->setDelimiter(';');
        $header = $csv->getHeader();
        $encoder = (new CharsetConverter())->inputEncoding('Windows-1252');

        $headerValidation = $this->validateHeader($header);
        if (null !== $headerValidation) {
            return $headerValidation;
        }
        $counters = $this->createCounters();
        $records = $encoder->convert($csv);
        $numFila = 2;
        foreach ($records as $record) {
            foreach (array_values($record) as $key => $value) {
                if (empty($value)) {
                    $counters[array_keys($record)[$key]] = $counters[array_keys($record)[$key]] + 1;
                }
            }
            $recordValidation = $this->validateRecord($record, $numFila);
            if (null !== $recordValidation) {
                return $recordValidation;
            }
            $numFila += 1;
        }

        return $this->checkRequiredFields($counters);
    }

    protected function getValidationMessage($key, $invalidRow, $invalidValue)
    {
        return $this->translator->trans(
            $key,
            [
                '%invalid_row%' => $invalidRow,
                '%invalid_value%' => $invalidValue,
            ],
            'validators'
        );
    }

    protected function validateHeader($header)
    {
        if (count($this->validHeaders) > count($header)) {
            return [
                'status' => self::TOO_FEW_FIELDS,
                'message' => $this->getHeaderValidationErrorMessage('too_few_fields', implode(',', array_diff($this->validHeaders, $header))),
            ];
        }
        if (count($this->validHeaders) !== count($header)) {
            return [
                'status' => self::TOO_MUCH_FIELDS,
                'message' => $this->getHeaderValidationErrorMessage('too_much_fields', implode(',', array_diff($header, $this->validHeaders))),
            ];
        }
        $diff = array_diff($header, $this->validHeaders);
        if (count($diff) > 0) {
            return [
                'status' => self::INCORRECT_FIELD_NAMES,
                'message' => $this->getHeaderValidationErrorMessage('incorrect_field_names', implode(',', $diff)),
            ];
        }

        return null;
    }

    protected function getHeaderValidationErrorMessage($key, $invalidHeaders)
    {
        return $this->translator->trans($key,[
            '%invalid_headers%' => $invalidHeaders,
            '%valid_headers%' => $this->validHeaders
        ],'validators');
    }

    private function validateRecord($record, $numFila)
    {
        if ( in_array('DNI', $this->requiredFields) ) {
            $dniValidation = $this->validateDni($numFila, in_array('DNI', $this->requiredFields) ? $record['DNI'] : null);
            if (null !== $dniValidation) {
                return $dniValidation;
            }
        }
        return null;
    }

    private function validateDni($numFila, $dni)
    {
        if (!empty($dni) && Validaciones::valida_nif_cif_nie($dni) <= 0) {
            return [
                'status' => self::INVALID_DNI,
                'message' => $this->getValidationMessage('invalid_dni', $numFila, $dni),
            ];
        }

        return null;
    }

    protected function checkRequiredFields($counters)
    {
        $fields_with_missing_values = [];
        foreach ($this->requiredFields as $field) {
            if ($counters[$field] > 0) {
                $fields_with_missing_values[] = $field;
            }
        }
        if (0 !== count($fields_with_missing_values)) {
            return [
                'status' => self::MISSING_VALUES_ON_REQUIRED_FIELDS,
                'message' => $this->translator->trans('fields_with_missing_values', [
                    '%fields%' => implode(',', $fields_with_missing_values),
                ], 'validators'),
            ];
        }

        return [
            'status' => self::VALID,
        ];
    }

    protected function createCounters()
    {
        foreach ($this->validHeaders as $field) {
            $counters[$field] = 0;
        }

        return $counters;
    }

    public function getValidHeaders()
    {
        return $this->validHeaders;
    }

    public function setValidHeaders($validHeaders)
    {
        $this->validHeaders = $validHeaders;

        return $this;
    }

    public function getRequiredFields()
    {
        return $this->requiredFields;
    }

    public function setRequiredFields($requiredFields)
    {
        $this->requiredFields = $requiredFields;

        return $this;
    }



}
