<?php


namespace App\Services;

use App\Models\Setting;
use Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class UpWorkService extends BaseService {

    public function __construct(){
    }

    public function init(){
        $client = $this->getUpworkClientForAuth();
        return redirect()->to($client->getServer()->getInstance()->getAuthorizationUrl());
    }


    public function code(){

        $client = $this->getUpworkClientForAuth();

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

    public function jobs($options = [])
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

                    engagement
                    amount {
                        rawValue
                        currency
                        displayValue
                    }
                    recordNumber
                    experienceLevel
                    category
                    subcategory
                    freelancersToHire
                    totalApplicants
                    createdDateTime
                    publishedDateTime
                    renewedDateTime
                    weeklyBudget {
                        rawValue
                        currency
                        displayValue
                    }
                    hourlyBudgetType
                    hourlyBudgetMin {
                        rawValue
                        currency
                        displayValue
                    }
                    hourlyBudgetMax {
                        rawValue
                        currency
                        displayValue
                    }
                    engagementDuration {
                        id
                        label
                        weeks
                    }
                    preferredFreelancerLocation
                    preferredFreelancerLocationMandatory
                    premium
                    applied
                    job {
                      id
                      workFlowState {
                        status
                        closeResult
                      }
                      ownership {
                        team {
                          id
                          name
                          type

                        }
                      }
                      content {
                        title
                        description
                      }
                      contractorSelection {
                        proposalRequirement {
                          coverLetterRequired
                          freelancerMilestonesAllowed
                          screeningQuestions {
                            sequenceNumber
                            question
                          }
                        }
                      }
                      attachments {
                        sequenceNumber
                        id
                        fileName
                        fileSize

                      }
                      classification {
                        category {
                          id
                          ontologyId
                          definition
                          preferredLabel
                          type
                        }
                        subCategory {
                          id
                          ontologyId
                          definition
                          preferredLabel
                          type
                        }
                        skills {
                          id
                          ontologyId
                          definition
                          preferredLabel
                          type
                        }
                        additionalSkills {
                          id
                          ontologyId
                          definition
                          preferredLabel
                          type
                        }
                      }
                      segmentationData {
                        segmentationValues {
                          segmentationInfo {
                            sortOrder
                            id
                            label
                            referenceName
                            skill {
                              id
                              ontologyId
                              definition
                              preferredLabel
                              type
                            }
                            segmentationType {
                              id
                              name
                              referenceName
                            }
                          }
                        }
                      }
                      activityStat {
                        applicationsBidStats {
                          avgRateBid {
                            rawValue
                            currency
                            displayValue
                          }
                          minRateBid {
                            rawValue
                            currency
                            displayValue
                          }
                          maxRateBid {
                            rawValue
                            currency
                            displayValue
                          }
                          avgInterviewedRateBid {
                            rawValue
                            currency
                            displayValue
                          }
                        }
                      }
                    }
                    client {
                        totalHires
                        totalPostedJobs
                        totalReviews,
                        totalFeedback
                        totalSpent {
                            rawValue
                            displayValue
                            currency
                        }
                        verificationStatus
                        companyRid
                        hasFinancialPrivacy
                        companyName
                        edcUserId
                        memberSinceDateTime
                    }
                    teamId
                    freelancerClientRelation {
                        companyName
                    }
                  }
                }
              }
        }
        QUERY;
        $query = 'laravel';

        if(array_key_exists('q',$options))
        {
            $query = $options['q'];
        }

        $params['variables'] = [
            "searchType" => "JOBS_FEED",
            "marketPlaceJobFilter" => [
              "searchExpression_eq" => $query,
              "pagination_eq" => [
                "after" => "0",
                "first" => 10
              ]
            ]
        ];

        $response = $graphql->execute($params);

        if(property_exists($response,'message'))
        {
            $this->log('warning',$response->message,[
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }

        $data = $response->data;
        $jobs = $data->marketplaceJobPostings->edges;
        $data = [];
        foreach($jobs as $job)
        {
            $node = $this->convertObjectToArray($job);
            $data[] = $node;
        }
        return $data;
    }

    public function job($upworkJobId)
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query marketplaceJobPosting(\$id: ID!) {
            marketplaceJobPosting(id: \$id) {
              id
              workFlowState {
                status
                closeResult
              }
              ownership {
                team {
                  id
                  nam1e
                  type
                  photoUrl
                }
              }
              content {
                title
                description
              }
              contractorSelection {
                proposalRequirement {
                  coverLetterRequired
                  freelancerMilestonesAllowed
                  screeningQuestions {
                    sequenceNumber
                    question
                  }
                }
              }
              attachments {
                sequenceNumber
                id
                fileName
                fileSize
              }
              classification {
                category {
                  id
                  ontologyId
                  definition
                  preferredLabel
                  type
                }
                subCategory {
                  id
                  ontologyId
                  definition
                  preferredLabel
                  type
                }
                skills {
                  id
                  ontologyId
                  definition
                  preferredLabel
                  type
                }
                additionalSkills {
                  id
                  ontologyId
                  definition
                  preferredLabel
                  type
                }
              }
              segmentationData {
                segmentationValues {
                  segmentationInfo {
                    sortOrder
                    id
                    label
                    referenceName
                    skill {
                      id
                      ontologyId
                      definition
                      preferredLabel
                      type
                    }
                    segmentationType {
                      id
                      name
                      referenceName
                    }
                  }
                }
              }
              activityStat {
                applicationsBidStats {
                  avgRateBid {
                    rawValue
                    currency
                    displayValue
                  }
                  minRateBid {
                    rawValue
                    currency
                    displayValue
                  }
                  maxRateBid {
                    rawValue
                    currency
                    displayValue
                  }
                  avgInterviewedRateBid {
                    rawValue
                    currency
                    displayValue
                  }
                }
              }
            }
          }
        QUERY;
        $params['variables'] = [
            "id" => $upworkJobId,
        ];
        $response = $graphql->execute($params);
        $response = json_decode(json_encode($response->data), true);
        $response = Arr::dot($response);
        $data = $response->data;
        $jobs = $data->marketplaceJobPostings->edges;
        $ids = [];
        foreach($jobs as $job)
        {
            $ids[] = (array) $job->node;
        }
        return $this->successfullApiResponse($ids);
    }

    public function categories()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query ontologyCategories {
            ontologyCategories {
              id
              preferredLabel
              altLabel
              slug
              ontologyId
              subcategories{
                id
                  preferredLabel
                  altLabel
              }

            }

        }
        QUERY;
        // $params['variables'] = [
        //     "searchType" => "JOBS_FEED",
        //     "marketPlaceJobFilter" => [
        //       "searchExpression_eq" => "laravel",
        //       "pagination_eq" => [
        //         "after" => "0",
        //         "first" => 10
        //       ]
        //     ]
        // ];
        $response = $graphql->execute($params);
        // dd($response);
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }
    public function skills()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query ontologySkills(
            \$limit: Int!,
            \$offset: Int
          ) {
              ontologySkills(
              limit: \$limit,
              offset: \$offset
            ){
              id
              prettyName
              preferredLabel
              presentationMode
              altLabel
              ontologyId
            }
          }
        QUERY;
        $params['variables'] = [
            "limit" => 100,
            "offset" => 100,
        ];
        $response = $graphql->execute($params);
        // dd($response);
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }
    public function timezones()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query timeZones {
            timeZones {
              timeZoneName
              timeZoneDescription
            }
        }
        QUERY;
        $params['variables'] = [
            "limit" => 100,
            "offset" => 100,
        ];
        $response = $graphql->execute($params);
        // dd($response);
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }

    public function languages()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query languages {
            languages {
              iso639Code
              active
              englishName
            }
        }
        QUERY;
        $params['variables'] = [
            "limit" => 100,
            "offset" => 100,
        ];
        $response = $graphql->execute($params);
        // dd($response);
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }

    public function countries()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query countries {
            countries {
              id
              name
              twoLetterAbbreviation
              threeLetterAbbreviation
              region
              phoneCode
              relatedRegion {
                id
                name
              }
              relatedSubRegion {
                id
                name
              }
              active
              registrationAllowed
            }
          }
        QUERY;
        $params['variables'] = [
            "limit" => 100,
            "offset" => 100,
        ];
        $response = $graphql->execute($params);
        // dd($response);
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }
    public function regions()
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query languages {
            languages {
              iso639Code
              active
              englishName
            }
        }
        QUERY;
        $params['variables'] = [
            "limit" => 100,
            "offset" => 100,
        ];
        $response = $graphql->execute($params);
        // dd($response);
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }

    public function getUpworkClientForAuth($overrideOptions = [])
    {
        $options = [
            'clientId'          => config('upwork.client_id'),
            'clientSecret'      => config('upwork.client_secret'),
            'redirectUri'       => config('upwork.redirect_uri').'/api/v1/upwork/code',
        ];

        $config = new \Upwork\API\Config($options);
        $client = new \Upwork\API\Client($config);
        return $client;
    }

    public function getUpworkClient($overrideOptions = [])
    {

        $settings = Setting::whereIn('name',[
            'access_token',
            'refresh_token',
            'expiry'
        ])->pluck('value','name');


        $options = [
            'clientId'          => config('upwork.client_id'),
            'clientSecret'      => config('upwork.client_secret'),
            'redirectUri'       => config('upwork.redirect_uri').'/upwork/code',
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

        if($this->isAccessTokenExpired($config))
        {
            $client = $this->renewAccessToken($client,$config);
        }

        return $client;
    }
    public function isAccessTokenExpired($config)
    {
        return Carbon::now()->timestamp > ( $config->get('expiresIn') - 30) ;
    }
    public function renewAccessToken($client,$config)
    {
        $this->log('info',"Renewing Access Token");
        $accessToken = $client->getServer()->getInstance()->getAccessToken('refresh_token',[
            'refresh_token' => $config->get('refreshToken')
        ]);

        Setting::insertOrUpdate('access_token',$accessToken->getToken());
        Setting::insertOrUpdate('refresh_token',$accessToken->getRefreshToken());
        Setting::insertOrUpdate('expiry',$accessToken->getExpires());

        $config->set('accessToken',$accessToken->getToken());
        $config->set('refreshToken',$accessToken->getRefreshToken());
        $config->set('expires',$accessToken->getExpires());

        $client = new \Upwork\API\Client($config);
        return $client;
    }
    public function log($type,$data,$contenxt = [])
    {
        Log::$type($data,$contenxt);
    }
}

