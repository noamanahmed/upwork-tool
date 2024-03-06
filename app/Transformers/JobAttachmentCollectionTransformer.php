<?php

namespace App\Transformers;

class JobAttachmentCollectionTransformer extends BaseCollectionTransformer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getEntityTransformer() : BaseTransformerContract
    {
        return new JobAttachmentTransformer();
    }

}
