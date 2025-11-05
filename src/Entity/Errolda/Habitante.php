<?php

namespace App\Entity\Errolda;

use App\Repository\Errolda\HabitanteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'PA_HABITANTE')]
#[ORM\Entity(repositoryClass: HabitanteRepository::class, readOnly: true)]
class Habitante 
{
    #[ORM\Column(name: 'CLA_PERSO', type: 'bigint')]
    #[ORM\Id]
    protected $id;

    #[ORM\Column(name: "NOMBRE", type: 'string', length: 30)]
    protected $nombre;

    #[ORM\Column(name: "PARTICUL1", type: 'string', length: 6)]
    protected $particul1;

    #[ORM\Column(name: "APELLIDO1", type: 'string', length: 30)]
    protected $apellido1;

    #[ORM\Column(name: "PARTICUL2", type: 'string', length: 6)]
    protected $particul2;

    #[ORM\Column(name: "APELLIDO2", type: 'string', length: 30)]
    protected $apellido2;

    #[ORM\Column(name: "DNI", type: 'string', length: 20)]
    protected $dni;

    #[ORM\Column(name: "REG_ACTIV", type: 'string', length: 3)]
    protected $activo;

    #[ORM\Column(name: "COD_VARIA", type: 'string', length: 2)]
    protected $codigoVariacion;

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getParticul1()
    {
        return $this->particul1;
    }

    public function setParticul1($particul1)
    {
        $this->particul1 = $particul1;

        return $this;
    }

    public function getApellido1()
    {
        return $this->apellido1;
    }

    public function setApellido1($apellido1)
    {
        $this->apellido1 = $apellido1;

        return $this;
    }

    public function getParticul2()
    {
        return $this->particul2;
    }

    public function setParticul2($particul2)
    {
        $this->particul2 = $particul2;

        return $this;
    }

    public function getApellido2()
    {
        return $this->apellido2;
    }

    public function setApellido2($apellido2)
    {
        $this->apellido2 = $apellido2;

        return $this;
    }

    public function getDni()
    {
        return $this->dni;
    }

    public function setDni($dni)
    {
        $this->dni = $dni;

        return $this;
    }

    public function getActivo()
    {
        return $this->activo;
    }

    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    public function getCodigoVariacion()
    {
        return $this->codigoVariacion;
    }

    public function setCodigoVariacion($codigoVariacion)
    {
        $this->codigoVariacion = $codigoVariacion;

        return $this;
    }

    public function getFullname()
    {
        $fullname = $this->nombre;
        if (!empty($this->particul1)) {
            $fullname .= ' ' . $this->particul1;
        }
        $fullname .= ' ' . $this->apellido1;
        if (!empty($this->particul2)) {
            $fullname .= ' ' . $this->particul2;
        }
        $fullname .= ' ' . $this->apellido2;
        return $fullname;
    }
}