<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function query()
    {
        return $this->model->query();
    }

    public function paginate($perPage = 10, array $options = [])
    {
        return $this->model->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete($id)
    {
        // Check if $id is already a model instance
        if ($id instanceof Model) {
            return $id->delete();
        }
        
        // Otherwise, treat it as an ID and find the record
        $record = $this->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    public function search(array $criteria, $get = true)
    {

        $query = $this->model->query();

        // Search by where
        if($where = data_get($criteria, 'where', false)) {
            foreach($where as $w) {
                $query->where(data_get($w, 'field', 'id'), data_get($w, 'operator', '='), data_get($w, 'value'));
            }
        }

        // Search by orWhere
        if($orWhere = data_get($criteria, 'orWhere', false)) {
            $query->where(function($query) use($orWhere){
                foreach($orWhere as $w) {
                    $query->orWhere(data_get($w, 'field', 'id'), data_get($w, 'operator', '='), data_get($w, 'value'));
                }
            });
        }

        // Search by orWhereNull
        if($orWhereNull = data_get($criteria, 'orWhereNull', false)) {
            $query->where(function($query) use($orWhereNull){
                foreach($orWhereNull as $w) {
                    $query->orWhereNull(data_get($w, 'field', 'id'));
                }
            });
        }


        // Search by whereDate
        if($whereDate = data_get($criteria, 'whereDate', false)) {
            foreach($whereDate as $w) {
                $query->where(data_get($w, 'field', 'id'), data_get($w, 'operator', '='), data_get($w, 'value'));
            }
        }

        // Search by whereIn
        if($whereIn = data_get($criteria, 'whereIn', false)) {
            foreach($whereIn as $w) {
                $query->whereIn(data_get($w, 'field', 'id'), data_get($w, 'value'));
            }
        }

        // Search by whereRelation
        if($whereRelation = data_get($criteria, 'whereRelation', false)) {
            foreach($whereRelation as $w) {
                $query->whereRelation(data_get($w, 'relationship'), data_get($w, 'field'), data_get($w, 'operator', '='), data_get($w, 'value'));
            }
        }

        if($callbacks = data_get($criteria, 'callbacks', [])) {
            foreach($callbacks as $callback) {
                $callback($query);
            }
        }

        return $get ? $query->get() : $query;

        // my search above
        $useOrOperator = data_get($criteria, 'use_or_operator', false);
        unset($criteria['use_or_operator']);

        $query = $this->model->query();

        $query->where(function ($query) use ($criteria, $useOrOperator) {
            foreach ($criteria as $key => $condition) {
                if (is_array($condition) && isset($condition['operator'])) {
                    $this->applyOperatorCondition($query, $key, $condition, $useOrOperator);
                } elseif (is_array($condition) && isset($condition['relationship'])) {
                    $this->applyRelationshipCondition($query, $condition);
                } else {
                    $this->applySimpleCondition($query, $key, $condition, $useOrOperator);
                }
            }
        });

        return $get ? $query->get() : $query;
    }

    protected function applyOperatorCondition($query, $key, $condition, $useOrOperator)
    {
        $operator = $condition['operator'];
        $value = $condition['value'];

        if ($useOrOperator) {
            $query->orWhere(function ($subQuery) use ($key, $operator, $value, $condition) {
                $this->applyCondition($subQuery, $key, $operator, $value, $condition);
            });
        } else {
            $this->applyCondition($query, $key, $operator, $value, $condition);
        }
    }

    protected function applyRelationshipCondition($query, $condition)
    {
        $relationship = $condition['relationship'];
        $relationshipKey = $condition['key'];
        $relationshipValue = $condition['value'];

        $query->whereHas($relationship, function ($subQuery) use ($relationshipKey, $relationshipValue, $condition) {
            $this->applyCondition($subQuery, $relationshipKey, 'LIKE', "%$relationshipValue%", $condition);
        });
    }

    protected function applySimpleCondition($query, $key, $condition, $useOrOperator)
    {
        if ($useOrOperator) {
            $query->orWhere($key, 'LIKE', "%$condition%");
        } else {
            $query->where($key, 'LIKE', "%$condition%");
        }
    }

    protected function applyCondition($query, $key, $operator, $value, $condition)
    {
        if (is_array($value) || $operator == 'IN') {
            $query->whereIn($key, $value);
        } else {
            $query->where($key, $operator, $value);
        }
    }
}
