<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailProcessorService;
use Webklex\IMAP\Facades\Client;

class EmailIdleWorker extends Command
{
    protected $signature = 'emails:idle';
    protected $description = 'Listen to new incoming emails in real-time using IMAP IDLE';

    public function handle()
    {
        $client = Client::account('gmail');
        $client->connect();
        $inbox = $client->getFolder('INBOX');

        $this->info("IMAP IDLE Worker started");

        $processor = new EmailProcessorService();

        $inbox->idle(function ($message) use ($processor) {
            $result = $processor->process($message);
            $this->info($result);
            $message->setFlag('Seen');
        });
    }

}
