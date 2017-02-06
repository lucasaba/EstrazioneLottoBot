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
 * Time: 19.21
 */

namespace AppBundle\Entity;


class Estrazione implements \Iterator
{
    private $data;

    private $numero;

    private $anno;

    private $ruote;

    private $puntatore;

    private $numero_ruote;

    private $nomi_ruote;

    public function __construct($numero, $anno, $data)
    {
        $this->numero = $numero;
        $this->anno = $anno;
        if($data instanceof \DateTime) {
            $this->data = $data;
        } else {
            $this->data = \DateTime::createFromFormat('d/m/Y', $data);
        }

        $this->ruote = array();
        $this->puntatore = 0;
        $this->numero_ruote = 0;
    }

    /**
     * @param $file_cache
     * @return Estrazione
     */
    public static function createFromCache($file_cache)
    {
        $data = json_decode(file_get_contents($file_cache), true);
        $estrazione = new Estrazione($data['numero'], $data['anno'], $data['data']);
        foreach ($data['ruote'] as $ruota => $numeri) {
            $estrazione->addRuota($ruota, $numeri);
        }

        return $estrazione;
    }

    public function addRuota($nome, $numeri)
    {
        $this->ruote[$nome] = new Ruota($nome, $numeri);
        $this->nomi_ruote[$this->numero_ruote] = $nome;
        $this->numero_ruote++;
    }

    public function getRuota($nome)
    {
        return $this->ruote[$nome];
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return $this->ruote[$this->nomi_ruote[$this->puntatore]];
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $this->puntatore++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return $this->nomi_ruote[$this->puntatore];
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        if($this->puntatore > $this->numero_ruote - 1) {
            return false;
        }
        return true;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->puntatore = 0;
    }

    public function toArray()
    {
        $estrazione = array(
            'numero' => $this->numero,
            'anno' => $this->anno,
            'data' => $this->data->format('d/m/Y'),
            'ruote' => array()
        );

        foreach ($this->ruote as $ruota) {
            $estrazione['ruote'][$ruota->getNome()] = $ruota->getNumeri();
        }

        return $estrazione;
    }

    public function saveToFile($nome_file)
    {
        $json_array = $this->toArray();

        file_put_contents($nome_file, json_encode($json_array));
    }
}