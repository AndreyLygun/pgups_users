<?php
namespace App\Http\Controllers;

use http\Exception;
use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $client;
    private $encoder;

    public function __construct()
    {
        $this->client = new Client('http://papercut-nuc:9191/rpc/api/xmlrpc');
        $this->encoder = new Encoder();
    }


    private function callAPI($method, ...$params) {
        array_unshift($params,  'MOHsG0-hvi');
        $apiParams = $this->encoder->encode($params);
        $request = new Request('api.'.$method, $apiParams);
//        dd($request->serialize());
        $response =  $this->client->send($request);
        if ($response->val === 0)
            throw new \Exception("Неправильный запрос. \n<br>Request = " . $request->serialize() . "\n<br>Response = " . $response->serialize());
        // TODO: сделать нормальную обработку возможной ошибки
//        dd($response->serialize());
        return $response;
    }

    public function getTotalUsers() {
        $response = $this->callAPI('getTotalUsers');
        return $this->encoder->decode($response->val);
    }

    public function getAllUsers($offset = 0, $limit = 1000) {
        $response = $this->callAPI('listUserAccounts', $offset, $limit);
        return $this->encoder->decode($response->val);
    }

    public function getUserInfo($userName) {
        try {
            $propertyNames = ["balance", "email", "full-name", "department"];
            $response = $this->callAPI('getUserProperties', $userName, $propertyNames);
            $values = $this->encoder->decode($response->val);
        } catch (\Exception $ex) {
            return $ex;
        }
        return array_combine($propertyNames, $values);
    }







}
