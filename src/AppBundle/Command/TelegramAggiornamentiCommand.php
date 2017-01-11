<?php

namespace AppBundle\Command;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TelegramAggiornamentiCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('telegram:aggiornamenti')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $telegram_api = sprintf(
            'https://api.telegram.org/bot%s/',
            $this->getContainer()->getParameter('telegram_api_key')
        );

        $messaggi = json_decode(file_get_contents($telegram_api.'getUpdates'));

        //$output->writeln($messaggi->result[2]->message->chat->id);

        //return;

        $client = new Client([
            'base_uri' => $telegram_api
        ]);

        $response = $client->request('POST', $telegram_api.'sendMessage', [
            'json' => [
                'chat_id' => $messaggi->result[2]->message->chat->id,
                'text' => 'Messaggio ricevuto'
            ]
        ]);

        var_dump($response);
    }
}
