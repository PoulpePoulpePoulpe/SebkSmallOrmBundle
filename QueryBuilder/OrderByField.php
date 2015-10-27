<?php
/**
 * This file is a part of SebkSmallOrmBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallOrmBundle\QueryBuilder;

use Sebk\SmallOrmBundle\QueryBuilder\FromBuilder;

/**
 * Field definition for condition
 */
class OrderByField
{
    public $model;
    public $fieldNameInModel;
    protected $sens;

    /**
     * Construct field definition
     * @param FromBuilder $model
     * @param string $fieldNameInModel
     */
    public function __construct(FromBuilder $model, $fieldNameInModel,
                                $sens = "ASC")
    {
        switch ($sens) {
            case "ASC":
            case "DESC":
                break;

            default:
                throw new QueryBuilderException("Sens of order by can't be '$sens'");
        }

        $this->model            = $model;
        $this->fieldNameInModel = $fieldNameInModel;
        $this->sens             = $sens;
    }

    /**
     *
     * @return string
     */
    public function getSql()
    {
        return $this->model->getFieldAliasIdentifiedByStringForSql($this->fieldNameInModel)." ".$this->sens." ";
    }
}