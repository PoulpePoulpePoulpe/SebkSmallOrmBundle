<?php
/**
 * This file is a part of SebkSmallOrmBundle
 * Copyright 2015 - Sébastien Kus
 * Under GNU GPL V3 licence
 */

namespace Sebk\SmallOrmBundle\QueryBuilder;

use Sebk\SmallOrmBundle\Dao\AbstractDao;

/**
 * Sql query builder
 */
class QueryBuilder
{
    protected $from;
    protected $joins = array();
    protected $where;
    protected $forcedSql;
    protected $parameters = array();

    /**
     * Construct QueryBuilder
     * @param AbstractDao $baseDao
     * @param string $baseAlias
     */
    public function __construct(AbstractDao $baseDao, $baseAlias = null)
    {
        if ($baseAlias == null) {
            $baseAlias = $baseDao->getModelName();
        }

        $this->from = new FromBuilder($baseDao, $baseAlias);
    }

    /**
     * Format select part as string
     * @return string
     */
    public function getFieldsForSqlAsString()
    {
        $resultArray = $this->from->getFieldsForSqlAsArray();
        foreach($this->joins as $join) {
            $resultArray = array_merge($resultArray, $join->getFieldsForSqlAsArray());
        }

        return implode(", ", $resultArray);
    }

    /**
     * Get relation identified by alias
     * If null => return from base relation
     * @param string $alias
     * @return FromBuilder
     * @throws QueryBuilderException
     */
    public function getRelation($alias = null)
    {
        if ($alias === null || $alias == $this->from->getAlias()) {
            return $this->from;
        }

        foreach ($this->joins as $joinAlias => $join) {
            if ($alias == $joinAlias) {
                return $join;
            }
        }

        throw new QueryBuilderException("Can't find relation '$alias'");
    }

    /**
     *
     * @return array
     */
    public function getChildRelationsForAlias($alias)
    {
        $result = array();
        foreach($this->joins as $join) {
            if($join->getFromAlias() == $alias) {
                $result[] = $join;
            }
        }
        return $result;
    }

    /**
     * Format from part as string
     * @return string
     */
    public function getFromForSqlAsString()
    {
        $result = $this->from->getSql();

        return $result;
    }

    /**
     * Add join
     * @param string $fromAlias
     * @param string $relationAlias
     * @param string $alias
     * @return \Sebk\SmallOrmBundle\QueryBuilder\JoinBuilder
     */
    public function join($fromAlias, $relationAlias, $alias = null)
    {
        if($alias == null) {
            $alias = $relationAlias;
        }
        $join                = new JoinBuilder(null, $alias);
        $join->setParent($this);
        $join->setFrom($this->getRelation($fromAlias), $relationAlias);
        $this->joins[$alias] = $join;
        $this->joins[$alias]->buildBaseConditions();

        return $join;
    }

    /**
     * Initialize where clause
     * @return Bracket
     */
    public function where()
    {
        $this->where = new Bracket($this);

        return $this->where;
    }

    /**
     * Return sql statement for this query
     * @return string
     */
    public function getSql()
    {
        if ($this->forcedSql !== null) {
            return $this->forcedSql;
        }

        $sql = "SELECT ";
        $sql .= $this->getFieldsForSqlAsString();
        $sql .= " FROM ";
        $sql .= $this->getFromForSqlAsString();

        foreach($this->joins as $join) {
            $sql .= $join->getSql();
        }

        if ($this->where !== null) {
            $sql .= " WHERE ";
            $sql .= $this->where->getSql();
        }

        return $sql;
    }

    /**
     * Is sql has been forced
     * @return boolean
     */
    public function isSqlHasBeenForced()
    {
        return $this->forcedSql === null;
    }

    /**
     * Force sql to execute
     * @param string $sql
     */
    public function forceSql($sql)
    {
        $this->forcedSql = $sql;

        return $this;
    }

    /**
     * Get condition field object
     * @param string $fieldName
     * @param string $modelAlias
     * @return \Sebk\SmallOrmBundle\QueryBuilder\ConditionField
     * @throws QueryBuilderException
     */
    public function getFieldForCondition($fieldName, $modelAlias)
    {
        if ($this->from->getAlias() == $modelAlias) {
            if ($this->from->getDao()->hasField($fieldName)) {
                return new ConditionField($this->from, $fieldName);
            }
        }

        foreach ($this->joins as $joinAlias => $join) {
            if ($joinAlias == $modelAlias) {
                if ($join->getDao()->hasField($fieldName)) {
                    return new ConditionField($join, $fieldName);
                }
            }
        }

        throw new QueryBuilderException("Field '$fieldName' is not in model aliased '$modelAlias'");
    }

    /**
     * Return where to be completed
     * @return Bracket
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * Set parameter
     * @param string $paramName
     * @param string $value
     * @return \Sebk\SmallOrmBundle\QueryBuilder\QueryBuilder
     */
    public function setParameter($paramName, $value)
    {
        $this->parameters[$paramName] = $value;

        return $this;
    }

    /**
     * Get query parameters
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get raw result of query
     * @return array
     */
    public function getRawResult()
    {
        return $this->from->getDao()->getRawResult($this);
    }

    public function getResult()
    {
        return $this->from->getDao()->populate($this, $this->getRawResult());
    }
}