<?php
namespace MrJmpl3\LaravelPeruConsult\Classes\Responses;

use JsonSerializable;

class Company implements JsonSerializable
{
    public string $ruc;

    public string $razonSocial;

    public string $nombreComercial;

    public array $telefonos;

    public string $tipo;

    public string $estado;

    public string $condicion;

    public string $direccion;

    public string $departamento;

    public string $provincia;

    public string $distrito;

    public ?string $fechaInscripcion;

    public string $sistEmsion;

    public string $sistContabilidad;

    public string $actExterior;

    public array $actEconomicas;

    public array $cpPago;

    public array $sistElectronica;

    public ?string $fechaEmisorFe;

    public array $cpeElectronico;

    public ?string $fechaPle;

    public array $padrones;

    public ?string $fechaBaja;

    public string $profesion;

    /**
     * Specify data which should be serialized to JSON.
     *
     * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource
     *
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
