<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class GymZKFrontController extends GymGenericFrontController
{

    private $token;
    private $bio_base_url;
    public function __construct()
    {
        parent::__construct();
        $this->bio_base_url = 'http://127.0.0.1:8088';

        if (app()->runningInConsole()) {
            $this->token = null;
            return;
        }

        $this->token = $this->login();
    }

    public function login(){
        $endpoint = $this->bio_base_url . "/jwt-api-token-auth/";
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        $data = [
            "username"=>"admin",
            "password"=>"admin"
        ];

        $client = new \GuzzleHttp\Client(['headers' => $headers]);
        $response = $client->post($endpoint, [
            \GuzzleHttp\RequestOptions::JSON => $data,
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        $result = json_decode($content, true);

        if ($statusCode == 200) {
            $this->token = $result['token'];
            return $this->token;
        }
        return '';

    }

    public function member($emp_code = null)
    {
        $endpoint = $this->bio_base_url . "/personnel/api/employees/?emp_code=".$emp_code;
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $endpoint, ['headers' => ['Authorization' => "JWT " . @$this->token]]);
        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        $content = json_decode($content);
//        $this->employeeAdd(['code' => "0000000028"]);
        return Response::json($content, $statusCode);
    }


    public function memberStore($data = null)
    {
        if($data == null)
            $data = request()->all();

        $endpoint = $this->bio_base_url . "/personnel/api/employees/";

        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $endpoint, ['headers' => ['Authorization' => "JWT " . @$this->token]
            , \GuzzleHttp\RequestOptions::JSON => [
                "id"=> (int)$data['code'],
                "uid"=> (int)$data['code'],
                "emp_code" => $data['code'],
                "first_name" => $data['code'],
                "last_name" => "",
                "area" => [1],
                "department" => 1,
                "card_no"=> $data['code']
            ]]);
        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        $content = json_decode($content);
        return Response::json($content, $statusCode);
    }

    public function memberDelete($member = null)
    {
        $endpoint = $this->bio_base_url . "/personnel/api/employees/".$member."/";
        $client = new \GuzzleHttp\Client();
        $response = $client->request('DELETE', $endpoint, ['headers' => ['Authorization' => "JWT " . @$this->token]
        ]);
        $statusCode = $response->getStatusCode();
        $content = $response->getBody();
        $content = json_decode($content);
        return Response::json($content, $statusCode);
    }

}

