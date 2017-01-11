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

class ScaricaEstrazione
{
    // Url per estrazione definita da numero e anno
    const BASE_ESTRAZIONE = 'https://www.agenziadoganemonopoli.gov.it/portale/monopoli/giochi/gioco-del-lotto/lotto_g/lotto_estr?p_p_id=webdisplayaams_WAR_webdisplayaamsportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=colonna1&p_p_col_pos=2&p_p_col_count=3&_webdisplayaams_WAR_webdisplayaamsportlet_prog=%s&_webdisplayaams_WAR_webdisplayaamsportlet_anno=%s';
    // Url per ultime estrazioni
    const BASE_ESTRAZIONI = 'https://www.agenziadoganemonopoli.gov.it/portale/monopoli/giochi/gioco-del-lotto/lotto_g/lotto_estr?p_p_id=webdisplayaams_WAR_webdisplayaamsportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=colonna1&p_p_col_pos=2&p_p_col_count=3&_webdisplayaams_WAR_webdisplayaamsportlet_sub=Cerca&_webdisplayaams_WAR_webdisplayaamsportlet_anno=%s&_webdisplayaams_WAR_webdisplayaamsportlet_mese=%s';

    private $cahce;

    public function __construct($cache_risultati)
    {
        $this->cahce = $cache_risultati;
    }



    /**
     * @param $numero
     * @param $anno
     * @return Estrazione|boolean
     */
    public function numeroAnno($numero, $anno)
    {
        if(file_exists($this->getCachedFileName($numero, $anno))) {
            $estrazione = Estrazione::createFromCahche($this->getCachedFileName($numero, $anno));
            return $estrazione;
        }

        $url = sprintf(self::BASE_ESTRAZIONE, $numero, $anno);

        $contenuto = file_get_contents($url);

        if(strpos('Non ci sono estrazioni', $contenuto) === false) {
            return false;
        }

        $xml = new \DOMDocument();
        $xml->loadHTML($contenuto);

        $estrazione = new Estrazione($numero, $anno, new \DateTime());

        foreach ($xml->getElementsByTagName('tr') as $riga) {
            /* @var \DOMElement $riga */
            $nome = $riga->getElementsByTagName('th')[0]->nodeValue;
            $numeri = array();
            foreach ($riga->getElementsByTagName('td') as $cella) {
                /* @var \DOMElement $cella */
                $numeri[] = $cella->nodeValue;
            }
            asort($numeri);
            $estrazione->addRuota($nome, $numeri);
        }

        $estrazione->saveToFile($this->getCachedFileName($numero, $anno));

        return $estrazione;
    }

    /**
     * @return Estrazione
     */
    public function infoUltimaEstrazione()
    {
        $xml = new \DOMDocument();
        $anno = date('Y');
        $mese = date('m');

        do {
            $url = sprintf(self::BASE_ESTRAZIONI, $anno, $mese);
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
            // $href avrà la forma: '?prog=3&anno=2017'
            $matches = array();
            preg_match('/\?prog=([0-9]{1,2})&anno=([0-9]{4})/', $href, $matches);
            if($matches[2] > $anno) {
                $anno = $matches[2];
                $numero = 0;
            }
            if($matches[1] > $numero) {
                $numero = $matches[1];
            }
        }

        return $this->numeroAnno($numero, $anno);
    }

    private function getCachedFileName($numero, $anno)
    {
        return $this->cahce.DIRECTORY_SEPARATOR.$anno.'_'.$numero.'_estrazione.json';
    }
}