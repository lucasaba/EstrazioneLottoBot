<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $estrazione = $this->get('scarica_estrazione')->numeroAnno(3, 2017);

        return $this->render('default/index.html.twig', [
            'estrazione' => $estrazione,
        ]);
    }

    /**
     * @Route("/estrazione/{numero}/{anno}", name="estrazione_numero_anno")
     * @param $numero
     * @param $anno
     * @return JsonResponse
     */
    public function estrazioneNumeroAnnoAction($numero, $anno)
    {
        $estrazione = $this->get('scarica_estrazione')->numeroAnno($numero, $anno);

        $response = new JsonResponse();
        if($estrazione === false) {
            $response->setData(['msg' => 'Nessuna estrazione trovata']);
        } else {
            $response->setData($estrazione->toArray());
        }
        return $response;
    }

    /**
     * @Route("/ultima-estrazione", name="ultima_estrazione")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function ultimaEstrazioneAction()
    {
        $estrazione = $this->get('scarica_estrazione')->infoUltimaEstrazione();

        $response = new JsonResponse();
        $response->setData($estrazione->toArray());
        return $response;
    }
}
