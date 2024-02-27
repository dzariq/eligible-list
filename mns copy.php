<?php

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class MNS
{
    public function publish($topic, $data = array(),$logger)
    {
        $logger->info('START MNS');
        // Set up Alibaba Cloud credentials
        AlibabaCloud::accessKeyClient(getenv('ACCESS_KEY_ID'), getenv('ACCESS_KEY_SECRET'))
            ->regionId('ap-southeast-3')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::mns()
                ->v20150901()
                ->sendMessageToQueue([
                    'QueueName' => $topic,
                    'MessageBody' => json_encode($data),
                ]);
            print_r($result->toArray());
            $logger->info(json_encode($result->toArray()));
        } catch (ClientException $e) {
            $logger->info($e->getErrorMessage());
        } catch (ServerException $e) {
            $logger->info($e->getErrorMessage());
        }
    }
}