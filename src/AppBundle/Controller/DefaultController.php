<?php

namespace AppBundle\Controller;

use GuzzleHttp\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ultimaEstrazioneAction()
    {
        $estrazione = $this->get('scarica_estrazione')->infoUltimaEstrazione();

        $response = new JsonResponse();

        $response->setData($estrazione->toArray());

        return $response;
    }

    /**
     * @Route("/ultima-estrazione-superenalotto", name="ultima_estrazione_superenalotto")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ultimaEstrazioneSuperenalottoAction()
    {
        $estrazione = $this->get('scarica_estrazione_superenalotto')->infoUltimaEstrazione();

        $response = new JsonResponse();

        $response->setData($estrazione->toArray());

        return $response;
    }

    /**
     * @Route("/webhook/update/{secret}", name="telegram_webhook_update")
     *
     * @param Request $request
     * @return Response
     */
    public function telegramWebhookAction($secret, Request $request)
    {
        if($secret != $this->getParameter('telegram_secret_uri')) {
            return new Response();
        }
        $data = json_decode($request->getContent(), true);
        $telegram_api = sprintf(
            'https://api.telegram.org/bot%s/',
            $this->getParameter('telegram_api_key')
        );

        $client = new Client([
            'base_uri' => $telegram_api
        ]);

        //$this->get('logger')->addCritical($data['message']['text']);


        if(! isset($data['message']['entities']) || ! $data['message']['entities'][0]['type'] == 'bot_command') {
            $this->messaggioNessunComandoInviato($client, $telegram_api, $data);
            return new Response();
        }

        switch ($data['message']['text']) {
            case '/superenalotto':
                $estrazione = $this->get('scarica_estrazione_superenalotto')->infoUltimaEstrazione();

                $messaggio = $this->get('twig')->render('messaggi/estrazione_superenalotto.txt.twig', array('estrazione' => $estrazione->toArray()));

                $client->request('POST', $telegram_api.'sendMessage', [
                    'json' => [
                        'chat_id' => $data['message']['chat']['id'],
                        'text' => $messaggio,
                        'parse_mode' => 'Markdown'
                    ]
                ]);
                break;
            case '/lotto':
                $estrazione = $this->get('scarica_estrazione')->infoUltimaEstrazione();

                $messaggio = $this->get('twig')->render('messaggi/estrazione.txt.twig', array('estrazione' => $estrazione->toArray()));

                $client->request('POST', $telegram_api.'sendMessage', [
                    'json' => [
                        'chat_id' => $data['message']['chat']['id'],
                        'text' => $messaggio,
                        'parse_mode' => 'Markdown'
                    ]
                ]);
                break;
        }

        return new Response();
    }

    private function messaggioNessunComandoInviato(Client $client, $telegram_api, $data)
    {
        $client->request('POST', $telegram_api.'sendMessage', [
            'json' => [
                'chat_id' => $data['message']['chat']['id'],
                'text' => 'Ciao '.$data['message']['from']['first_name'].', sto aspettando un comando. Prova con /ultimaestrazione',
                'parse_mode' => 'Markdown'
            ]
        ]);
    }
}
