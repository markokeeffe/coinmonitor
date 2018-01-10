<?php


namespace CoinMonitor;


class CoinMonitor
{
    protected $targetsMet = false;
    protected $hitStopLoss = false;

    public function monitorCoinMovement($coin, $exchange, $buyPrice, array $sellTargets, $stopLoss = null)
    {

        $time = date('Y-m-d H:i:s');

        $coinLog = dirname(__FILE__) . '/../logs/' . strtolower($coin) . '-' . $time . '.csv';

        $fh = fopen($coinLog, "a");

        $headers = [
            [
                'Time: ' . $time,
                'Coin: ' . $coin,
                'Exchange: ' . $exchange,
                'Buy Price: ' . $buyPrice,
                'Target 1: ' . $sellTargets[0],
                'Target 2: ' . $sellTargets[1],
                'Stop Loss: ' . $stopLoss,
            ],
            [
                'Time',
                'Price',
                'Status',
            ],
        ];
        foreach ($headers as $header) {
            fputcsv($fh, $header);
        }

        while (!$this->targetsMet && !$this->hitStopLoss) {
            switch ($exchange) {
                case 'bittrex' :
                    $response = file_get_contents('https://bittrex.com/api/v1.1/public/getticker?market=BTC-' . $coin);
                    if (!$response) {
                        throw new \Exception('Unable to get response from Bittrex ticker.');
                    }
                    $ticker = json_decode($response, true);
                    if (!$ticker['success']) {
                        throw new \Exception('Error getting Bittrex ticker: ' . $ticker['message']);
                    }

                    $exchangePrice = $ticker['result']['Last'];

                    break;
                default :
                    throw new \Exception('Invalid exchange type: ' . $exchange);
            }

            $status = 'Holding';
            if ($sellTargets[0] <= $exchangePrice) {
                $status = 'Target 1 Hit';
            }
            if ($sellTargets[1] <= $exchangePrice) {
                $status = 'Target 2 Hit';
                $this->targetsMet = true;
            }
            if ($stopLoss >= $exchangePrice) {
                $status = 'Stop Loss Hit';
                $this->hitStopLoss = true;
            }

            $data = [
                date('Y-m-d H:i:s'),
                number_format($exchangePrice, 8),
                $status,
            ];

            fputcsv($fh, $data);

            sleep(10);
        }

        fclose($fh);
    }

}