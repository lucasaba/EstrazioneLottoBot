<?php
/**
 * Questo file fa parte del progetto estrazioni.
 * Il codice è fornito senza alcuna garanzia e distribuito
 * con licenza di tipo open source.
 * Per le informazioni sui diritti e le informazioni sulla licenza
 * consultare il file LICENSE che deve essere distribuito
 * insieme a questo codice.
 *
 * (c) Luca Saba <lucasaba@gmail.com>
 *
 * Created by PhpStorm.
 * User: luca
 * Date: 10/01/17
 * Time: 19.26
 */

namespace AppBundle\Service;


use AppBundle\Entity\Estrazione;
use AppBundle\Entity\EstrazioneSuperenalotto;

class ScaricaEstrazioneSuperenalotto
{
    // Url per estrazione definita da numero e anno
    const BASE_ESTRAZIONE = 'https://www.agenziadoganemonopoli.gov.it/portale/monopoli/giochi/giochi_num_total/superenalotto/enalotto_estr?p_p_id=webdisplayaams_WAR_webdisplayaamsportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&_webdisplayaams_WAR_webdisplayaamsportlet_sigla=E%s%s&_webdisplayaams_WAR_webdisplayaamsportlet_tipo=su';
    // Url per ultime estrazioni
    const BASE_ULTIME_ESTRAZIONI = 'https://www.agenziadoganemonopoli.gov.it/portale/monopoli/giochi/giochi_num_total/superenalotto/enalotto_estr?p_p_id=webdisplayaams_WAR_webdisplayaamsportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=colonna1&p_p_col_pos=2&p_p_col_count=3&_webdisplayaams_WAR_webdisplayaamsportlet_sub=Cerca&_webdisplayaams_WAR_webdisplayaamsportlet_anno=%s&_webdisplayaams_WAR_webdisplayaamsportlet_mese=%s';

    private $cahce;

    public function __construct($cache_risultati)
    {
        $this->cahce = $cache_risultati;
    }

    /**
     * @param $numero
     * @param $anno
     * @return EstrazioneSuperenalotto|boolean
     */
    public function numeroAnno($numero, $anno)
    {
        if(file_exists($this->getCachedFileName($numero, $anno))) {
            $estrazione = EstrazioneSuperenalotto::createFromCache($this->getCachedFileName($numero, $anno));
            return $estrazione;
        }

        $url = sprintf(self::BASE_ESTRAZIONE, str_pad($anno,'0', STR_PAD_LEFT), str_pad($numero, '0', STR_PAD_LEFT));

        $contenuto = file_get_contents($url);

        if(strpos($contenuto, 'Non ci sono estrazioni') !== false) {
            return false;
        }

        $xml = new \DOMDocument();
        $xml->loadHTML($contenuto);

        $dati_estrazione = json_decode($xml->getElementById('cmsTiTrovi')->nodeValue, true);
        $label = $dati_estrazione['breadcrumb'][0]['label'];
        preg_match('/(\d{2}\/\d{2}\/\d{4})/', $label, $matches);
        $numeri = array();
        $jolly = 0;
        $superstar = 0;

        $ids= $xml->getElementById('tab1');
        $spans = $ids->getElementsByTagName('span');

        foreach ($spans as $span) {
            if(count($numeri) == 6){
                $jolly = $span->nodeValue;
                break;
            }
            $numeri[] = $span->nodeValue;
        }
        asort($numeri);

        $tab2 = $xml->getElementById('tab2');
        foreach ($tab2->getElementsByTagName('p') as $paragrafo) {
            /* @var \DOMElement $paragrafo */
            if($paragrafo->getAttribute('class')=='IntBordo') {
                $span = $paragrafo->getElementsByTagName('span');
                $superstar = $span[0]->nodeValue;
            } else {
                continue;
            }
        }
        $data = \DateTime::createFromFormat('d/m/Y', $matches[0]);

        $estrazione = new EstrazioneSuperenalotto(
            (int) $numero, (int) $anno, $data,
            $numeri, $jolly, $superstar
        );

        $estrazione->saveToFile($this->getCachedFileName($numero, $anno));

        return $estrazione;
    }

    /**
     * @return EstrazioneSuperenalotto
     */
    public function infoUltimaEstrazione()
    {
        $xml = new \DOMDocument();
        $anno = date('Y');
        $mese = date('m');

        do {
            $url = sprintf(self::BASE_ULTIME_ESTRAZIONI, $anno, $mese);
            $xml->loadHTML(file_get_contents($url));
            $estrazioni = $xml->getElementsByTagName('a');
            $mese -= 1;
            if($mese == 0) {
                $mese = 12;
                $anno -= 1;
            }
        } while($estrazioni->length == 0);

        $anno = 0;
        $numero = 0;

        foreach ($estrazioni as $estrazione) {
            /* @var \DOMElement $estrazione */
            $href = $estrazione->getAttribute('href');
            // $href avrà la forma: '?sigla=E0000201700015&tipo=su'
            $matches = array();
            preg_match('/\?sigla=E(\d{8})(\d{5})/', $href, $matches);

            if((count($matches) > 2) && $matches[1] > $anno) {
                $anno = $matches[1];
                $numero = 0;
            }
            if($matches[2] > $numero) {
                $numero = $matches[2];
            }
        }
        return $this->numeroAnno($numero, $anno);
    }

    private function getCachedFileName($numero, $anno)
    {
        return $this->cahce.DIRECTORY_SEPARATOR.$anno.'_'.$numero.'_estrazione_superenalotto.json';
    }
}