<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use App\Services\EmailProcessorService;

class FetchEmails extends Command
{
    protected $signature = 'emails:fetch';
    protected $description = 'Fetch new emails from Gmail and store them as tickets';

    public function handle()
    {
        $client = Client::account('gmail'); 
        $client->connect();

        $inbox = $client->getFolder('INBOX');
        $messages = $inbox->query()->unseen()->limit(10)->get();

        $processor = new EmailProcessorService();

        foreach ($messages as $message) {
            $result = $processor->process($message);
            $this->info($result);
            $message->setFlag('Seen');
        }

        $client->disconnect();
    }

}
