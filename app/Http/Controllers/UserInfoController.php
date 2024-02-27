<?php

namespace App\Http\Controllers;

use http\Exception;
use PhpXmlRpc\Client;
use PhpXmlRpc\Encoder;
use PhpXmlRpc\Request;
use Illuminate\Auth;

class UserInfoController extends Controller
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
        $papercut_url = env('PAPERCUT_URL');
        $papercut_port = env('PAPERCUT_PORT', "9191");
        $this->client = new Client("$papercut_url:$papercut_port/rpc/api/xmlrpc");
        $this->encoder = new Encoder();
    }


    private function callAPI($method, ...$params)
    {
        if (request()->header('api-token')!==env('API_TOKEN'))
            Abort(401, "Ошибка авторизации. Неправильный токен '" . request()->header('API_TOKEN') . "'");
        array_unshift($params, 'MOHsG0-hvi');
        $apiParams = $this->encoder->encode($params);
        $request = new Request('api.' . $method, $apiParams);
        //dd($request->serialize());
        $response = $this->client->send($request);
        if ($response->val === 0)
            Abort(501, "Неправильный запрос. \n<br>Request = " . $request->serialize() . "\n<br>Response = " . $response->serialize());
        // TODO: сделать нормальную обработку возможной ошибки
//        dd($response->serialize());
        return $response;
    }

    public function getTotalUsers()
    {
        $response = $this->callAPI('getTotalUsers');
        return $this->encoder->decode($response->val);
    }

    public function getAllUsers($offset = 0, $limit = 1000)
    {
        $response = $this->callAPI('listUserAccounts', $offset, $limit);
        return $this->encoder->decode($response->val);
    }

    public function getUserInfo($userName)
    {
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
