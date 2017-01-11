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
 * Time: 19.22
 */

namespace AppBundle\Entity;


class Ruota
{
    private $nome;

    private $numeri;

    public function __construct($nome, $numeri)
    {
        $this->nome = $nome;
        $this->numeri = $numeri;
    }

    /**
     * @return array
     */
    public function getNumeri()
    {
        return $this->numeri;
    }

    public function getNome()
    {
        return $this->nome;
    }
}