<?php


namespace CoinMonitor;


class HitBtc
{
    protected $client;

    public function __construct()
    {
        $this->client = new \Hitbtc\ProtectedClient(getenv('HITBTC_API_KEY'), getenv('HITBTC_API_SECRET'));
    }

    public function checkForC20()
    {
        $c20Exists = false;
        $symbols = json_decode(file_get_contents('https://api.hitbtc.com/api/2/public/symbol'));
        foreach ($symbols as $symbol) {
            if ($symbol->baseCurrency != 'C20') {
                continue;
            }
            if ($symbol->quoteCurrency === 'BTC') {
                $c20Exists = true;
            }
        }

        return $c20Exists;
    }

    public function buyC20($btcAmount)
    {
        $newOrder = new \Hitbtc\Model\NewOrder();
        $newOrder->setSide($newOrder::SIDE_SELL);
        $newOrder->setSymbol('C20BTC');
        $newOrder->setTimeInForce($newOrder::TIME_IN_FORCE_GTC);
        $newOrder->setType($newOrder::TYPE_MARKET);
        $newOrder->setQuantity($btcAmount);

        try {
            $order = $this->client->newOrder($newOrder);
            var_dump($order->getOrderId());
            var_dump($order->getStatus()); // new
        } catch (\Hitbtc\Exception\RejectException $e) {
            echo $e; // if creating order will rejected
        } catch (\Hitbtc\Exception\InvalidRequestException $e) {
            echo $e->getMessage(); // error in request
        } catch (\Exception $e) {
            echo $e->getMessage(); // other error like network issue
        }
    }

    public function getBalance($symbol = null)
    {
        try {
            $balances = $this->client->getBalanceTrading();
            if ($symbol) {
                foreach ($balances as $balance) {
                    if ($symbol === $balance->getCurrency()) {
                        return $balance;
                    }
                }
            } else {
                return $balances;
            }
        } catch (\Hitbtc\Exception\InvalidRequestException $e) {
            echo $e;
        } catch (\Exception $e) {
            echo $e;
        }
    }
}