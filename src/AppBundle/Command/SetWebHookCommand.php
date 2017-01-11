<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetWebHookCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('telegram:set')
            ->setDescription('Crea un webhook telegram')
            ->addArgument('url', InputArgument::REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');

        $telegram_api = sprintf(
            'https://api.telegram.org/bot%s/',
            $this->getContainer()->getParameter('telegram_api_key')
        );

        $client = new Client([
            'base_uri' => $telegram_api
        ]);

        $response = $client->request('POST', $telegram_api.'setWebhook', [
            'json' => [
                'url' => $url
            ]
        ]);

        var_dump($response->getBody());
    }
}
