<?php


namespace CoinMonitor;


class CoinMonitor
{
    protected $coin;
    protected $exchange;
    protected $buyPrice;
    protected $sellTargets;
    protected $stopLoss;
    protected $targetsMet = false;
    protected $hitStopLoss = false;

    public function __construct($coin, $exchange, $buyPrice, array $sellTargets, $stopLoss = null)
    {
        $this->coin = $coin;
        $this->exchange = $exchange;
        $this->buyPrice = $buyPrice;
        $this->sellTargets = $sellTargets;
        $this->stopLoss = $stopLoss;

        $time = date('Y-m-d H:i:s');

        $coinLog = dirname(__FILE__) . '/../logs/' . strtolower($this->coin) . '-' . $time . '.csv';

        $fh = fopen($coinLog, "a");

        $headers = [
            [
                'Time: ' . $time,
                'Coin: ' . $this->coin,
                'Exchange: ' . $this->exchange,
                'Buy Price: ' . $this->buyPrice,
                'Target 1: ' . $this->sellTargets[0],
                'Target 2: ' . $this->sellTargets[1],
                'Stop Loss: ' . $this->stopLoss,
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
            if ($this->sellTargets[0] <= $exchangePrice) {
                $status = 'Target 1 Hit';
            }
            if ($this->sellTargets[1] <= $exchangePrice) {
                $status = 'Target 2 Hit';
                $this->targetsMet = true;
            }
            if ($this->stopLoss >= $exchangePrice) {
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