<?php

use AliyunMNS\Client;
use AliyunMNS\Exception\MessageNotExistException;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Exception\MnsException;

class MNS
{
    public function publish($topicName,$data = array(),$logger)
    {
        $client = new Client(getenv('MNS_ENDPOINT'), getenv('ACCESS_KEY_ID'),getenv('ACCESS_KEY_SECRET'));

        $topic = $client->getTopicRef($topicName);

        $request = new PublishMessageRequest(json_encode($data));
        try {
            $res = $topic->publishMessage($request);
            $logger->info("MessagePublished!");
        } catch (MnsException $e) {
            $logger->info("PublishMessage Failed: " . $e);
            return;
        }
    }
}