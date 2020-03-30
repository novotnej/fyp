<?php

namespace App\ApiModule\Presenters;


use Nette\Utils\Random;
use Tracy\Debugger;

class HomepagePresenter extends BasePresenter {

    public function actionDefault($client, $length, $format) {
        $startTime = hrtime(true);
        $content = Random::generate($length);
        $end = hrtime(true);
        $duration = ($end - $startTime);
        switch($format) {
            case 'json':
                $response = array(
                    "client" => $client,
                    "start" => $startTime,
                    "content" => $content,
                    "end" => $end,
                    "duration" => $duration,
                    "timestamp" => time()
                );
                $this->getHttpResponse()->setContentType("application/json");
                $this->getHttpResponse()->setExpiration(10);
                print(json_encode($response));
                break;
            default:
                $this->getHttpResponse()->setContentType("text/plain");
                $this->getHttpResponse()->setExpiration(10);
                print($client . "|" . $startTime . "|" . $end . "|" . $duration . "|" . time());
                //print(PHP_EOL);
                //print($content);
                break;
        }
        $this->terminate();
    }
}
