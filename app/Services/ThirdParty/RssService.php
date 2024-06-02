<?php

namespace App\Services\ThirdParty;

use Illuminate\Support\Facades\Http;

class RssService{

    protected $feedUrl = null;

    public function setFeedUrl($feedUrl)
    {
        $this->feedUrl = $feedUrl;
        return $this;
    }
    public function parse(){
        $feed = \Laminas\Feed\Reader\Reader::import($this->feedUrl);
        $data = [
            'title'        => $feed->getTitle(),
            'link'         => $feed->getLink(),
            'dateModified' => $feed->getDateModified(),
            'description'  => $feed->getDescription(),
            'language'     => $feed->getLanguage(),
            'entries'      => [],
        ];

        foreach ($feed as $entry) {
            $edata = [
                'title'        => $entry->getTitle(),
                'description'  => $entry->getDescription(),
                'dateModified' => $entry->getDateModified(),
                'authors'      => $entry->getAuthors(),
                'link'         => $entry->getLink(),
                'content'      => $entry->getContent(),
            ];
            $data['entries'][] = $edata;
        }
        return $data;
    }
}
