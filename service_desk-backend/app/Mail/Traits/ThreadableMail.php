<?php

namespace App\Mail\Traits;

trait ThreadableMail
{
    protected function applyThreadHeaders($message, string $ticketId, bool $isParent = false, ?string $suffix = null)
    {
        $headers = $message->getHeaders();
        $unique = $suffix ? "-{$suffix}" : '';
        $messageId = "ticket-{$ticketId}{$unique}@gmail.com";

        $headers->remove('Message-ID');
        $headers->addIdHeader('Message-ID', $messageId);

        if (!$isParent) {
            $parentId = "ticket-{$ticketId}@gmail.com";
            $headers->addIdHeader('In-Reply-To', $parentId);
            $headers->addIdHeader('References', $parentId);
        }
    }
}