<?php

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BaseRepository implements BaseRepositoryContract{
    /**
     * The Model to interact with
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Default Primary Key Column Name (ex: id)
     *
     * @var string
     */
    protected string $primaryKey = 'id';
    /**
     * All available paginators. Currently only simple pagination with OFFSET and LIMIT is supported
     *
     * @var string
     */
    protected string $paginator = 'simple'; // TODO: Implement Cursor base pagination

    /**
     * The maximum number of reocrds per page in a paginated response
     *
     * @var integer
     */
    protected int $perPage = 20;

    /**
     * The list of Eloquent Relationships to eagerload
     *
     * @var Array
     */
    protected Array $with = [];
    /**
     * The list of count of Eloquent Relationsips. Thie will use COUNT on DB level instead of fetching all records
     *
     * @var Array
     */
    protected Array $withCount = [];

    /**
     * The list of default scopes to apply
     *
     * @var Array
     */
    protected Array $scopes = [];


    /**
     * List of available filters (DB columns to filter)
     *
     * @var Array
     */
    protected Array $filters = [];

    /**
     * List of searchable filters (DB columns to filter)
     *
     * @var Array
     */
    protected Array $searchableFilters = [];

    /**
     * List of available sorters (DB columns to sort on)
     *
     * @var Array
     */
    protected Array $sorters = [];

    /**
     * The column to sort on by Default
     *
     * @var String
     */
    protected String $defaultSorter = 'id';

    /**
     * The default direction to sort on. i.e DESC or ASC
     *
     * @var String
     */
    protected String $defaultSorterDirection = 'asc';

    /**
     * The search query to filter records
     *
     * @var String|null
    */
    protected String|null $searchQuery = null;

    /**
     * Allow ability to perform full text search
     *
     * @var bool
     */
    protected bool $supportsFullTextSearch = false;

    /**
     * List of default column for dropdown. This fetches the complete table so be careful!
     *
     * @var Array
     */
    protected Array $defaultDropdownFields = ['id'];

    public function __construct()
    {
        $this->buildOptionsFromRequest();

    }

    public function setModel(Model|Authenticatable $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel() : Model|Authenticatable
    {
        return $this->model;
    }

    public function buildOptionsFromRequest(){
        if(app()->runningInConsole()) return;
        $options = request()->get('options') ?? [];

        if($options['page'] ?? false)
        {
            request()->merge(['page' => $options['page']]);
        }
        if($options['itemsPerPage'] ?? false && (int) $options['itemsPerPage'] >= 0 && (int) $options['itemsPerPage'] <= 100 )
        {
            $this->perPage = $options['itemsPerPage'];
        }

        if(!empty(request()->get('q'))  ?? false)
        {
            $this->searchQuery = request()->get('q');
        }
        if(!empty(request()->get('options'))  && !empty(request()->get('options')['sortBy']))
        {
            $sortByOptions = request()->get('options')['sortBy'];
            $sortString = '';
            foreach($sortByOptions as $key => $sortOptions)
            {
                if(!array_key_exists('key',$sortOptions)) continue;
                if(!array_key_exists('order',$sortOptions)) continue;
                if(strtolower($sortOptions['order']) === 'desc')
                {
                    $sortString .= '-'.$sortOptions['key'];
                }else{
                    $sortString .= $sortOptions['key'];
                }
            }
            if(!empty($sortString))
            {
                request()->merge(['sort' => $sortString]);
            }
        }
    }
    public function getQueryBuilder() : QueryBuilder
    {
        $queryBuilder = QueryBuilder::for($this->model);
        if(!empty($this->filters))
        {
            $queryBuilder = $queryBuilder->allowedFilters($this->filters);
        }
        if(!empty($this->sorters))
        {
            $queryBuilder = $queryBuilder->allowedSorts($this->sorters);
        }
        if(!empty($this->defaultSorterDirection) && !empty($this->defaultSorter))
        {
            $this->defaultSorterDirection = strtolower($this->defaultSorterDirection);
            if(!in_array($this->defaultSorterDirection,['asc','desc'])) throw new RuntimeException(`The $this->defaultSorterDirection must be either ASC or DESC`);
            if($this->defaultSorterDirection === 'desc')
            {
                $this->defaultSorter = '-'.$this->defaultSorter;
            }
            $queryBuilder = $queryBuilder->defaultSort($this->defaultSorter);
        }
        if(!empty($this->searchQuery) && $this->supportsFullTextSearch)
        {
            $queryBuilder = $queryBuilder->whereFullText($this->searchableFilters,$this->searchQuery .'*',[
                'mode' => 'boolean'
            ]);
        }
        if(!empty($this->searchQuery) && !$this->supportsFullTextSearch)
        {
            $queryBuilder = $queryBuilder->where(function($query){
                foreach($this->searchableFilters as $key => $filter)
                {
                    if($key === 0)
                    {
                        $query->where($filter,'LIKE','%'.$this->searchQuery.'%');
                    }else{
                        $query->orWhere($filter,'LIKE','%'.$this->searchQuery.'%');
                    }
                }
            });

        }

        if(!empty($this->with))
        {
            $queryBuilder = $queryBuilder->with($this->with);
        }

        if(!empty($this->withCount))
        {

            $queryBuilder = $queryBuilder->withCount($this->withCount);
        }

        if(!empty($this->scopes))
        {
            $queryBuilder = $queryBuilder->scopes($this->scopes);
        }

        return $queryBuilder;
    }

    public function index()
    {
        $queryBuilder =  $this->getQueryBuilder();

        if($this->paginator === 'simple') return $queryBuilder->paginate($this->perPage);

        return $queryBuilder;
    }
    public function dropdown()
    {
        $this->with = [];
        $this->withCount = [];
        return $this->getQueryBuilder()->select($this->defaultDropdownFields)->get();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function get($id)
    {
        return $this->model->find($id);
    }
    public function pluckIds()
    {
        return $this->model->pluck($this->primaryKey);
    }
    public function store($array)
    {
        $this->model->fill($array)->save();
        $this->model->refresh();
        return $this->model;
    }
    public function update($id,$array)
    {
        $this->model = $this->model->find($id);
        $this->model->fill($array)->save();
        $this->model->refresh();
        return $this->model;
    }
    public function destory($id)
    {
        return $this->model->where($this->primaryKey,$id)->delete();
    }
    public function destroyMulti($array)
    {
        return$this->model->whereIn($this->primaryKey,$array)->delete();
    }

    public function addScopes($scope)
    {
        $this->scopes[] = $scope;
        return $this;
    }
}
