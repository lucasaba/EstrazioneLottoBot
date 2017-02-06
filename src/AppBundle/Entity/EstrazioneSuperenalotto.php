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
 * Date: 06/02/17
 * Time: 20.37
 */

namespace AppBundle\Entity;


class EstrazioneSuperenalotto
{
    protected $numero;
    protected $anno;
    /**
     * @var \DateTime
     */
    protected $data;
    protected $numeri;
    protected $jolly;
    protected $superstar;

    public function __construct(
        $numero,
        $anno,
        $data,
        $numeri,
        $jolly,
        $superstar)
    {
        $this->numero = $numero;
        $this->anno = $anno;
        $this->data= $data;
        $this->numeri=$numeri;
        $this->jolly = $jolly;
        $this->superstar = $superstar;
    }

    /**
     * @param $file_cache
     * @return EstrazioneSuperenalotto
     */
    public static function createFromCache($file_cache)
    {
        $data = json_decode(file_get_contents($file_cache), true);
        $estrazione = new EstrazioneSuperenalotto(
            $data['numero'],
            $data['anno'],
            \DateTime::createFromFormat('d/m/Y' , $data['data']),
            $data['numeri'],
            $data['jolly'],
            $data['superstar']
            );

        return $estrazione;
    }

    public function toArray()
    {
        $estrazione = array(
            'numero' => $this->numero,
            'anno' => $this->anno,
            'data' => $this->data->format('d/m/Y'),
            'numeri' => $this->numeri,
            'jolly' => $this->jolly,
            'superstar' => $this->superstar
        );

        return $estrazione;
    }

    public function saveToFile($nome_file)
    {
        $json_array = $this->toArray();

        file_put_contents($nome_file, json_encode($json_array));
    }
}