<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnsetWebHookCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:unset')
            ->setDescription('Rimuove un webhook');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$url = $input->getArgument('url');

        $telegram_api = sprintf(
            'https://api.telegram.org/bot%s/',
            $this->getContainer()->getParameter('telegram_api_key')
        );

        $client = new Client([
            'base_uri' => $telegram_api
        ]);

        $response = $client->request('POST', $telegram_api.'getWebhookInfo', [
            'json' => [
                'url' => 'https://estrazioni.mmxforge.net/webhook/update/AociaIxsa8hjnii'
            ]
        ]);

        var_dump($response->getBody());
    }
}
