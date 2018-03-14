<?php
/**
 * Created by PhpStorm.
 * User: tpetin
 * Date: 06.07.2017
 * Time: 13:07
 */

namespace Reinvently\Twilio;

use Twilio\Exceptions\TwilioException;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\ClientToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Jwt\Grants\VideoGrant;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Rest\Client;

class Twilio
{
    public $retries;
    public $account;
    public $number;
    public $twiMLApp;
    public $apiKey;
    public $configurationProfile;

    /**
     * @param bool $name
     * @param null $ttl
     * @return bool|ClientToken
     */
    public function generateCapabilityToken($name = false, $ttl = null)
    {
        if ($name) {
            $cap = new ClientToken($this->account['sid'], $this->account['token']);
            $cap->allowClientOutgoing($this->twiMLApp['sid']);
            $cap->allowClientIncoming($name);
            $token = $cap->generateToken($ttl);
            return $token;
        }

        return false;
    }

    private function getToken($identity, $ttl) {
        return new AccessToken(
            $this->account['sid'],
            $this->apiKey['sid'],
            $this->apiKey['secret'],
            $ttl,
            $identity
        );
    }

    /**
     * Generate auth token for video
     *
     * @param null $identity
     * @param int $ttl
     * @return string
     */
    public function generateVideoAuthToken($identity = null, $ttl = 3600)
    {
        $token = $this->getToken($identity, $ttl);
        $grant = new VideoGrant();
        $token->addGrant($grant);
        return $token->toJWT();
    }

    /**
     * Generate auth token for chat
     *
     * @param null $identity
     * @param int $ttl
     * @return string
     */
    public function generateChatAuthToken($identity = null, $ttl = 3600)
    {
        $token = $this->getToken($identity, $ttl);
        $chatGrant = new ChatGrant();
        $chatGrant->setServiceSid($this->twiMLApp['sid']);
        $token->addGrant($chatGrant);
        return $token->toJWT();
    }

    /**
     * Generate auth token for voice
     *
     * @param null $identity
     * @param int $ttl
     * @return string
     */
    public function generateVoiceAuthToken($identity = null, $ttl = 3600)
    {
        $token = $this->getToken($identity, $ttl);
        $voiceGrant = new VoiceGrant();
        $voiceGrant->setOutgoingApplicationSid($this->twiMLApp['sid']);
        $token->addGrant($voiceGrant);
        return $token->toJWT();
    }

    /**
     * Send SMS
     *
     * @param $recipient string phone number
     * @param $message string
     * @return bool
     */
    public function sendSms($recipient, $message)
    {
        if (\Yii::$app->request->isConsoleRequest) {
            echo 'Sending SMS to ' . $recipient . "\n";
        }

        $client = new Client($this->account['sid'], $this->account['token']);

        try {
            $client->account->messages->create(
                $recipient,
                [
                    'from' => $this->number,
                    'body' => $message
                ]
            );

            \Yii::info([
                'recipient' => $recipient,
                'body' => $message,
            ], 'outgoingSms');

            return true;
        } catch (TwilioException $e) {
            if (\Yii::$app->request->isConsoleRequest) {
                echo $e->getMessage() . "\n";
            }
            // todo: log it
            // note that such exception throws pretty often
            return false;
        }
    }

    /**
     * Make a twilio call
     *
     * @param string $phoneNumber
     * @param array $params - say, uid
     * @return bool
     */
    public function call($phoneNumber, $params)
    {
        if (\Yii::$app->request->isConsoleRequest) {
            echo 'Calling to ' . $phoneNumber . "\n";
        }

        $retries = 0;

        while ($retries <= $this->retries) {
            $retries++;

            try {
                $client = new Client($this->account['sid'], $this->account['token']);

                $client->calls->create(
                    $phoneNumber,
                    $this->number,
                    [
                        'url' => \Yii::$app->params['siteUrl'] . '/api/v1/phone-call/voice?' . http_build_query($params),
                        'method' => 'GET',
                    ]
                );

                return true;
            } catch (TwilioException $e) {
                if (\Yii::$app->request->isConsoleRequest) {
                    echo $e->getMessage() . "\n";
                }

                \Yii::info($e->getMessage(), 'twilioCallException');
                sleep(1);
            }
        }

        return false;
    }

}