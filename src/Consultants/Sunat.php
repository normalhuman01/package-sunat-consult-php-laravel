<?php
namespace MrJmpl3\LaravelPeruConsult\Consultants;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use MrJmpl3\LaravelPeruConsult\Classes\Endpoints\SunatEndpoints;
use MrJmpl3\LaravelPeruConsult\Classes\Parsers\Sunat\HtmlRecaptchaParser;
use MrJmpl3\LaravelPeruConsult\Classes\Parsers\Sunat\RucParser;
use MrJmpl3\LaravelPeruConsult\Classes\Responses\Company;

class Sunat
{
    private static string $PATTERN = '/<input type="hidden" name="numRnd" value="(.*)">/';

    /**
     * @throws RequestException
     */
    public function get($ruc): ?Company
    {
        $jar = new CookieJar();

        Http::retry(3, 1000)->withOptions([
            'cookies' => $jar,
            'timeout' => 60,
        ])
            ->get(SunatEndpoints::CONSULT)
            ->throw();

        $htmlRandom = Http::retry(3, 1000)->withOptions([
            'cookies' => $jar,
            'timeout' => 60,
        ])
            ->asForm()
            ->post(SunatEndpoints::CONSULT, [
                'accion' => 'consPorRazonSoc',
                'razSoc' => 'BVA FOODS',
            ])
            ->throw()
            ->body();

        $random = $this->getRandom($htmlRandom);

        $html = Http::retry(3, 1000)->withOptions([
            'cookies' => $jar,
            'timeout' => 60,
        ])
            ->asForm()
            ->post(SunatEndpoints::CONSULT, [
                'accion' => 'consPorRuc',
                'nroRuc' => $ruc,
                'numRnd' => $random,
                'actReturn' => '1',
                'modo' => '1',
            ])
            ->throw()
            ->body();

        $htmlParser = new HtmlRecaptchaParser();
        $dic = $htmlParser->parse($html);

        return (new RucParser())->parse($dic);
    }

    private function getRandom(string $html): string
    {
        preg_match_all(self::$PATTERN, $html, $matches, PREG_SET_ORDER);

        return \count($matches) > 0 ? $matches[0][1] : '';
    }
}
