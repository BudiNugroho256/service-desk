<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;
use EmailReplyParser\EmailReplyParser;
use HTMLPurifier;
use HTMLPurifier_Config;
use App\Models\Ticket;
use App\Models\TicketTracking;
use App\Models\User;
use App\Models\TicketNotification;
use App\Mail\TicketCreatedNotification;
use App\Services\EmailProcessorService;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
