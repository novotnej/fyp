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
                    "duration" => $duration
                );
                $this->getHttpResponse()->setContentType("text/json");
                print(json_encode($response));
                break;
            default:
                $this->getHttpResponse()->setContentType("text/plain");
                print($client . "|" . $startTime . "|" . $end . "|" . $duration);
                //print(PHP_EOL);
                //print($content);
                break;
        }
        $this->terminate();
    }
}
