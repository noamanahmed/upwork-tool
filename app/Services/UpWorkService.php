<?php


namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Fluent;

class UpWorkService extends BaseService {

    public function __construct(){
    }

    public function init(){
        $client = $this->getUpworkClient();
        return redirect()->to($client->getServer()->getInstance()->getAuthorizationUrl());
    }


    public function code(){

        $client = $this->getUpworkClient();
        $accessToken = $client->getServer()->getInstance()->getAccessToken('authorization_code',[
            'code' => request()->get('code')
        ]);
        Setting::insertOrUpdate('access_token',$accessToken->getToken());
        Setting::insertOrUpdate('refresh_token',$accessToken->getRefreshToken());
        Setting::insertOrUpdate('expiry',$accessToken->getExpires());


        return $this->successfullApiResponse(['status' => 'success']);
    }

    public function refreshToken()
    {

    }

    public function jobs()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query marketplaceJobPostings(
            \$marketPlaceJobFilter: MarketplaceJobFilter,
            \$searchType: MarketplaceJobPostingSearchType,
            \$sortAttributes: [MarketplaceJobPostingSearchSortAttribute]
          ) {
            marketplaceJobPostings(
              marketPlaceJobFilter: \$marketPlaceJobFilter,
              searchType: \$searchType,
              sortAttributes: \$sortAttributes
            ) {
              totalCount
              edges {
                node {
                    id
                    title
                    ciphertext
                }
              }
            }
          }
        QUERY;
        $params['variables'] = [
            "searchType" => "JOBS_FEED",
            "marketPlaceJobFilter" => [
              "searchExpression_eq" => "laravel",
              "pagination_eq" => [
                "after" => "0",
                "first" => 10
              ]
            ]
        ];
        $response = $graphql->execute($params);
        $data = $response->data;
        $jobs = $data->marketplaceJobPostings->edges;
        $ids = [];
        foreach($jobs as $job)
        {
            $ids[] = (array) $job->node;
        }
        return $this->successfullApiResponse($ids);
    }

    public function getUpworkClient()
    {

        $settings = Setting::whereIn('name',[
            'access_token',
            'refresh_token',
            'expiry'
        ])->pluck('value','name');


        $options = [
            'clientId'          => config('upwork.client_id'),
            'clientSecret'      => config('upwork.client_secret'),
            // 'grantType'         => 'code', // used in Client Credentials Grant
            // 'redirectUri'       => config('upwork.redirect_uri').'/upwork/code',
        ];
        if($settings->has('access_token'))
        {
            $options['accessToken'] = $settings->get('access_token');
        }
        if($settings->has('refresh_token'))
        {
            $options['refreshToken'] = $settings->get('refresh_token');
        }
        if($settings->has('expiry'))
        {
            $options['expiresIn'] = $settings->get('expiry');
        }

        $config = new \Upwork\API\Config($options);
        $client = new \Upwork\API\Client($config);
        return $client;
    }
}

