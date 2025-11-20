<?php

namespace App\Message;

class ProcessThirdCheckFile
{
   public function __construct(private int $thirdCheckFileId)
   {    
   }

   public function getThirdCheckFileId(): int
   {
      return $this->thirdCheckFileId;
   }
}