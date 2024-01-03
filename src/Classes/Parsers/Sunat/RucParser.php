<?php
namespace MrJmpl3\LaravelPeruConsult\Classes\Parsers\Sunat;

use DateTime;
use Generator;
use MrJmpl3\LaravelPeruConsult\Classes\Responses\Company;

class RucParser
{
    /**
     * Override Departments.
     *
     * @var array<string, string>
     */
    private array $overrideDepartments = [
        'DIOS' => 'MADRE DE DIOS',
        'MARTIN' => 'SAN MARTIN',
        'LIBERTAD' => 'LA LIBERTAD',
        'CALLAO' => 'PROV. CONST. DEL CALLAO',
    ];

    public function parse(array|bool $dic): ?Company
    {
        if ($dic === false) {
            return null;
        }

        return $this->getCompany($dic);
    }

    private function getCompany(array $items): Company
    {
        $company = $this->getHeadCompany($items);
        $company->sistEmsion = $items['Sistema Emisión de Comprobante:'] ?? $items['Sistema de Emisión de Comprobante:'] ?? '';
        $company->sistContabilidad = $items['Sistema Contabilidiad:'] ?? $items['Sistema Contabilidad:'] ?? $items['Sistema de Contabilidad:'] ?? '';
        $company->actExterior = $items['Actividad Comercio Exterior:'] ?? $items['Actividad de Comercio Exterior:'] ?? '';
        $company->actEconomicas = $items['Actividad(es) Económica(s):'] ?? [];
        $company->cpPago = $items['Comprobantes de Pago c/aut. de impresión (F. 806 u 816):'] ?? [];
        $company->sistElectronica = $items['Sistema de Emisión Electrónica:'] ?? $items['Sistema de Emision Electronica:'] ?? [];
        $company->fechaEmisorFe = $this->parseDate($items['Emisor electrónico desde:'] ?? '');
        $company->cpeElectronico = $this->getCpes($items['Comprobantes Electrónicos:'] ?? '');
        $company->fechaPle = $this->parseDate($items['Afiliado al PLE desde:'] ?? '');
        $company->padrones = $items['Padrones:'] ?? [];

        $this->fixDirection($company);

        return $company;
    }

    private function getHeadCompany(array $items): Company
    {
        $company = new Company();

        [$company->ruc, $company->razonSocial] = $this->getRucRzSocial($items['Número de RUC:'] ?? $items['RUC:']);
        $company->nombreComercial = $items['Nombre Comercial:'] ?? '';
        $company->telefonos = [];
        $company->tipo = $items['Tipo Contribuyente:'] ?? '';
        $company->estado = $items['Estado del Contribuyente:'] ?? $items['Estado:'];
        $company->condicion = $this->getFirstLine($items['Condición del Contribuyente:'] ?? $items['Condición:']);
        $company->direccion = $items['Domicilio Fiscal:'] ?? $items['Dirección del Domicilio Fiscal:'];
        $company->fechaInscripcion = $this->parseDate($items['Fecha de Inscripción:'] ?? '');
        $company->fechaBaja = $this->parseDate($items['Fecha de Baja:'] ?? '');
        $company->profesion = $items['Profesión u Oficio:'] ?? '';

        $this->fixEstado($company);

        return $company;
    }

    private function parseDate(string $text): ?string
    {
        if (empty($text) || $text === '-') {
            return null;
        }

        $date = DateTime::createFromFormat('d/m/Y', $text);

        return $date === false ? null : $date->format('Y-m-d').'T00:00:00.000Z';
    }

    private function getFirstLine(string $text): string
    {
        $lines = explode("\r\n", $text);

        return trim($lines[0]);
    }

    private function fixEstado(Company $company): void
    {
        $lines = explode("\r\n", $company->estado);
        $count = \count($lines);
        if ($count === 1) {
            return;
        }

        $validLines = iterator_to_array($this->filterValidLines($lines));
        $updateFechaBaja = \count($validLines) === 3 && $company->fechaBaja === null;

        $company->estado = $validLines[0];
        $company->fechaBaja = $updateFechaBaja ? $this->parseDate($validLines[2]) : $company->fechaBaja;
    }

    private function filterValidLines(array $lines): Generator
    {
        foreach ($lines as $line) {
            $value = trim($line);

            if ($value === '') {
                continue;
            }

            yield $value;
        }
    }

    private function fixDirection(Company $company): void
    {
        $items = explode('                                               -', $company->direccion);

        if (\count($items) !== 3) {
            $company->direccion = preg_replace('[\\s+]', ' ', $company->direccion);

            return;
        }

        $pieces = explode(' ', trim($items[0]));
        $department = $this->getDepartment(end($pieces));

        $company->departamento = $department;
        $company->provincia = trim($items[1]);
        $company->distrito = trim($items[2]);

        $removeLength = \count(explode(' ', $department));
        array_splice($pieces, -1 * $removeLength);

        $company->direccion = rtrim(implode(' ', $pieces));
    }

    private function getDepartment(?string $department): string
    {
        $department = mb_strtoupper($department);

        if (isset($this->overrideDepartments[$department])) {
            $department = $this->overrideDepartments[$department];
        }

        return $department;
    }

    /**
     * @return string[]
     */
    private function getCpes(?string $text): array
    {
        $cpes = [];

        if ( ! empty($text) && $text !== '-') {
            $cpes = explode(',', $text);
        }

        return $cpes;
    }

    /**
     * @return string[]
     */
    private function getRucRzSocial(?string $text): array
    {
        $pos = mb_strpos($text, '-');

        $ruc = trim(mb_substr($text, 0, $pos));
        $rzSocial = trim(mb_substr($text, $pos + 1));

        return [$ruc, $rzSocial];
    }
}
