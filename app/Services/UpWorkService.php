<?php


namespace App\Services;

use App\Enums\ProposalStatusEnum;
use App\Models\Job;
use App\Models\Setting;
use App\Services\ThirdParty\RssService;
use Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
                          rid
                          legacyId
                          name
                          type
                          creationDate
                          flag {
                            client
                            vendor
                            agency
                            individual
                          }
                          active
                          legacyType
                          photoUrl
                        }
                      }
                      content {
                        title
                        description
                      }
                      contractTerms{
                        contractType
                        onSiteType
                        personsToHire
                        experienceLevel
                        fixedPriceContractTerms {
                            amount {
                                rawValue
                                currency
                                displayValue
                            }
                            maxAmount {
                                rawValue
                                currency
                                displayValue
                            }
                            engagementDuration {
                                label
                                weeks
                            }
                        }
                        hourlyContractTerms {
                            engagementDuration {
                                label
                                weeks
                            }
                            engagementType
                            hourlyBudgetMin
                            hourlyBudgetMax
                        }
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
                        jobActivity {
                            lastClientActivity
                            invitesSent
                            totalInvitedToInterview
                            totalHired
                            totalUnansweredInvites
                            totalOffered
                            totalRecommended
                        }
                      }
                      clientCompany {
                        id
                      }
                      clientCompanyPublic{
                        id
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
                        location {
                            city
                            country
                            timezone
                            state
                            offsetToUTC
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
        $query = null;

        if(array_key_exists('q',$options))
        {
            $query = $options['q'];
        }

        $params['variables'] = [
            "searchType" => "JOBS_FEED",
        ];
        $params['variables']["marketPlaceJobFilter"] = [];
        if(!empty($query))
        {
            $params['variables']["marketPlaceJobFilter"]["searchExpression_eq"] = $query;
        }

        if(!empty($options['title'] ?? null))
        {
            $params['variables']["marketPlaceJobFilter"]["titleExpression_eq"] = $options['title'];
        }

        $params['variables']["marketPlaceJobFilter"]["pagination_eq"] = [
            "after" => $options['start'] ?? "0",
            "first" => $options['limit'] ?? 20
        ];

        if($options['is_payment_verified'] ?? false)
        {
            $params['variables']["marketPlaceJobFilter"]['verifiedPaymentOnly_eq'] = true;
        }
        if(!is_null($options['client_previous_hired_minimum'] ?? null))
        {
            $params['variables']["marketPlaceJobFilter"]['clientHiresRange_eq']['rangeStart'] = (int) $options['client_previous_hired_minimum'];
        }
        if($options['client_previous_hired_maximum'] ?? 0)
        {
            $params['variables']["marketPlaceJobFilter"]['clientHiresRange_eq']['rangeEnd'] = (int) $options['client_previous_hired_maximum'];
        }
        if(!is_null($options['feedback_minimum'] ?? null))
        {
            $params['variables']["marketPlaceJobFilter"]['clientFeedBackRange_eq']['rangeStart'] = (float) $options['feedback_minimum'];
        }
        if($options['feedback_maximum'] ?? 0)
        {
            $params['variables']["marketPlaceJobFilter"]['clientFeedBackRange_eq']['rangeEnd'] = (float) $options['feedback_maximum'];
        }

        if(!is_null($options['proposals_minimum'] ?? null))
        {
            $params['variables']["marketPlaceJobFilter"]['proposalRange_eq']['rangeStart'] = (int) $options['proposals_minimum'];
        }
        if($options['proposals_maximum'] ?? 0)
        {
            $params['variables']["marketPlaceJobFilter"]['proposalRange_eq']['rangeEnd'] = (int) $options['proposals_maximum'];
        }
        if(!empty($options['location'] ?? null))
        {
            $params['variables']["marketPlaceJobFilter"]['locations_any'] = $options['location'];
        }
        if(!empty($options['days_posted'] ?? null))
        {
            $params['variables']["marketPlaceJobFilter"]['daysPosted_eq'] = $options['days_posted'];
        }

        $response = $graphql->execute($params);
        // dd($response);
        if(property_exists($response,'message'))
        {
            $this->log('warning',$response->message,[
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }

        if(!property_exists($response,'data') || is_null($response->data))
        {
            $this->log('warning','API Request failed to fetch data',[
                'reponse' => $response,
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }

        $data = $response->data;
        if(!property_exists($data,'marketplaceJobPostings') || is_null($data->marketplaceJobPostings))
        {
            $this->log('warning','API Request failed to parse data from response',[
                'data' => $data,
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }
        $jobs = $data->marketplaceJobPostings->edges;
        $data = [];
        foreach($jobs as $job)
        {
            $node = $this->convertObjectToArray($job);
            $data[] = $node;
        }
        return $data;
    }
    public function jobActivity($options = [])
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
                edges {
                  node {
                    id
                    totalApplicants
                    job {
                      id
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
                        jobActivity {
                            lastClientActivity
                            invitesSent
                            totalInvitedToInterview
                            totalHired
                            totalUnansweredInvites
                            totalOffered
                            totalRecommended
                        }
                      }
                    }
                  }
                }
              }
        }
        QUERY;
        $query = 'laravel';

        if(array_key_exists('title',$options))
        {
            $query = $options['title'];
        }

        $params['variables'] = [
            "searchType" => "JOBS_FEED",
        ];
        $params['variables']["marketPlaceJobFilter"] = [];
        $params['variables']["marketPlaceJobFilter"]["searchExpression_eq"] = $query;
        $params['variables']["marketPlaceJobFilter"]["pagination_eq"] = [
            "after" => "0",
            "first" => 20
        ];
        $response = $graphql->execute($params);

        if(property_exists($response,'message'))
        {
            $this->log('warning',$response->message,[
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }
        if(!property_exists($response,'data') || is_null($response->data))
        {
            $this->log('warning','API Request failed to fetch data',[
                'reponse' => $response,
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }

        $data = $response->data;
        if(!property_exists($data,'marketplaceJobPostings') || is_null($data->marketplaceJobPostings))
        {
            $this->log('warning','API Request failed to parse data from response',[
                'data' => $data,
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }
        $jobs = $data->marketplaceJobPostings->edges;
        $data = [];
        foreach($jobs as $job)
        {
            $node = $this->convertObjectToArray($job);
            $data[] = $node;
        }
        return $data;
    }
    public function jobContents($ids = [])
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query marketplaceJobPostingsContents(\$ids: [ID!]!) {
            marketplaceJobPostingsContents(ids: \$ids) {
              id
              ciphertext
            }
        }
        QUERY;
        $query = 'laravel';

        $params['variables']['ids'] = $ids;
        $response = $graphql->execute($params);
        if(property_exists($response,'message'))
        {
            $this->log('warning',$response->message,[
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }
        if(!property_exists($response,'data') || is_null($response->data))
        {
            $this->log('warning','API Request failed to fetch data',[
                'reponse' => $response,
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }

        $data = $response->data;
        $jobs = $data->marketplaceJobPostingsContents;
        $data = [];
        foreach($jobs as $job)
        {
            $node = $this->convertObjectToArray($job);
            $data[] = $node;
        }
        return $data;
    }
    public function rssJobs($rssJobSearch)
    {
        $url = $rssJobSearch->url ;//. '&t='.urlencode(now());
        $rssData = app(RssService::class)->setFeedUrl($url)->parse();
        $rssJobs = [];
        if(empty($rssData['entries']))
        {
            abort(429);
        }
        foreach ($rssData['entries'] ?? [] as $entry)
        {
            $rssJobs[] = [
                'rssLink' => $url,
                'dateModified' => $rssData['dateModified'],
                'link' => $entry['link'],
                'ciphertext' => $this->getCipherTextFromRssUrl($entry['link']),
                'timestamp' => now(),
                'raw' => $entry,
            ];
        }
        return $rssJobs;
    }

    public function categories()
    {
        $data = '[[{"id":"531770282584862721","preferredLabel":"Accounting & Consulting","altLabel":null,"slug":"accounting-consulting","ontologyId":"upworkOccupation:accountingandconsulting","subcategories":[{"id":"1534904461833879552","preferredLabel":"Personal & Professional Coaching","altLabel":null},{"id":"531770282601639943","preferredLabel":"Accounting & Bookkeeping","altLabel":null},{"id":"531770282601639945","preferredLabel":"Financial Planning","altLabel":null},{"id":"531770282601639946","preferredLabel":"Recruiting & Human Resources","altLabel":null},{"id":"531770282601639944","preferredLabel":"Management Consulting & Analysis","altLabel":null},{"id":"531770282601639947","preferredLabel":"Other - Accounting & Consulting","altLabel":null}]},{"id":"531770282580668416","preferredLabel":"Admin Support","altLabel":null,"slug":"admin-support","ontologyId":"upworkOccupation:adminsupport","subcategories":[{"id":"531770282584862724","preferredLabel":"Data Entry & Transcription Services","altLabel":null},{"id":"531770282584862725","preferredLabel":"Virtual Assistance","altLabel":null},{"id":"531770282584862728","preferredLabel":"Project Management","altLabel":null},{"id":"531770282584862726","preferredLabel":"Market Research & Product Reviews","altLabel":["Web Research"]}]},{"id":"531770282580668417","preferredLabel":"Customer Service","altLabel":null,"slug":"customer-service","ontologyId":"upworkOccupation:customerservicecategory","subcategories":[{"id":"1484275072572772352","preferredLabel":"Community Management & Tagging","altLabel":null},{"id":"531770282584862730","preferredLabel":"Customer Service & Tech Support","altLabel":null}]},{"id":"531770282580668420","preferredLabel":"Data Science & Analytics","altLabel":null,"slug":"data-science-analytics","ontologyId":"upworkOccupation:datascienceandanalytics","subcategories":[{"id":"531770282593251330","preferredLabel":"Data Analysis & Testing","altLabel":["A\/B Testing"]},{"id":"531770282593251331","preferredLabel":"Data Extraction\/ETL","altLabel":null},{"id":"531770282589057038","preferredLabel":"Data Mining & Management","altLabel":null},{"id":"531770282593251329","preferredLabel":"AI & Machine Learning","altLabel":null}]},{"id":"531770282580668421","preferredLabel":"Design & Creative","altLabel":null,"slug":"design-creative","ontologyId":"upworkOccupation:designandcreative","subcategories":[{"id":"531770282593251335","preferredLabel":"Art & Illustration","altLabel":null},{"id":"531770282593251341","preferredLabel":"Audio & Music Production","altLabel":null},{"id":"1044578476142100480","preferredLabel":"Branding & Logo Design","altLabel":["Brand Identity & Strategy"]},{"id":"1356688560628174848","preferredLabel":"NFT, AR\/VR & Game Art","altLabel":null},{"id":"531770282593251334","preferredLabel":"Graphic, Editorial & Presentation Design","altLabel":["Graphics & Design"]},{"id":"1356688565288046592","preferredLabel":"Performing Arts","altLabel":null},{"id":"531770282593251340","preferredLabel":"Photography","altLabel":null},{"id":"531770282601639953","preferredLabel":"Product Design","altLabel":null},{"id":"1356688570056970240","preferredLabel":"Video & Animation","altLabel":null}]},{"id":"531770282584862722","preferredLabel":"Engineering & Architecture","altLabel":null,"slug":"engineering-architecture","ontologyId":"upworkOccupation:engineeringandarchitecture","subcategories":[{"id":"531770282601639949","preferredLabel":"Building & Landscape Architecture","altLabel":null},{"id":"531770282605834240","preferredLabel":"Chemical Engineering","altLabel":null},{"id":"531770282601639950","preferredLabel":"Civil & Structural Engineering","altLabel":null},{"id":"531770282605834241","preferredLabel":"Contract Manufacturing","altLabel":null},{"id":"531770282601639951","preferredLabel":"Electrical & Electronic Engineering","altLabel":null},{"id":"531770282605834242","preferredLabel":"Interior & Trade Show Design","altLabel":["Interior Design"]},{"id":"531770282601639952","preferredLabel":"Energy & Mechanical Engineering","altLabel":null},{"id":"1301900647896092672","preferredLabel":"Physical Sciences","altLabel":null},{"id":"531770282601639948","preferredLabel":"3D Modeling & CAD","altLabel":null}]},{"id":"531770282580668419","preferredLabel":"IT & Networking","altLabel":null,"slug":"it-networking","ontologyId":"upworkOccupation:itandnetworking","subcategories":[{"id":"531770282589057033","preferredLabel":"Database Management & Administration","altLabel":["Database Administration"]},{"id":"531770282589057034","preferredLabel":"ERP\/CRM Software","altLabel":null},{"id":"531770282589057036","preferredLabel":"Information Security & Compliance","altLabel":null},{"id":"531770282589057035","preferredLabel":"Network & System Administration","altLabel":null},{"id":"531770282589057037","preferredLabel":"DevOps & Solution Architecture","altLabel":null}]},{"id":"531770282584862723","preferredLabel":"Legal","altLabel":null,"slug":"legal","ontologyId":"upworkOccupation:legal","subcategories":[{"id":"531770282605834246","preferredLabel":"Corporate & Contract Law","altLabel":null},{"id":"1484275156546932736","preferredLabel":"International & Immigration Law","altLabel":null},{"id":"531770283696353280","preferredLabel":"Finance & Tax Law","altLabel":null},{"id":"1484275408410693632","preferredLabel":"Public Law","altLabel":null}]},{"id":"531770282580668422","preferredLabel":"Sales & Marketing","altLabel":null,"slug":"sales-marketing","ontologyId":"upworkOccupation:salesandmarketing","subcategories":[{"id":"531770282597445636","preferredLabel":"Digital Marketing","altLabel":null},{"id":"531770282597445634","preferredLabel":"Lead Generation & Telemarketing","altLabel":null},{"id":"531770282593251343","preferredLabel":"Marketing, PR & Brand Strategy","altLabel":null}]},{"id":"531770282584862720","preferredLabel":"Translation","altLabel":["Translation Category"],"slug":"translation","ontologyId":"upworkOccupation:translationcategory","subcategories":[{"id":"1534904461842268160","preferredLabel":"Language Tutoring & Interpretation","altLabel":null},{"id":"531770282601639939","preferredLabel":"Translation & Localization Services","altLabel":null}]},{"id":"531770282580668418","preferredLabel":"Web, Mobile & Software Dev","altLabel":null,"slug":"web-mobile-software-dev","ontologyId":"upworkOccupation:webmobileandsoftwaredev","subcategories":[{"id":"1517518458442309632","preferredLabel":"Blockchain, NFT & Cryptocurrency","altLabel":null},{"id":"1737190722360750082","preferredLabel":"AI Apps & Integration","altLabel":null},{"id":"531770282589057025","preferredLabel":"Desktop Application Development","altLabel":null},{"id":"531770282589057026","preferredLabel":"Ecommerce Development","altLabel":null},{"id":"531770282589057027","preferredLabel":"Game Design & Development","altLabel":null},{"id":"531770282589057024","preferredLabel":"Mobile Development","altLabel":["Mobile Developer"]},{"id":"531770282589057032","preferredLabel":"Other - Software Development","altLabel":null},{"id":"531770282589057030","preferredLabel":"Product Management & Scrum","altLabel":null},{"id":"531770282589057031","preferredLabel":"QA Testing","altLabel":null},{"id":"531770282589057028","preferredLabel":"Scripts & Utilities","altLabel":null},{"id":"531770282589057029","preferredLabel":"Web & Mobile Design","altLabel":null},{"id":"531770282584862733","preferredLabel":"Web Development","altLabel":null}]},{"id":"531770282580668423","preferredLabel":"Writing","altLabel":null,"slug":"writing","ontologyId":"upworkOccupation:writing","subcategories":[{"id":"1534904462131675136","preferredLabel":"Sales & Marketing Copywriting","altLabel":null},{"id":"1301900640421842944","preferredLabel":"Content Writing","altLabel":null},{"id":"531770282597445644","preferredLabel":"Editing & Proofreading Services","altLabel":null},{"id":"531770282597445646","preferredLabel":"Professional & Business Writing","altLabel":["Technical Writing"]}]}]]';
        return json_decode($data,true);
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
        $data = $response->data;
        $ids = [];
        foreach($data as $category)
        {
            $ids[] = (array) $category;
        }
        return $this->successfullApiResponse($ids);
    }
    public function skills($limit = 100,$offset = 0)
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
            "limit" => $limit,
            "offset" => $offset,
        ];
        $response = $graphql->execute($params);
        $data = $response->data;
        if(is_null($data)) return $this->successfullApiResponse([]);
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
    public function analytics()
    {
        $response = [];
        $jobsStats = [];
        $jobsStatsIntervals = [
            [
                'key' => 'day',
                'start_date' => now()->subDay()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ],
            [
                'key' => 'previous_day',
                'start_date' => now()->subDays(2)->startOfDay()->format('Y-m-d H:i:s'), // 2 days ago
                'end_date' => now()->subDays(1)->endOfDay()->format('Y-m-d H:i:s'), // 1 day ago
            ],
            [
                'key' => 'week',
                'start_date' => now()->subWeek()->startOfWeek()->format('Y-m-d H:i:s'),
                'end_date' => now()->startOfWeek()->format('Y-m-d H:i:s'),
            ],
            [
                'key' => 'previous_week',
                'start_date' => now()->subWeeks(2)->startOfWeek()->format('Y-m-d H:i:s'), // 2 weeks ago
                'end_date' => now()->subWeeks(1)->startOfWeek()->subSeconds(1)->format('Y-m-d H:i:s'), // 1 week ago (end of previous week)
            ],
            [
                'key' => 'month',
                'start_date' => now()->startOfMonth()->format('Y-m-d H:i:s'),
                'end_date' => now()->addMonth()->startOfMonth()->format('Y-m-d H:i:s'),
            ],
            [
                'key' => 'previous_month',
                'start_date' => now()->subMonths(2)->startOfMonth()->format('Y-m-d H:i:s'), // 2 months ago
                'end_date' => now()->subMonths(1)->startOfMonth()->subSeconds(1)->format('Y-m-d H:i:s'), // 1 month ago (end of previous month)
            ],
            [
                'key' => 'year',
                'start_date' => now()->startOfYear()->format('Y-m-d H:i:s'),
                'end_date' => now()->addYear()->startOfYear()->format('Y-m-d H:i:s'),
            ],
            [
                'key' => 'previous_year',
                'start_date' => now()->subYears(2)->startOfYear()->format('Y-m-d H:i:s'), // 2 years ago
                'end_date' => now()->subYears(1)->startOfYear()->subSeconds(1)->format('Y-m-d H:i:s'), // 1 year ago (end of previous year)
            ],
        ];

        foreach($jobsStatsIntervals as $interval)
        {
            $response[$interval['key']] = Job::join('job_searches_jobs_pivot', 'jobs.id', '=', 'job_searches_jobs_pivot.job_id')
                ->join('job_searches', 'job_searches_jobs_pivot.job_search_id', '=', 'job_searches.id')
                ->where('jobs.created_at','>=',$interval['start_date'])
                ->where('jobs.created_at','<=',$interval['end_date'])
                ->groupBy('job_searches.id', 'job_searches.name')
                ->select(DB::raw('job_searches.name as job_search_name'), DB::raw('COUNT(*) as entry_count'))
                ->get();
        }
        return $this->apiResponse($response,200);
    }
    public function proposals($status = 'Accepted')
    {
        $proposalTypes = ProposalStatusEnum::toArray();
        $proposals = [];
        foreach($proposalTypes as $proposalType)
        {
            $proposals = [...$proposals,...$this->proposalsByType(ucfirst(strtolower($proposalType->name)))];
        }
        // dd($proposals);
        return $proposals;
    }
    public function proposalsByType($status = 'Accepted')
    {
        $client = $this->getUpworkClient();
        $graphql = new \Upwork\API\Routers\Graphql($client);
        $params['query'] = <<<QUERY
        query vendorProposals(
            \$filter: VendorProposalFilter!,
            \$sortAttribute: VendorProposalSortAttribute!,
            \$pagination: Pagination!
          ) {
            vendorProposals(
              filter: \$filter,
              sortAttribute: \$sortAttribute,
              pagination: \$pagination
            ) {
              totalCount
              pageInfo {
                endCursor
                hasNextPage
              }
              edges {
                node {
                  id
                  proposalCoverLetter
                  status {
                    status
                  }
                  terms {
                    chargeRate {
                      rawValue
                      displayValue
                      currency
                    }
                    estimatedDuration {
                      label
                    }
                  }
                  marketplaceJobPosting {
                    id
                    workFlowState {
                      status
                      closeResult
                    }
                    ownership {
                      team {
                        id
                        rid
                        legacyId
                        name
                        type
                        creationDate
                        flag {
                          client
                          vendor
                          agency
                          individual
                        }
                        active
                        legacyType
                        photoUrl
                      }
                    }
                    content {
                      title
                      description
                    }
                    contractTerms{
                      contractType
                      onSiteType
                      personsToHire
                      experienceLevel
                      fixedPriceContractTerms {
                          amount {
                              rawValue
                              currency
                              displayValue
                          }
                          maxAmount {
                              rawValue
                              currency
                              displayValue
                          }
                          engagementDuration {
                              label
                              weeks
                          }
                      }
                      hourlyContractTerms {
                          engagementDuration {
                              label
                              weeks
                          }
                          engagementType
                          hourlyBudgetMin
                          hourlyBudgetMax
                      }
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
                      jobActivity {
                          lastClientActivity
                          invitesSent
                          totalInvitedToInterview
                          totalHired
                          totalUnansweredInvites
                          totalOffered
                          totalRecommended
                      }
                    }
                    clientCompanyPublic{
                      id
                    }
                  }

                }
              }
            }
          }
        QUERY;
        $params['variables']['filter']['status_eq'] = $status;
        $params['variables']['sortAttribute']['field'] = 'CREATEDDATETIME';
        $params['variables']['sortAttribute']['sortOrder'] = 'DESC';
        $params['variables']['pagination']['first'] = 40;

        $response = $graphql->execute($params);

        if(property_exists($response,'message'))
        {
            $this->log('warning',$response->message,[
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }
        if(!property_exists($response,'data') || is_null($response->data))
        {
            $this->log('warning','API Request failed to fetch data',[
                'reponse' => $response,
                'client' => (array) $client->getServer()->getInstance()
            ]);
            return [];
        }

        $data = $response->data;
        $proposals = $data->vendorProposals->edges;
        $data = [];
        foreach($proposals as $proposal)
        {
            $node = $this->convertObjectToArray($proposal);
            $node['type'] = $status;
            $data[] = $node;
        }
        return $data;
    }
    public function log($type,$data,$contenxt = [])
    {
        Log::$type($data,$contenxt);
    }
    public function getCipherTextFromRssUrl($url)
    {
        $url = urldecode($url);
        $startPos = strpos($url, '~');
        $endPos = strpos($url, '?');

        // Check if both tilde and question mark are found in the URL
        if ($startPos !== false && $endPos !== false && $startPos < $endPos) {
            // Extract the substring from the tilde to the question mark
            return substr($url, $startPos, $endPos - $startPos);
        } else {
            // Return null if the pattern is not found
            return null;
        }
    }
}

