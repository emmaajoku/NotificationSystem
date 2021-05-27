<?php

namespace App\Traits;

trait WebhookNotifiable
{

    /**
     * @return string
     */
    public function getSigningKey(): string
    {
        return $this->api_key;
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->webhook_url;
    }

}
